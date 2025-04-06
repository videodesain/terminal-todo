#!/bin/bash
# Script untuk rollback ke versi sebelumnya jika terjadi masalah pada update

set -e  # Exit jika ada error

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}===== TERMINAL TODO - ROLLBACK PRODUCTION =====${NC}"
echo "Memulai proses rollback ke versi sebelumnya..."
TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")
echo "Waktu: $TIMESTAMP"

# Pastikan berada di root project
if [ ! -f "composer.json" ] || [ ! -f "package.json" ]; then
    echo -e "${RED}Error: Script harus dijalankan dari root project!${NC}"
    exit 1
fi

# Cek apakah ada argumen commit hash
ROLLBACK_COMMIT=""
if [ -n "$1" ]; then
    ROLLBACK_COMMIT=$1
    echo "Rollback ke commit spesifik: $ROLLBACK_COMMIT"
else
    # Dapatkan commit sebelum HEAD
    ROLLBACK_COMMIT=$(git log --format="%H" -n 2 | tail -n 1)
    echo "Rollback ke commit sebelumnya: $ROLLBACK_COMMIT"
fi

# 1. Backup penting
echo -e "${YELLOW}Membuat backup file penting...${NC}"
BACKUP_DIR="storage/backup/rollback_$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR
cp .env $BACKUP_DIR/.env.backup
cp public/build/manifest.json $BACKUP_DIR/manifest.json.backup 2>/dev/null || true

# 2. Reset ke commit yang dipilih
echo -e "${YELLOW}Melakukan rollback ke commit $ROLLBACK_COMMIT...${NC}"
git fetch --all
git reset --hard $ROLLBACK_COMMIT

# 3. Restore sesuai jenis perubahan
echo -e "${YELLOW}Memeriksa perubahan untuk proses restore...${NC}"

# Cek apakah ada perubahan pada composer.json
if git diff HEAD^ HEAD --name-only | grep -q "composer.json"; then
    echo "Mendeteksi perubahan composer.json, memperbarui dependensi..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Cek apakah ada perubahan pada package.json
if git diff HEAD^ HEAD --name-only | grep -q "package.json"; then
    echo "Mendeteksi perubahan package.json, memperbarui dependensi JavaScript..."
    npm ci --no-audit
fi

# 4. Rebuild frontend
echo -e "${YELLOW}Membangun ulang assets frontend...${NC}"
npm run build

# Pastikan manifest.json berada di lokasi yang benar
if [ -f "public/build/.vite/manifest.json" ] && [ ! -f "public/build/manifest.json" ]; then
    echo "Menyalin Vite manifest..."
    cp public/build/.vite/manifest.json public/build/manifest.json
fi

# 5. Refresh database jika ada migrasi
if git diff HEAD^ HEAD --name-only | grep -q "database/migrations/"; then
    echo -e "${RED}PERHATIAN: Mendeteksi perubahan migrasi.${NC}"
    echo "Rollback migrasi dapat menyebabkan kehilangan data."
    read -p "Apakah Anda ingin melakukan rollback migrasi? (y/N): " confirm
    
    if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
        echo "Melakukan rollback migrasi terakhir..."
        php artisan migrate:rollback --step=1
    else
        echo "Melewati rollback migrasi..."
    fi
fi

# 6. Clear dan cache konfigurasi
echo -e "${YELLOW}Memperbarui cache aplikasi...${NC}"
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# 7. Optimize Laravel
echo -e "${YELLOW}Mengoptimalkan Laravel...${NC}"
php artisan optimize

# 8. Restart queue workers
echo -e "${YELLOW}Merestart queue workers...${NC}"
php artisan queue:restart

# 9. Selesai
echo -e "${GREEN}====== ROLLBACK SELESAI ======${NC}"
echo "Rollback ke commit $ROLLBACK_COMMIT berhasil."
echo "Waktu selesai: $(date +"%Y-%m-%d %H:%M:%S")"
echo -e "${YELLOW}CATATAN: Periksa aplikasi untuk memastikan semuanya berfungsi dengan benar${NC}"
echo -e "${GREEN}===============================${NC}"

exit 0 