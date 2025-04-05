<template>
  <div>
    <!-- Overlay latar belakang ketika pop-up aktif -->
    <div v-if="showInstallPrompt" class="fixed inset-0 bg-black bg-opacity-50 z-40" @click="closePromptTemporary"></div>

    <!-- Modal Pop-up PWA (ukuran diperkecil) -->
    <div v-if="showInstallPrompt" class="fixed inset-x-0 top-4 mx-auto w-10/12 max-w-sm bg-white dark:bg-gray-800 rounded-xl shadow-lg z-50 overflow-hidden">
      <!-- Header dengan Icon dan Close Button -->
      <div class="flex items-center justify-between bg-indigo-600 px-3 py-2 text-white">
        <div class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-base font-medium">Instal Terminal Todo</h3>
        </div>
        <button @click="closePromptTemporary" class="text-white hover:text-indigo-200">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body (diperkecil) -->
      <div class="px-4 py-3">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <img src="/icons/pwa-192x192.png" alt="Terminal Todo Logo" class="w-10 h-10 rounded-md">
          </div>
          <div class="ml-3">
            <p class="text-sm text-gray-800 dark:text-gray-200 mb-1">
              Instal aplikasi ini untuk akses lebih cepat
            </p>
          </div>
        </div>
      </div>

      <!-- Footer dengan tombol aksi -->
      <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 flex justify-end space-x-2">
        <button @click="closePromptUntilLogout" class="px-2 py-1 text-xs text-gray-700 dark:text-gray-300 hover:underline">
          Jangan tampilkan lagi
        </button>
        <button @click="installPWA" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
          Instal
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const showInstallPrompt = ref(false);
const deferredPrompt = ref(null);
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

// Tutup pop-up sementara (tidak muncul sampai refresh)
const closePromptTemporary = () => {
  showInstallPrompt.value = false;
};

// Tutup pop-up sampai logout
const closePromptUntilLogout = () => {
  showInstallPrompt.value = false;
  localStorage.setItem('pwaInstallPromptClosed', 'true');
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

// Cek jika user mengakses kembali
const checkReturningUser = () => {
  const lastVisit = localStorage.getItem('lastVisitTimestamp');
  const currentTime = new Date().getTime();
  
  if (lastVisit) {
    const timeDiff = currentTime - parseInt(lastVisit);
    const isNewVisit = timeDiff > (10 * 60 * 1000); // 10 menit
    
    if (isNewVisit) {
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
    return;
  }
  
  // Tampilkan prompt jika user kembali mengakses dan belum install
  if (checkReturningUser() && !isPWAInstallPromptClosed() && !isInstalled.value) {
    setTimeout(() => {
      showInstallPrompt.value = true;
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