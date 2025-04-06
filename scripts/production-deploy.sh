#!/bin/bash
# Script untuk deploy penuh dari remote repository ke produksi
# Digunakan untuk deployment pertama kali atau deploy ulang penuh

set -e  # Exit jika ada error

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}===== TERMINAL TODO - FULL DEPLOYMENT =====${NC}"
echo "Memulai proses deployment penuh..."
TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")
echo "Waktu: $TIMESTAMP"

# Argumen untuk repository
if [ -z "$1" ]; then
    echo -e "${RED}Error: Harap berikan URL repository Git.${NC}"
    echo "Penggunaan: $0 <repository_url> [branch]"
    exit 1
fi

REPO_URL=$1
BRANCH=${2:-main}  # Default ke main jika tidak ada branch yang ditentukan
APP_DIR=${3:-/var/www/html}  # Default ke /var/www/html jika tidak ada direktori yang ditentukan

echo "Repository: $REPO_URL"
echo "Branch: $BRANCH"
echo "Target direktori: $APP_DIR"

# 1. Siapkan direktori aplikasi
if [ ! -d "$APP_DIR" ]; then
    echo -e "${YELLOW}Membuat direktori aplikasi...${NC}"
    mkdir -p "$APP_DIR"
fi

cd "$APP_DIR"

# 2. Cek apakah ini deployment pertama atau redeployment
if [ -d ".git" ]; then
    echo -e "${YELLOW}Repository Git sudah ada. Melakukan update...${NC}"
    
    # Backup file konfigurasi penting
    echo "Membuat backup file konfigurasi..."
    BACKUP_DIR="storage/backup/$(date +%Y%m%d_%H%M%S)"
    mkdir -p $BACKUP_DIR
    
    if [ -f ".env" ]; then
        cp .env $BACKUP_DIR/.env.backup
    fi
    
    # Melakukan git pull untuk update
    git fetch --all
    git checkout $BRANCH
    git reset --hard origin/$BRANCH
else
    echo -e "${YELLOW}Melakukan clone repository...${NC}"
    # Clone repository baru
    rm -rf * .[^.]*
    git clone --branch $BRANCH $REPO_URL .
    
    # Siapkan file .env
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "File .env dibuat dari .env.example. Silakan perbarui konfigurasi yang diperlukan."
    fi
fi

# 3. Instalasi dependensi PHP
echo -e "${YELLOW}Menginstal dependensi PHP...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Instalasi dependensi Node.js
echo -e "${YELLOW}Menginstal dependensi JavaScript...${NC}"
npm ci --no-audit

# 5. Generate app key jika belum ada
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    echo -e "${YELLOW}Membuat app key baru...${NC}"
    php artisan key:generate
fi

# 6. Build frontend assets
echo -e "${YELLOW}Membangun assets frontend...${NC}"
npm run build

# Pastikan manifest.json ada di lokasi yang benar
if [ -f "public/build/.vite/manifest.json" ] && [ ! -f "public/build/manifest.json" ]; then
    echo "Menyalin Vite manifest..."
    mkdir -p public/build
    cp public/build/.vite/manifest.json public/build/manifest.json
fi

# 7. Mengatur izin
echo -e "${YELLOW}Mengatur izin direktori...${NC}"
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Berikan izin tulis ke direktori yang diperlukan
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Symlink storage
echo -e "${YELLOW}Membuat symlink storage...${NC}"
php artisan storage:link --force

# 9. Migrasi database
echo -e "${YELLOW}Menjalankan migrasi database...${NC}"
php artisan migrate --force

# 10. Optimize Laravel
echo -e "${YELLOW}Mengoptimasi Laravel...${NC}"
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Hapus file sementara
echo -e "${YELLOW}Membersihkan file sementara...${NC}"
rm -rf node_modules
rm -rf vendor/phpunit

# 12. Selesai
echo -e "${GREEN}====== DEPLOYMENT SELESAI ======${NC}"
echo "Aplikasi telah berhasil di-deploy."
echo "Waktu selesai: $(date +"%Y-%m-%d %H:%M:%S")"
echo -e "${GREEN}=================================${NC}"

exit 0 