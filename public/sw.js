// Terminal Todo PWA Service Worker

// Versi cache yang akan digunakan, sesuaikan dengan APP_VERSION di vite.config.js
const APP_VERSION = '1.0.0';
const CACHE_NAME = `terminal-todo-${APP_VERSION}`;
const OFFLINE_URL = '/offline.html';

// Daftar aset yang akan di-cache untuk penggunaan offline
const urlsToCache = [
  '/',
  OFFLINE_URL,
  '/icons/pwa-192x192.png',
  '/icons/pwa-512x512.png',
  '/build/assets/app.css',
  '/build/assets/app.js',
  '/manifest.json',
  '/favicon.ico'
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
      .catch(error => {
        console.error('Gagal melakukan caching aset:', error);
      })
  );
});

// Aktivasi service worker dan membersihkan cache lama
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.filter((name) => {
          return name.startsWith('terminal-todo-') && name !== CACHE_NAME;
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

  // URL absolut dari request
  const requestUrl = new URL(event.request.url);

  // Hindari cache untuk permintaan API dan admin panel
  if (requestUrl.pathname.startsWith('/api/') || 
      requestUrl.pathname.startsWith('/admin/') ||
      requestUrl.pathname.includes('login') ||
      requestUrl.pathname.includes('logout')) {
    return;
  }

  // Strategi NetworkFirst untuk navigasi halaman
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          return caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  // Strategi StaleWhileRevalidate untuk aset statis
  event.respondWith(
    caches.match(event.request)
      .then((cachedResponse) => {
        // Dapatkan cache dan update di background
        const fetchPromise = fetch(event.request)
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
              })
              .catch(error => {
                console.error('Gagal menyimpan ke cache:', error);
              });

            return networkResponse;
          })
          .catch(error => {
            console.error('Fetch gagal:', error);
          });

        // Kembalikan respons dari cache atau network
        return cachedResponse || fetchPromise;
      })
  );
});

// Event listener untuk skip waiting
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Sinkronisasi background (jika didukung browser)
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-data') {
    event.waitUntil(syncData());
  }
});

// Fungsi untuk sinkronisasi data (contoh)
const syncData = async () => {
  const dbName = 'offlineData';
  
  // Contoh implementasi sinkronisasi data dari IndexedDB
  try {
    // Kode untuk mengambil data dari IndexedDB dan mengirimkannya ke server
    console.log('Sinkronisasi data background berhasil');
  } catch (error) {
    console.error('Gagal melakukan sinkronisasi data:', error);
    return Promise.reject(error);
  }
};

// Notifikasi push (jika didukung browser)
self.addEventListener('push', (event) => {
  if (!event.data) return;

  const data = event.data.json();
  const options = {
    body: data.body || 'Notifikasi dari Terminal Todo',
    icon: '/icons/pwa-192x192.png',
    badge: '/icons/pwa-192x192.png',
    data: {
      url: data.url || '/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Terminal Todo', options)
  );
});

// Tindakan pada klik notifikasi
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  event.waitUntil(
    clients.matchAll({type: 'window'})
      .then((clientList) => {
        // Cek apakah ada tab terbuka
        for (const client of clientList) {
          if (client.url === event.notification.data.url && 'focus' in client) {
            return client.focus();
          }
        }
        
        // Jika tidak ada tab terbuka, buka tab baru
        if (clients.openWindow) {
          return clients.openWindow(event.notification.data.url);
        }
      })
  );
});
