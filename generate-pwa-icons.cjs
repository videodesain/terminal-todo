const fs = require('fs');
const path = require('path');

// Buat direktori jika belum ada
const iconsDir = path.join(__dirname, 'public', 'icons');
if (!fs.existsSync(iconsDir)) {
    fs.mkdirSync(iconsDir, { recursive: true });
}

// Buat file HTML dengan instruksi
const htmlPath = path.join(__dirname, 'pwa-icons-guide.html');
const htmlContent = `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Membuat Ikon PWA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #4F46E5;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }
        .icon-example {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .icon-example h3 {
            margin-top: 0;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .resource {
            background-color: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Panduan Membuat Ikon PWA</h1>
    
    <p>
        Untuk mengimplementasikan PWA di aplikasi Anda, Anda perlu membuat ikon-ikon dengan ukuran yang berbeda 
        dan meletakkannya di folder <code>public/icons/</code>.
    </p>

    <h2>Ukuran Ikon yang Diperlukan</h2>
    <div class="container">
        <div class="icon-example">
            <h3>192x192 piksel</h3>
            <p>Nama file: <code>pwa-192x192.png</code></p>
            <p>Untuk tampilan di homescreen Android</p>
        </div>
        <div class="icon-example">
            <h3>512x512 piksel</h3>
            <p>Nama file: <code>pwa-512x512.png</code></p>
            <p>Untuk splash screen dan ikon berukuran besar</p>
        </div>
    </div>

    <h2>Cara Membuat Ikon</h2>
    <p>Ada beberapa cara untuk membuat ikon PWA:</p>
    
    <div class="resource">
        <h3>1. Menggunakan Generator Online</h3>
        <ul>
            <li><a href="https://www.pwabuilder.com/imageGenerator" target="_blank">PWA Builder Image Generator</a></li>
            <li><a href="https://app-manifest.firebaseapp.com/" target="_blank">Web App Manifest Generator</a></li>
            <li><a href="https://realfavicongenerator.net/" target="_blank">Real Favicon Generator</a></li>
        </ul>
        <p>Upload logo Anda dan generator akan menghasilkan semua ukuran ikon yang diperlukan.</p>
    </div>

    <div class="resource">
        <h3>2. Menggunakan Alat Desain</h3>
        <ul>
            <li>Adobe Photoshop</li>
            <li>Figma</li>
            <li>GIMP (gratis dan open source)</li>
            <li>Inkscape (untuk grafik vektor)</li>
        </ul>
        <p>Buat desain dengan ukuran 512x512 piksel dan ekspor dalam berbagai ukuran yang diperlukan.</p>
    </div>

    <h2>Setelah Membuat Ikon</h2>
    <p>Setelah Anda membuat ikon-ikon tersebut:</p>
    <ol>
        <li>Letakkan file <code>pwa-192x192.png</code> dan <code>pwa-512x512.png</code> di folder <code>public/icons/</code></li>
        <li>File-file ini akan direferensikan oleh manifest.json dan digunakan saat pengguna menginstal aplikasi Anda</li>
    </ol>

    <h2>Contoh struktur folder:</h2>
    <pre>
public/
└── icons/
    ├── pwa-192x192.png
    └── pwa-512x512.png
    </pre>

    <p><strong>Catatan:</strong> Pastikan ikon-ikon Anda memiliki latar belakang dan tidak transparan untuk tampilan terbaik di berbagai platform.</p>
</body>
</html>`;

fs.writeFileSync(htmlPath, htmlContent);

// Buat placeholder icons (warna solid dengan teks)
console.log('Membuat placeholder untuk ikon PWA...');

// Catat instruksi di konsol
console.log(`
PWA Icons Generator (Alternatif Tanpa Canvas)
==============================================

File panduan HTML telah dibuat di: ${htmlPath}

Silakan buka file tersebut di browser Anda untuk melihat instruksi lengkap cara membuat ikon PWA.

Anda perlu membuat dan menyimpan ikon-ikon berikut secara manual:
- public/icons/pwa-192x192.png
- public/icons/pwa-512x512.png

Setelah ikon-ikon tersebut dibuat, PWA Anda akan siap digunakan.
`);

// Buat file placeholder.txt di folder icons
fs.writeFileSync(
    path.join(iconsDir, 'placeholder.txt'),
    'Letakkan ikon PWA Anda di folder ini dengan nama: pwa-192x192.png dan pwa-512x512.png'
);

console.log('Selesai! Silakan ikuti instruksi di file HTML yang dibuat.'); 