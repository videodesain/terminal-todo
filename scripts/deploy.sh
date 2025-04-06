#!/bin/bash
# Script untuk men-deploy aplikasi dengan benar
# dan memastikan manifest.json selalu tersedia

# Exit jika ada error
set -e

echo "===== Starting deployment process ====="

# Pastikan kita berada di root project
if [ ! -f "composer.json" ] || [ ! -f "package.json" ]; then
    echo "❌ Error: Run this script from the project root folder!"
    exit 1
fi

# 1. Install dependencies
echo "🔧 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔧 Installing Node.js dependencies..."
npm ci

# 2. Build assets
echo "🔨 Building frontend assets..."
npm run build

# 3. Pastikan manifest.json tersedia dengan izin yang benar
echo "🔍 Memastikan manifest.json tersedia..."

MANIFEST_PATH="public/build/manifest.json"
ALT_MANIFEST_PATH="public/build/.vite/manifest.json"

if [ -f "$ALT_MANIFEST_PATH" ]; then
    echo "✓ Manifest ditemukan di $ALT_MANIFEST_PATH"
    
    # Buat direktori jika belum ada
    mkdir -p "$(dirname "$MANIFEST_PATH")"
    
    # Salin manifest
    cp "$ALT_MANIFEST_PATH" "$MANIFEST_PATH"
    
    # Tetapkan izin yang benar
    chmod 644 "$MANIFEST_PATH"
    
    echo "✓ Manifest disalin ke $MANIFEST_PATH dengan izin yang benar"
else
    if [ ! -f "$MANIFEST_PATH" ]; then
        echo "❌ Warning: Tidak dapat menemukan manifest.json!"
        echo "❌ Build frontend mungkin gagal. Periksa log untuk detail."
    else
        echo "✓ Manifest sudah tersedia di lokasi yang benar"
    fi
fi

# 4. Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Clear caches yang mungkin masih menggunakan versi lama
echo "🧹 Membersihkan cache yang tidak diperlukan..."
php artisan cache:clear

echo "✅ Deployment completed successfully!"
echo "==================================="

exit 0 