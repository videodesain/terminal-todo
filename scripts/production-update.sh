#!/bin/bash
# Script untuk update sistem ke production dengan gangguan minimal
# Hanya update file yang berubah dari git pull

set -e  # Exit jika ada error

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}===== TERMINAL TODO - PRODUCTION UPDATE =====${NC}"
echo "Memulai proses update dengan gangguan minimal..."
TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")
echo "Waktu: $TIMESTAMP"

# 1. Simpan status aplikasi saat ini
CURRENT_DIR=$(pwd)
ROOT_DIR=$(dirname $(dirname "$0")) # Naik satu level dari scripts/
cd "$ROOT_DIR"

# Pastikan berada di root project
if [ ! -f "composer.json" ] || [ ! -f "package.json" ]; then
    echo -e "${RED}Error: Script harus dijalankan dari root project!${NC}"
    exit 1
fi

# 2. Simpan hash commit sebelumnya untuk dibandingkan nanti
BEFORE_COMMIT=$(git rev-parse HEAD)
echo "Commit hash sebelum update: $BEFORE_COMMIT"

# 3. Backup file penting
echo -e "${YELLOW}Membuat backup file konfigurasi penting...${NC}"
BACKUP_DIR="storage/backup/$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR
cp .env $BACKUP_DIR/.env.backup
if [ -f "public/build/manifest.json" ]; then
    cp public/build/manifest.json $BACKUP_DIR/manifest.json.backup
fi

# 4. Pull kode terbaru dari repository
echo -e "${YELLOW}Mengambil perubahan terbaru dari GitHub...${NC}"
git fetch
git status
echo -e "${GREEN}Mengambil update terbaru...${NC}"
git pull

# 5. Cek file yang berubah
AFTER_COMMIT=$(git rev-parse HEAD)
echo "Commit hash setelah update: $AFTER_COMMIT"

# 6. Jika tidak ada perubahan, tidak perlu melanjutkan
if [ "$BEFORE_COMMIT" == "$AFTER_COMMIT" ]; then
    echo -e "${GREEN}Tidak ada perubahan terdeteksi. Sistem sudah yang terbaru.${NC}"
    exit 0
fi

# 7. Tampilkan file yang berubah
echo -e "${YELLOW}File yang telah berubah:${NC}"
CHANGED_FILES=$(git diff --name-only $BEFORE_COMMIT $AFTER_COMMIT)
echo "$CHANGED_FILES"

# 8. Deteksi tipe perubahan yang terjadi
COMPOSER_CHANGES=false
NPM_CHANGES=false
MIGRATION_CHANGES=false
CONFIG_CHANGES=false
FRONTEND_CHANGES=false

echo -e "${YELLOW}Menganalisis perubahan...${NC}"

# Cek perubahan pada dependencies
if echo "$CHANGED_FILES" | grep -q "composer.json\|composer.lock"; then
    COMPOSER_CHANGES=true
    echo "- Perubahan pada dependensi PHP terdeteksi"
fi

if echo "$CHANGED_FILES" | grep -q "package.json\|package-lock.json\|yarn.lock"; then
    NPM_CHANGES=true
    echo "- Perubahan pada dependensi JavaScript terdeteksi"
fi

# Cek perubahan pada migrasi
if echo "$CHANGED_FILES" | grep -q "database/migrations/"; then
    MIGRATION_CHANGES=true
    echo "- Perubahan pada migrasi database terdeteksi"
fi

# Cek perubahan pada konfigurasi
if echo "$CHANGED_FILES" | grep -q "config/"; then
    CONFIG_CHANGES=true
    echo "- Perubahan pada file konfigurasi terdeteksi"
fi

# Cek perubahan pada frontend
if echo "$CHANGED_FILES" | grep -q "resources/js\|resources/css\|resources/views\|vite.config.js"; then
    FRONTEND_CHANGES=true
    echo "- Perubahan pada frontend terdeteksi"
fi

# 9. Proses update
echo -e "${GREEN}Memulai proses update...${NC}"

# 9a. Update dependensi PHP jika diperlukan
if [ "$COMPOSER_CHANGES" = true ]; then
    echo -e "${YELLOW}Mengupdate dependensi PHP...${NC}"
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "Melewati update composer (tidak ada perubahan)"
fi

# 9b. Update dependensi JavaScript jika diperlukan
if [ "$NPM_CHANGES" = true ]; then
    echo -e "${YELLOW}Mengupdate dependensi JavaScript...${NC}"
    npm ci --no-audit
else
    echo "Melewati update npm (tidak ada perubahan)"
fi

# 9c. Build frontend jika diperlukan
if [ "$FRONTEND_CHANGES" = true ] || [ "$NPM_CHANGES" = true ]; then
    echo -e "${YELLOW}Membangun ulang aset frontend...${NC}"
    npm run build
    
    # Pastikan manifest.json ada di lokasi yang benar
    if [ -f "public/build/.vite/manifest.json" ] && [ ! -f "public/build/manifest.json" ]; then
        echo "Menyalin Vite manifest..."
        cp public/build/.vite/manifest.json public/build/manifest.json
    fi
else
    echo "Melewati build frontend (tidak ada perubahan)"
fi

# 9d. Jalankan migrasi dalam mode safe jika diperlukan
if [ "$MIGRATION_CHANGES" = true ]; then
    echo -e "${YELLOW}Menjalankan migrasi database...${NC}"
    php artisan migrate --force --no-interaction
else
    echo "Melewati migrasi database (tidak ada perubahan)"
fi

# 9e. Clear cache jika ada perubahan konfigurasi
if [ "$CONFIG_CHANGES" = true ]; then
    echo -e "${YELLOW}Membersihkan cache aplikasi...${NC}"
    php artisan config:clear
    php artisan config:cache
    php artisan route:clear
    php artisan route:cache
    php artisan view:clear
    php artisan view:cache
else
    echo "Melewati clear cache (tidak ada perubahan konfigurasi)"
fi

# 10. Selalu optimize Laravel
echo -e "${YELLOW}Mengoptimalkan Laravel...${NC}"
php artisan optimize

# 11. Restart queue workers jika diperlukan
if [ "$COMPOSER_CHANGES" = true ] || [ "$CONFIG_CHANGES" = true ]; then
    echo -e "${YELLOW}Merestart queue workers...${NC}"
    php artisan queue:restart
fi

# 12. Symlink jika diperlukan
echo -e "${YELLOW}Memastikan storage link sudah ada...${NC}"
php artisan storage:link --quiet

# 13. Selesai
echo -e "${GREEN}====== UPDATE SELESAI ======${NC}"
echo "Update berhasil diterapkan dengan gangguan minimal."
echo "Waktu selesai: $(date +"%Y-%m-%d %H:%M:%S")"
echo -e "${GREEN}============================${NC}"

exit 0 