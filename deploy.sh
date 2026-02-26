#!/bin/bash

#######################################
# Auto Deployment Script
# Absensi Frontend/Admin Panel
# Called by webhook or cron
#######################################

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Config - SESUAIKAN PATH INI
PROJECT_DIR="/var/www/html/absensi"
LOG_FILE="$PROJECT_DIR/storage/logs/deployment.log"

# Function to log with timestamp
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] ✓ $1${NC}" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ✗ $1${NC}" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] ⚠ $1${NC}" | tee -a "$LOG_FILE"
}

# Start deployment
log "========================================="
log "Starting Auto Deployment - Absensi"
log "========================================="

# Change to project directory
cd "$PROJECT_DIR" || {
    log_error "Failed to change directory to $PROJECT_DIR"
    exit 1
}

# Check if git repo exists
if [ ! -d ".git" ]; then
    log_error "Not a git repository!"
    exit 1
fi

# Enable maintenance mode
log "Enabling maintenance mode..."
php artisan down --retry=60 --secret="deploy-secret-2026" || log_warning "Failed to enable maintenance mode"

# Stash any local changes
log "Stashing local changes..."
git stash

# Pull latest changes
log "Pulling latest code from repository..."
if git pull origin main; then
    log_success "Code pulled successfully"
else
    log_error "Failed to pull code"
    php artisan up
    exit 1
fi

# Check if composer.json changed
if git diff HEAD@{1} --name-only | grep -q "composer.json"; then
    log "composer.json changed, installing dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    log_success "Composer dependencies updated"
else
    log "No changes in composer.json, skipping composer install"
fi

# Check if package.json changed
if git diff HEAD@{1} --name-only | grep -q "package.json"; then
    log "package.json changed, installing npm dependencies..."
    npm install
    log_success "NPM dependencies updated"
else
    log "No changes in package.json, skipping npm install"
fi

# Build frontend assets (ALWAYS, karena mungkin ada perubahan di resources)
log "Building frontend assets with Vite..."
if npm run build; then
    log_success "Frontend assets built successfully"
else
    log_warning "Failed to build assets, continuing..."
fi

# Run migrations
log "Running database migrations..."
if php artisan migrate --force; then
    log_success "Migrations completed"
else
    log_warning "Migrations failed or no new migrations"
fi

# Clear and optimize caches
log "Clearing and optimizing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

log "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set correct permissions
log "Setting file permissions..."
sudo chmod -R 775 storage bootstrap/cache 2>/dev/null || log_warning "Could not change some permissions"
sudo chmod -R 755 public/build 2>/dev/null || log_warning "Could not change build permissions"
sudo chown -R www-data:www-data storage bootstrap/cache public/build 2>/dev/null || log_warning "Could not change ownership"
log_success "Permissions set"

# Restart PHP-FPM
log "Restarting PHP-FPM..."
if sudo systemctl restart php8.2-fpm; then
    log_success "PHP-FPM restarted"
else
    log_warning "Failed to restart PHP-FPM"
fi

# Disable maintenance mode
log "Disabling maintenance mode..."
php artisan up
log_success "Application is back online"

# Deployment summary
log "========================================="
log_success "Deployment completed successfully!"
log "========================================="
log ""

exit 0
