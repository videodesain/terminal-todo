// Terminal Todo PWA Service Worker

const CACHE_NAME = 'terminal-todo-v1';
const OFFLINE_URL = '/offline.html';

// Daftar aset yang akan di-cache untuk penggunaan offline
const urlsToCache = [
  '/',
  OFFLINE_URL,
  '/icons/pwa-192x192.png',
  '/icons/pwa-512x512.png',
  '/build/assets/app.css',
  '/build/assets/app.js'
];

// Install service worker dan cache aset utama
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache dibuka');
        return cache.addAll(urlsToCache);
      })
      .then(() => {
        return self.skipWaiting();
      })
  );
});

// Aktivasi service worker dan membersihkan cache lama
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.filter((name) => {
          return name !== CACHE_NAME;
        }).map((name) => {
          console.log('Menghapus cache lama:', name);
          return caches.delete(name);
        })
      );
    }).then(() => {
      return self.clients.claim();
    })
  );
});

// Strategi cache untuk fetch requests
self.addEventListener('fetch', (event) => {
  // Hanya menangani permintaan GET
  if (event.request.method !== 'GET') return;

  // Hindari cache untuk permintaan API
  if (event.request.url.includes('/api/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Kembalikan cache jika ditemukan
        if (response) {
          return response;
        }

        // Jika tidak ditemukan di cache, ambil dari jaringan
        return fetch(event.request)
          .then((networkResponse) => {
            // Jangan cache respons yang bukan 200 OK
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }

            // Clone respons karena respons adalah stream yang hanya bisa dibaca sekali
            const responseToCache = networkResponse.clone();

            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });

            return networkResponse;
          })
          .catch(() => {
            // Jika navigasi ke halaman gagal karena offline, tampilkan halaman offline
            if (event.request.mode === 'navigate') {
              return caches.match(OFFLINE_URL);
            }
            
            // Jika bukan navigasi, coba ambil aset dari cache
            return caches.match(event.request);
          });
      })
  );
});

// Event listener untuk skip waiting
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
