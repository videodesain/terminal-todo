#!/usr/bin/env node
/**
 * Script untuk memastikan manifest.json selalu tersedia di lokasi yang benar
 * setelah build Vite. Ini menyelesaikan masalah di mana Vite menyimpan manifest
 * di .vite/manifest.json tetapi Laravel mencarinya di build/manifest.json
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Dapatkan __dirname di ESM
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Path absolut ke root project 
const rootDir = path.resolve(__dirname, '..');
const viteManifestPath = path.join(rootDir, 'public', 'build', '.vite', 'manifest.json');
const laravelManifestPath = path.join(rootDir, 'public', 'build', 'manifest.json');

console.log('🔍 Memastikan manifest.json tersedia untuk Laravel...');

try {
  // Periksa apakah file manifest Vite ada
  if (fs.existsSync(viteManifestPath)) {
    console.log(`✓ Manifest Vite ditemukan di ${viteManifestPath}`);
    
    // Baca isi file manifest Vite
    const manifestContent = fs.readFileSync(viteManifestPath, 'utf8');
    
    // Buat direktori parent jika belum ada
    const parentDir = path.dirname(laravelManifestPath);
    if (!fs.existsSync(parentDir)) {
      fs.mkdirSync(parentDir, { recursive: true });
      console.log(`✓ Direktori ${parentDir} dibuat`);
    }
    
    // Salin isi ke file manifest Laravel
    fs.writeFileSync(laravelManifestPath, manifestContent, { mode: 0o644 });
    console.log(`✓ Manifest disalin ke ${laravelManifestPath}`);
    
    // Ubah mode file untuk memastikan izin yang benar
    fs.chmodSync(laravelManifestPath, 0o644);
    console.log('✓ Izin file diatur ke 644');
    
    console.log('✅ Manifest berhasil disediakan untuk Laravel!');
  } else {
    console.error(`❌ Manifest Vite tidak ditemukan di ${viteManifestPath}`);
    
    // Periksa keberadaan build directory
    const buildDir = path.join(rootDir, 'public', 'build');
    if (!fs.existsSync(buildDir)) {
      console.error(`❌ Direktori build tidak ditemukan. Jalankan 'npm run build' terlebih dahulu.`);
    } else {
      console.error(`❌ Periksa konfigurasi Vite untuk memastikan manifest dihasilkan dengan benar.`);
    }
    
    process.exit(1);
  }
} catch (error) {
  console.error(`❌ Error: ${error.message}`);
  process.exit(1);
} 