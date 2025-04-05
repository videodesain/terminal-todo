<template>
  <div v-if="showInstallPrompt" class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md bg-indigo-600 text-white p-4 rounded-lg shadow-lg z-50">
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <div class="mr-3">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <div class="font-medium">Instal Terminal Todo</div>
          <div class="text-sm opacity-90">Tambahkan ke layar utama untuk akses lebih cepat</div>
        </div>
      </div>
      <div class="flex items-center">
        <button @click="installPWA" class="bg-white text-indigo-600 px-3 py-1 rounded mr-2 text-sm font-medium hover:bg-indigo-50">
          Instal
        </button>
        <button @click="closePrompt" class="text-white hover:text-indigo-200 p-1">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const showInstallPrompt = ref(false);
const deferredPrompt = ref(null);

// Periksa apakah notifikasi sudah ditutup sebelumnya
const isPWAInstallPromptClosed = () => {
  return localStorage.getItem('pwaInstallPromptClosed') === 'true';
};

// Penanganan event beforeinstallprompt
const beforeInstallPromptHandler = (e) => {
  // Cegah Chrome 67+ menampilkan prompt instalasi otomatis
  e.preventDefault();
  // Simpan event untuk dipicu nanti
  deferredPrompt.value = e;
  // Tampilkan notifikasi jika belum pernah ditutup
  if (!isPWAInstallPromptClosed()) {
    showInstallPrompt.value = true;
  }
};

// Tutup notifikasi
const closePrompt = () => {
  showInstallPrompt.value = false;
  localStorage.setItem('pwaInstallPromptClosed', 'true');
};

// Reset penanda setiap minggu (opsional)
const resetPromptClosedStatus = () => {
  const lastReset = localStorage.getItem('pwaPromptLastReset');
  const now = new Date().getTime();
  const oneWeek = 7 * 24 * 60 * 60 * 1000;
  
  if (!lastReset || now - parseInt(lastReset) > oneWeek) {
    localStorage.removeItem('pwaInstallPromptClosed');
    localStorage.setItem('pwaPromptLastReset', now.toString());
  }
};

// Instal PWA
const installPWA = async () => {
  if (!deferredPrompt.value) {
    return;
  }
  
  // Tampilkan prompt instalasi
  deferredPrompt.value.prompt();
  
  // Tunggu pengguna merespons prompt
  const { outcome } = await deferredPrompt.value.userChoice;
  console.log(`Hasil prompt instalasi: ${outcome}`);
  
  // Bersihkan deferredPrompt
  deferredPrompt.value = null;
  
  // Sembunyikan notifikasi
  showInstallPrompt.value = false;
};

onMounted(() => {
  // Reset status penanda notifikasi ditutup jika sudah seminggu
  resetPromptClosedStatus();
  
  // Tambahkan listener untuk beforeinstallprompt
  window.addEventListener('beforeinstallprompt', beforeInstallPromptHandler);
  
  // Periksa apakah PWA sudah terinstal
  window.addEventListener('appinstalled', () => {
    console.log('PWA sudah terinstal');
    showInstallPrompt.value = false;
  });
});

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', beforeInstallPromptHandler);
});
</script> 