
# Terminal Todo

Aplikasi manajemen tugas untuk tim dengan fokus pada editorial calendar dan social media management.

## Production Deployment Guide

### Persyaratan Server
- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- Node.js 16+ dan NPM
- Composer 2+
- Webserver (Nginx direkomendasikan)
- SSL Certificate

### Langkah Deployment

1. **Siapkan Environment**
   ```bash
   # Clone repository
   git clone [repository_url] /path/to/app
   cd /path/to/app
   
   # Install dependensi
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   
   # Setup environment
   cp .env.example .env
   php artisan key:generate
   ```

2. **Konfigurasi Environment**
   Edit file `.env` dan sesuaikan:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_secure_password
   
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis
   
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

3. **Setup Database**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force # Jika diperlukan
   ```

4. **Optimasi**
   ```bash
   # Cache routes dan config
   php artisan route:cache
   php artisan config:cache
   php artisan view:cache
   
   # Set permission
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. **Setup Queue Worker**
   Buat sistem service untuk menjalankan queue worker:
   ```bash
   # /etc/systemd/system/laravel-queue.service
   [Unit]
   Description=Laravel Queue Worker
   After=network.target

   [Service]
   User=www-data
   Group=www-data
   Restart=always
   ExecStart=/usr/bin/php /path/to/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

   [Install]
   WantedBy=multi-user.target
   ```

   ```bash
   # Aktifkan service
   sudo systemctl enable laravel-queue
   sudo systemctl start laravel-queue
   ```

6. **Setup Scheduler**
   Tambahkan cron job:
   ```
   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
   ```

