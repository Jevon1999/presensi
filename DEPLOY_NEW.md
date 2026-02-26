# 🚀 Auto Deploy - Webhook Setup

Simple auto-deployment menggunakan GitHub Webhook untuk Absensi Frontend.

## ⚡ Setup (3 Langkah)

### 1. Setup di VPS

```bash
# SSH ke VPS
ssh pkl@presensi.globalintermedia.online

# Masuk ke project
cd /var/www/html/absensi

# Run setup
bash setup.sh setup
```

Secret webhook sudah hardcoded: **koekontol**

### 2. Edit .env

```bash
nano .env

# Tambahkan:
WEBHOOK_SECRET=koekontol
```

### 3. Setup GitHub Webhook

1. Buka: https://github.com/YOUR-USERNAME/absensi/settings/hooks
2. Add webhook:
   - **Payload URL:** `https://presensi.globalintermedia.online/deploy-webhook.php`
   - **Content type:** `application/json`
   - **Secret:** `koekontol`
   - **Events:** Just the push event
3. Save

## ✅ Done!

Sekarang setiap push ke `main` branch, otomatis:
1. ✅ Pull code
2. ✅ Install dependencies (composer & npm)
3. ✅ **Build Vite assets**
4. ✅ Run migrations
5. ✅ Clear & optimize cache
6. ✅ Restart PHP-FPM

---

## 📝 Manual Deploy

```bash
# SSH ke VPS
ssh pkl@presensi.globalintermedia.online
cd /var/www/html/absensi

# Deploy
bash setup.sh deploy
```

---

## 🧪 Monitor Deployment

### Logs
```bash
# Deployment logs
tail -f storage/logs/deployment.log

# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
```

### Test Webhook
```bash
# Test endpoint (harus return error 400/401, itu normal)
curl -I https://presensi.globalintermedia.online/deploy-webhook.php
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

### Permission error
```bash
sudo bash setup.sh setup
```

### Assets tidak update
```bash
# Hard refresh browser: Ctrl+Shift+R
# Check build manifest
cat public/build/manifest.json
```

### Webhook tidak jalan
```bash
# Check webhook logs
tail -f storage/logs/deployment.log

# Manual trigger
bash deploy.sh
```

---

## 🕐 Alternative: Cronjob

Jika webhook tidak bisa dipakai, bisa pakai cronjob:

```bash
crontab -e

# Check update setiap 5 menit
*/5 * * * * /var/www/html/absensi/auto-pull.sh
```

---

**Simple & Reliable!** 🎉
