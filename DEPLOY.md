# GitHub Actions Auto Deploy

Auto deployment menggunakan GitHub Actions - lebih simple tanpa webhook handler!

## ⚡ Setup (4 Langkah)

### 1. Generate SSH Key di VPS

```bash
# SSH ke VPS sebagai user pkl
ssh pkl@api.globalintermedia.online

# Generate SSH key (tanpa passphrase)
ssh-keygen -t ed25519 -C "github-actions-absensi" -f ~/.ssh/github_deploy -N ""

# Add ke authorized_keys
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys

# Copy private key (simpan untuk GitHub Secrets)
cat ~/.ssh/github_deploy
```

### 2. Setup Sudoers untuk Auto Restart

```bash
# Edit sudoers
sudo visudo

# Tambahkan di paling bawah:
pkl ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.2-fpm
```

### 3. Setup GitHub Secrets

Buka: `https://github.com/YOUR-USERNAME/absensi/settings/secrets/actions`

Tambahkan secrets berikut:

| Secret Name | Value |
|------------|-------|
| `VPS_HOST` | `api.globalintermedia.online` |
| `VPS_USER` | `pkl` |
| `VPS_SSH_KEY` | Paste isi `~/.ssh/github_deploy` (private key) |
| `VPS_PORT` | `22` |
| `DEPLOY_SECRET` | `koekontol` |

### 4. Push Code

```bash
git add .
git commit -m "Setup GitHub Actions deployment"
git push origin main
```

## ✅ Done!

Setiap push ke `main` branch akan otomatis:
1. ✅ SSH ke VPS
2. ✅ Pull latest code
3. ✅ Install dependencies (composer & npm)
4. ✅ **Build Vite assets**
5. ✅ Run migrations
6. ✅ Clear cache
7. ✅ Restart PHP-FPM

---

## 📝 Manual Deploy

```bash
# SSH ke VPS
ssh pkl@api.globalintermedia.online
cd /var/www/html/absensi

# Pull & deploy
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.2-fpm
```

---

## 🧪 Monitor Deployment

### GitHub Actions
Buka: `https://github.com/YOUR-USERNAME/absensi/actions`

### VPS Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
```

---

## 🔍 Troubleshooting

### Build gagal
```bash
# Check npm
npm run build

# Check node version
node --version  # Harus v16+
```

### SSH connection failed
```bash
# Test SSH key
ssh -i ~/.ssh/github_deploy pkl@api.globalintermedia.online

# Check key di GitHub Secrets (harus private key lengkap)
```

### Permission denied
```bash
# Fix ownership
sudo chown -R pkl:pkl /var/www/html/absensi
chmod -R 755 /var/www/html/absensi

# Fix storage & cache
chmod -R 775 storage bootstrap/cache
```

### Assets tidak update
```bash
# Hard refresh browser: Ctrl+Shift+R
# Check build manifest
cat public/build/manifest.json
```

---

## 🎯 Deployment Flow

GitHub Actions akan menjalankan:
```yaml
1. git pull origin main
2. composer install --no-dev
3. npm ci && npm run build
4. php artisan migrate --force
5. php artisan config:cache
6. php artisan route:cache  
7. php artisan view:cache
8. sudo systemctl restart php8.2-fpm
```

Total waktu: **~2-3 menit** (karena npm build)

---

**Simple & Clean!** 🚀