7. **Konfigurasi Nginx**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       return 301 https://$host$request_uri;
   }

   server {
       listen 443 ssl;
       server_name yourdomain.com;
       
       ssl_certificate /path/to/fullchain.pem;
       ssl_certificate_key /path/to/privkey.pem;
       
       root /path/to/app/public;
       index index.php;
       
       # Gzip Compression
       gzip on;
       gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       # Cache static assets
       location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
           expires max;
           add_header Cache-Control "public, max-age=31536000";
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

8. **Monitoring Setup**
   - Gunakan supervisor untuk memastikan queue worker tetap berjalan
   - Setup monitoring untuk server (New Relic, Datadog, atau solusi lain)
   - Setup backup otomatis untuk database

9. **Security Checks**
   - Pastikan semua direktori memiliki permission yang benar
   - Periksa firewall settings
   - Gunakan Laravel Security Checker untuk memeriksa vulnerabilities

10. **Post-Deployment**
    - Clear cache
    ```bash
    php artisan cache:clear
    php artisan optimize
    ```
    
    - Test aplikasi di environment production
    - Setup monitoring dan alerts

### Maintenance

- **Updates**
  ```bash
  # Turn on maintenance mode
  php artisan down
  
  # Pull the latest changes
  git pull
  
  # Update dependencies
  composer install --no-dev --optimize-autoloader
  npm install
  npm run build
  
  # Run migrations
  php artisan migrate --force
  
  # Clear caches
  php artisan optimize:clear
  php artisan optimize
  
  # Turn off maintenance mode
  php artisan up
  ```

- **Backups**
  Setup backup otomatis menggunakan package seperti spatie/laravel-backup.

### Performance Monitoring

Untuk monitoring performa aplikasi, gunakan tool seperti:
- New Relic
- Datadog
- Laravel Telescope (hanya untuk development/staging)

### Security Best Practices

- Update dependencies secara rutin
- Aktifkan CSRF protection di semua form
- Gunakan sistem permissions untuk kontrol akses
- Gunakan prepared statements untuk query database
- Validasi semua input user
- Protect against XSS dan CSRF attacks dengan middleware
- Log semua aktivitas keamanan

================

Untuk menjalankan `./scripts/deploy.sh` di lingkungan produksi, ikuti langkah-langkah berikut:

1. Pastikan script memiliki izin eksekusi:
```bash
chmod +x scripts/deploy.sh
```

2. Jalankan script dari direktori root project:
```bash
cd /path/to/aplikasi
./scripts/deploy.sh
```

Jika Anda menggunakan sistem deployment seperti Forge, Envoyer, atau menjalankan dari jarak jauh via SSH:

```bash
ssh user@server "cd /path/to/aplikasi && ./scripts/deploy.sh"
```

Untuk server dengan pembatasan akses:

1. Login ke server via SSH
2. Navigasi ke direktori project
3. Jalankan script:
```bash
cd /path/to/aplikasi
sudo ./scripts/deploy.sh
```

Pastikan server memiliki semua dependensi yang diperlukan (PHP 8.2+, Node.js, npm, dan composer). Jika mengalami masalah izin, tambahkan perintah `sudo` di depan jika diperlukan.

Periksa log terminal untuk memastikan tidak ada error dalam proses deployment.


================

## License

The Terminal Todo is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Sistem Embed Media Sosial

## Komponen Modular untuk Embed Media Sosial

Sistem ini terdiri dari komponen-komponen modular Vue.js yang menangani embed berbagai platform media sosial dengan cara yang lebih terstruktur dan mudah dikelola.

### Struktur Komponen

- **EmbedManager.vue** - Komponen induk yang mendeteksi jenis media sosial dan memilih komponen yang sesuai.
- **TwitterEmbed.vue** - Menangani embed Twitter/X dengan tiga strategi berbeda (oEmbed API, iframe langsung, dan fallback statis).
- **YouTubeEmbed.vue** - Menangani YouTube videos dan Shorts.
- **TikTokEmbed.vue** - Menangani embed TikTok dengan loading script dinamis.
- **InstagramEmbed.vue** - Menangani embed Instagram posts dan Reels.
- **FacebookEmbed.vue** - Menangani embed Facebook posts.
- **DefaultEmbed.vue** - Fallback untuk platform yang tidak memiliki komponen khusus.

### Penggunaan

```vue
<template>
  <EmbedManager 
    :url="url" 
    :metaData="metaData" 
  />
</template>

<script setup>
import EmbedManager from '@/Components/Embeds/EmbedManager.vue';

// URL dan metadata dari platform sosial media
const url = 'https://twitter.com/username/status/123456789';
const metaData = {
  platform: 'twitter',
  author_name: 'Username',
  text: 'Tweet content',
  // ...metadata lainnya
};
</script>
```

### Fitur

1. **Deteksi Platform Otomatis** - EmbedManager dapat mendeteksi platform berdasarkan URL.
2. **Multi-strategi Loading** - Setiap komponen memiliki beberapa strategi loading untuk kehandalan.
3. **Responsive Design** - Semua embed dioptimalkan untuk tampilan berbagai ukuran layar.
4. **Dark Mode Support** - Mendukung tema terang/gelap secara otomatis.
5. **Fallback Statis** - Jika embed tidak dapat dimuat, tampilan statis ditampilkan sebagai fallback.

### Strategi Loading

Komponen menggunakan strategi bertingkat:

1. **oEmbed API** (jika tersedia)
2. **Embed langsung via iframe**
3. **Tampilan statis fallback**

### Menambahkan Platform Baru

Untuk menambahkan platform baru:

1. Buat komponen baru dalam direktori `resources/js/Components/Embeds/`
2. Implementasikan logika spesifik untuk platform tersebut
3. Tambahkan kondisi deteksi ke `EmbedManager.vue`
4. Daftarkan komponen baru di `EmbedManager.vue`

## Deployment ke Produksi

Untuk men-deploy aplikasi ke server produksi dengan benar, ikuti langkah-langkah berikut:

1. Gunakan script deploy yang disediakan:

```bash
./scripts/deploy.sh
```

Script ini akan:
- Menginstal dependensi PHP dan Node.js
- Membangun aset frontend
- Memastikan `manifest.json` berada di lokasi yang benar
- Mengoptimalkan Laravel untuk produksi

2. Alternatif, jika Anda ingin melakukannya manual:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Build frontend
npm run build

# Pastikan manifest.json ada di lokasi yang benar
node scripts/ensure-manifest.js

# Optimize Laravel
php artisan optimize
```

### Update Produksi (Zero Downtime)

Ketika Anda perlu mengupdate aplikasi di produksi dengan gangguan minimal:

#### 1. Update Inkremental (Hanya File yang Berubah)

Gunakan script update produksi untuk mengupdate aplikasi dengan gangguan minimal, hanya memproses file yang berubah:

```bash
# Di server produksi
cd /path/to/aplikasi
./scripts/production-update.sh
```

Script ini akan:
- Melakukan git pull dari repository
- Mendeteksi file-file yang berubah
- Hanya mengupdate dependensi jika diperlukan
- Hanya membangun ulang frontend jika diperlukan
- Hanya menjalankan migrasi jika ada perubahan pada migrasi

#### 2. Deployment Penuh

Untuk deployment pertama kali atau deployment ulang penuh:

```bash
# Di server produksi
./scripts/production-deploy.sh <repository_url> [branch] [target_directory]
```

Contoh:
```bash
./scripts/production-deploy.sh git@github.com:username/terminal-todo.git main /var/www/html
```

#### 3. Rollback

Jika terjadi masalah setelah update, Anda dapat melakukan rollback ke versi sebelumnya:

```bash
# Rollback ke commit terakhir
cd /path/to/aplikasi
./scripts/production-rollback.sh

# Atau rollback ke commit spesifik
./scripts/production-rollback.sh a1b2c3d4e5f6
```

### Troubleshooting

Jika Anda melihat error:
```
Vite manifest not found at: /path/to/public/build/manifest.json
```

Coba solusi berikut:
1. Bersihkan cache Vite: `rm -rf public/build/.vite/manifest.json`
2. Rebuild frontend: `npm run build`
3. Pastikan manifest.json ada: `node scripts/ensure-manifest.js`
4. Bersihkan cache Laravel: `php artisan cache:clear`
