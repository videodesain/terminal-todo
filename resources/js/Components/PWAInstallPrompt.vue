<template>
  <div>
    <!-- Overlay latar belakang ketika pop-up aktif -->
    <div v-if="showInstallPrompt" class="fixed inset-0 bg-black bg-opacity-50 z-40" @click="closePromptTemporary"></div>

    <!-- Modal Pop-up PWA -->
    <div v-if="showInstallPrompt" class="fixed inset-x-0 top-1/4 mx-auto w-11/12 max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl z-50 overflow-hidden">
      <!-- Header dengan Icon dan Close Button -->
      <div class="flex items-center justify-between bg-indigo-600 px-4 py-3 text-white">
        <div class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-lg font-medium">Instal Terminal Todo</h3>
        </div>
        <button @click="closePromptTemporary" class="text-white hover:text-indigo-200">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 py-5">
        <div class="flex items-start mb-4">
          <div class="flex-shrink-0 mt-1">
            <img src="/icons/pwa-192x192.png" alt="Terminal Todo Logo" class="w-14 h-14 rounded-lg">
          </div>
          <div class="ml-4">
            <p class="text-gray-800 dark:text-gray-200 mb-2">
              Instal <strong>Terminal Todo</strong> ke perangkat Anda untuk:
            </p>
            <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-300 space-y-1">
              <li>Akses lebih cepat dari layar utama</li>
              <li>Bekerja lebih baik saat offline</li>
              <li>Pengalaman seperti aplikasi native</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Footer dengan tombol aksi -->
      <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-between">
        <div>
          <button @click="remindLater" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 text-sm">
            Ingatkan nanti
          </button>
        </div>
        <div class="space-x-2">
          <button @click="closePromptUntilLogout" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-600">
            Jangan tampilkan lagi
          </button>
          <button @click="installPWA" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
            Instal sekarang
          </button>
        </div>
      </div>
    </div>

    <!-- Floating Mini Button untuk menampilkan prompt secara manual -->
    <button 
      v-if="!showInstallPrompt && hasInstallEvent && !isInstalled" 
      @click="showPrompt" 
      class="fixed bottom-4 right-4 bg-indigo-600 text-white rounded-full p-3 shadow-lg z-30 hover:bg-indigo-700 transition-all duration-300"
      title="Instal aplikasi"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const showInstallPrompt = ref(false);
const deferredPrompt = ref(null);
const hasInstallEvent = ref(false);
const isInstalled = ref(false);

// Cek status PWA berdasarkan localStorage
const isPWAInstallPromptClosed = () => {
  return localStorage.getItem('pwaInstallPromptClosed') === 'true';
};

const isPWAInstallPromptDelayed = () => {
  const delayedUntil = localStorage.getItem('pwaInstallPromptDelayed');
  if (!delayedUntil) return false;
  
  // Cek apakah masa tunda sudah berakhir
  return parseInt(delayedUntil) > new Date().getTime();
};

// Handler untuk event beforeinstallprompt
const beforeInstallPromptHandler = (e) => {
  console.log('beforeinstallprompt event fired');
  // Cegah Chrome menampilkan prompt instalasi otomatis
  e.preventDefault();
  
  // Simpan event untuk dipicu nanti
  deferredPrompt.value = e;
  hasInstallEvent.value = true;
  
  // Simpan ke localStorage bahwa event install tersedia
  localStorage.setItem('pwaInstallAvailable', 'true');
  
  // Tampilkan pop-up jika belum pernah ditutup dan tidak ditunda
  if (!isPWAInstallPromptClosed() && !isPWAInstallPromptDelayed()) {
    // Tunda beberapa detik agar tidak langsung muncul saat load
    setTimeout(() => {
      if (!isInstalled.value) {
        showInstallPrompt.value = true;
      }
    }, 1000);
  }
};

// Tampilkan prompt secara manual
const showPrompt = () => {
  showInstallPrompt.value = true;
};

// Ingatkan nanti (1 jam kemudian)
const remindLater = () => {
  showInstallPrompt.value = false;
  
  // Set waktu pengingat 1 jam dari sekarang (lebih singkat dari sebelumnya)
  const oneHourLater = new Date().getTime() + (1 * 60 * 60 * 1000);
  localStorage.setItem('pwaInstallPromptDelayed', oneHourLater.toString());
  
  // Tambahkan pesan toast jika tersedia
  if (window.$toast) {
    window.$toast.info('Kami akan mengingatkan Anda nanti untuk menginstal aplikasi');
  }
};

// Tutup pop-up sementara (tidak muncul sampai refresh)
const closePromptTemporary = () => {
  showInstallPrompt.value = false;
};

// Tutup pop-up sampai logout
const closePromptUntilLogout = () => {
  showInstallPrompt.value = false;
  localStorage.setItem('pwaInstallPromptClosed', 'true');
  
  // Tambahkan pesan toast jika tersedia
  if (window.$toast) {
    window.$toast.success('Pop-up tidak akan muncul lagi sampai Anda logout');
  }
};

