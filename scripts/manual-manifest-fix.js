#!/usr/bin/env node
/**
 * Script untuk membuat dan memperbaiki manifest.json secara manual
 * Gunakan jika proses build otomatis gagal
 * 
 * Cara penggunaan:
 * 1. node scripts/manual-manifest-fix.js
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Dapatkan __dirname di ESM
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');

// Path ke file manifest
const manifestDirs = [
  path.join(rootDir, 'public', 'build', '.vite'),
  path.join(rootDir, 'public', 'build'),
];

// Path ke direktori build
const buildDir = path.join(rootDir, 'public', 'build');

console.log('🔧 Manual Manifest Fix Tool 🔧');
console.log('------------------------------');

// 1. Periksa direktori build
if (!fs.existsSync(buildDir)) {
  console.error('❌ Direktori build tidak ditemukan!');
  console.log('👉 Jalankan "npm run build" terlebih dahulu');
  process.exit(1);
}

// 2. Temukan file CSS dan JS
console.log('🔍 Mencari file CSS dan JS di direktori build...');
const files = fs.readdirSync(buildDir);
let cssFiles = files.filter(file => file.endsWith('.css'));
let jsFiles = files.filter(file => 
  file.endsWith('.js') && 
  !file.includes('workbox-') && 
  !file.startsWith('sw.')
);

console.log(`   - Ditemukan ${cssFiles.length} file CSS`);
console.log(`   - Ditemukan ${jsFiles.length} file JS aplikasi`);

if (cssFiles.length === 0 || jsFiles.length === 0) {
  console.error('❌ Tidak cukup file untuk membuat manifest!');
  process.exit(1);
}

// 3. Cari file app.js utama (umumnya berukuran paling besar)
jsFiles.sort((a, b) => {
  const sizeA = fs.statSync(path.join(buildDir, a)).size;
  const sizeB = fs.statSync(path.join(buildDir, b)).size;
  return sizeB - sizeA;
});

const mainJs = jsFiles[0];
const mainCss = cssFiles[0];

console.log(`✓ File utama aplikasi terdeteksi:`);
console.log(`   - JS : ${mainJs}`);
console.log(`   - CSS: ${mainCss}`);

// 4. Buat manifest sederhana
const manifest = {
  "resources/css/app.css": {
    "file": mainCss,
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/js/app.js": {
    "file": mainJs,
    "isEntry": true,
    "src": "resources/js/app.js",
    "css": [mainCss]
  }
};

// 5. Tulis ke semua lokasi yang mungkin
let success = false;

for (const dir of manifestDirs) {
  try {
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
      console.log(`✓ Direktori ${dir} dibuat`);
    }
    
    const manifestPath = path.join(dir, 'manifest.json');
    fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));
    console.log(`✓ Manifest dibuat di ${manifestPath}`);
    
    // Set izin
    fs.chmodSync(manifestPath, 0o644);
    success = true;
  } catch (error) {
    console.error(`❌ Gagal menulis manifest di ${dir}: ${error.message}`);
  }
}

if (success) {
  console.log('✅ Manifest berhasil dibuat!');
  console.log('👉 Jalankan "php artisan cache:clear" untuk membersihkan cache Laravel');
} else {
  console.error('❌ Gagal membuat manifest di semua lokasi!');
  process.exit(1);
} 