// Instal PWA
const installPWA = async () => {
  if (!deferredPrompt.value) {
    console.error('No deferredPrompt available');
    return;
  }
  
  // Tampilkan prompt instalasi native
  deferredPrompt.value.prompt();
  
  // Tunggu pengguna merespons prompt
  const { outcome } = await deferredPrompt.value.userChoice;
  console.log(`Hasil prompt instalasi: ${outcome}`);
  
  // Jika berhasil diinstal
  if (outcome === 'accepted') {
    isInstalled.value = true;
    // Tambahkan pesan toast jika tersedia
    if (window.$toast) {
      window.$toast.success('Terima kasih! Aplikasi sedang diinstal');
    }
  }
  
  // Bersihkan deferredPrompt
  deferredPrompt.value = null;
  
  // Sembunyikan pop-up
  showInstallPrompt.value = false;
};

// Cek apakah aplikasi sudah diinstal sebagai PWA
const checkIfInstalledAsPWA = () => {
  // Pengguna mengakses dari standalone mode (PWA diinstal)
  if (window.matchMedia('(display-mode: standalone)').matches) {
    isInstalled.value = true;
    return true;
  }
  
  // Pada iOS jika diakses dari 'Add to Home Screen'
  if (window.navigator.standalone) {
    isInstalled.value = true;
    return true;
  }
  
  return false;
};

// Reset semua status PWA saat logout
const resetPWAPromptStatus = () => {
  localStorage.removeItem('pwaInstallPromptClosed');
  localStorage.removeItem('pwaInstallPromptDelayed');
};

// Cek jika delayed prompt sudah waktunya muncul lagi
const checkDelayedPrompt = () => {
  const delayedUntil = localStorage.getItem('pwaInstallPromptDelayed');
  
  // Jika tidak ada waktu delay tersimpan tapi PWA belum diinstall, tampilkan prompt
  if (!delayedUntil && hasInstallEvent.value && !isInstalled.value && !isPWAInstallPromptClosed()) {
    return true;
  }
  
  // Jika waktu delay telah berakhir atau user mengakses kembali setelah delay
  if (delayedUntil) {
    const lastVisit = localStorage.getItem('lastVisitTimestamp');
    const currentTime = new Date().getTime();
    
    // Jika ini kunjungan baru (bukan sesi berkelanjutan)
    if (lastVisit && (currentTime - parseInt(lastVisit)) > (10 * 60 * 1000)) { // 10 menit
      localStorage.removeItem('pwaInstallPromptDelayed');
      return true;
    }
    
    // Atau delay sudah berakhir
    if (parseInt(delayedUntil) <= currentTime) {
      localStorage.removeItem('pwaInstallPromptDelayed');
      return true;
    }
  }
  
  return false;
};

onMounted(() => {
  console.log('PWAInstallPrompt component mounted');
  
  // Update timestamp kunjungan terakhir
  localStorage.setItem('lastVisitTimestamp', new Date().getTime().toString());
  
  // Cek apakah sudah diinstal sebagai PWA
  if (checkIfInstalledAsPWA()) {
    console.log('Application is already installed as PWA');
    isInstalled.value = true;
    // Reset semua prompt karena sudah terinstall
    localStorage.removeItem('pwaInstallPromptDelayed');
    localStorage.removeItem('pwaInstallPromptClosed');
    return;
  }
  
  // Cek jika install event sudah tersimpan di localStorage
  if (localStorage.getItem('pwaInstallAvailable') === 'true') {
    hasInstallEvent.value = true;
  }
  
  // Cek apakah prompt delay sudah berakhir atau user baru mengakses kembali
  if (checkDelayedPrompt()) {
    // Tampilkan prompt dengan delay kecil
    setTimeout(() => {
      if (!isInstalled.value) {
        showInstallPrompt.value = true;
      }
    }, 1000);
  }
  
  // Tambahkan listener untuk beforeinstallprompt
  window.addEventListener('beforeinstallprompt', beforeInstallPromptHandler);
  
  // Periksa apakah PWA sudah terinstal
  window.addEventListener('appinstalled', () => {
    console.log('PWA berhasil diinstal');
    isInstalled.value = true;
    showInstallPrompt.value = false;
    
    // Hapus semua flag karena PWA sudah terinstal
    localStorage.removeItem('pwaInstallPromptDelayed');
    localStorage.removeItem('pwaInstallPromptClosed');
    localStorage.removeItem('pwaInstallAvailable');
    
    // Tambahkan pesan toast jika tersedia
    if (window.$toast) {
      window.$toast.success('Aplikasi berhasil diinstal!');
    }
  });
});

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', beforeInstallPromptHandler);
});

// Force munculkan prompt secara manual - bisa dipanggil dari luar
const forceShowPrompt = () => {
  if (!isInstalled.value) {
    showInstallPrompt.value = true;
  }
};

// Ekspos fungsi-fungsi yang bisa diakses dari luar
defineExpose({
  resetPWAPromptStatus,
  forceShowPrompt,
  installPWA
});
</script> 