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
          <h3 class="text-lg font-medium">Instal {{ appName }}</h3>
        </div>
        <button @click="closePromptTemporary" class="text-white hover:text-indigo-200">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 py-5">
        <div class="flex items-center">
          <div class="flex-shrink-0 mt-1">
            <img src="/icons/pwa-192x192.png" :alt="`${appName} Logo`" class="w-10 h-10 rounded-lg">
          </div>
          <div class="ml-4">
            <p class="text-gray-800 dark:text-gray-200">
              Instal <strong>{{ appName }}</strong> ke perangkat Anda untuk:
            </p>
           
          </div>
        </div>
      </div>

      <!-- Footer dengan tombol aksi -->
      <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-between">
        <div>
          <button @click="remindLater" class="text-gray-600 py-1.5 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 text-sm">
            Nanti Saja
          </button>
        </div>
        <div class="space-x-2">
          <button @click="closePromptUntilLogout" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-600">
            Tutup Dulu
          </button>
          <button @click="installPWA" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
            Install
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const showInstallPrompt = ref(false);
const deferredPrompt = ref(null);
const isStandalone = ref(false);
const isDesktop = ref(false);
const isChrome = ref(false);
const isEdge = ref(false);
const isFirefox = ref(false);
const isSafari = ref(false);

// Get app name from environment variable
const appName = ref(import.meta.env.VITE_APP_NAME || 'Terminal Todo');

// Deteksi platform dan browser
const detectPlatformAndBrowser = () => {
  const ua = navigator.userAgent;
  
  // Deteksi platform
  isDesktop.value = !(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua));
  
  // Deteksi browser
  isChrome.value = /Chrome/.test(ua) && !/Edge|Edg/.test(ua);
  isEdge.value = /Edge|Edg/.test(ua);
  isFirefox.value = /Firefox/.test(ua);
  isSafari.value = /Safari/.test(ua) && !/Chrome/.test(ua);
  
  // Deteksi mode standalone
  isStandalone.value = 
    window.matchMedia('(display-mode: standalone)').matches || 
    window.navigator.standalone || 
    document.referrer.includes('android-app://');
  
  console.log('Platform detection:', {
    isDesktop: isDesktop.value,
    isChrome: isChrome.value,
    isEdge: isEdge.value,
    isFirefox: isFirefox.value,
    isSafari: isSafari.value,
    isStandalone: isStandalone.value
  });
};

// Cek apakah aplikasi dapat diinstal di browser saat ini
const isInstallable = computed(() => {
  // Mobile devices
  if (!isDesktop.value) return true;
  
  // Desktop - restrict to supported browsers
  if (isDesktop.value) {
    return isChrome.value || isEdge.value || isFirefox.value;
  }
  
  return false;
});

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

// Tampilkan prompt dengan delay untuk menghindari masalah timing
const showPromptWithDelay = (delay = 2000) => {
  if (isStandalone.value) {
    console.log('App already installed as standalone, not showing prompt');
    return;
  }
  
  if (!isInstallable.value) {
    console.log('Browser tidak mendukung PWA installation');
    return;
  }
  
  if (isPWAInstallPromptClosed() || isPWAInstallPromptDelayed()) {
    console.log('User closed or delayed prompt before');
    return;
  }
  
  setTimeout(() => {
    showInstallPrompt.value = true;
    console.log('Showing install prompt');
  }, delay);
};

// Handler untuk event beforeinstallprompt
const beforeInstallPromptHandler = (e) => {
  // Cegah Chrome menampilkan prompt instalasi otomatis
  e.preventDefault();
  
  console.log('beforeinstallprompt event fired!');
  
  // Simpan event untuk dipicu nanti
  deferredPrompt.value = e;
  
  showPromptWithDelay();
};

// Manual trigger untuk platform yang tidak mendukung beforeinstallprompt
const checkInstallationEligibility = () => {
  // Jika browser tidak mendukung PWA atau sudah diinstal, tidak perlu tampilkan prompt
  if (isStandalone.value || !isInstallable.value) {
    return;
  }
  
  // Jika tidak ada deferredPrompt (Safari/iOS atau some Firefox)
  if (!deferredPrompt.value) {
    // Untuk Safari di iOS/MacOS, tampilkan instruksi manual
    if (isSafari.value) {
      showPromptWithDelay(3000);
    }
    
    // Deteksi Firefox di desktop
    if (isFirefox.value && isDesktop.value) {
      showPromptWithDelay(3000);
    }
  }
};

// Ingatkan nanti (3 jam kemudian)
const remindLater = () => {
  showInstallPrompt.value = false;
  
  // Set waktu pengingat 3 jam dari sekarang
  const threeHoursLater = new Date().getTime() + (3 * 60 * 60 * 1000);
  localStorage.setItem('pwaInstallPromptDelayed', threeHoursLater.toString());
  
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
  if (deferredPrompt.value) {
    // Chrome dan browser berbasis Chrome - gunakan prompt API
    try {
      // Tampilkan prompt instalasi native
      deferredPrompt.value.prompt();
      
      // Tunggu pengguna merespons prompt
      const { outcome } = await deferredPrompt.value.userChoice;
      console.log(`Hasil prompt instalasi: ${outcome}`);
      
      // Jika berhasil diinstal
      if (outcome === 'accepted') {
        // Tambahkan pesan toast jika tersedia
        if (window.$toast) {
          window.$toast.success('Terima kasih! Aplikasi sedang diinstal');
        }
      }
      
      // Bersihkan deferredPrompt
      deferredPrompt.value = null;
    } catch (error) {
      console.error('Error saat menginstal PWA:', error);
    }
  } else if (isSafari.value) {
    // Safari - tampilkan instruksi manual
    const message = isDesktop.value ? 
      'Untuk menginstal, klik "Share" di toolbar browser lalu pilih "Add to Home Screen"' :
      'Ketuk icon Share/Bagikan di browser, lalu pilih "Add to Home Screen"';
    
    if (window.$toast) {
      window.$toast.info(message, { duration: 8000 });
    } else {
      alert(message);
    }
  } else if (isFirefox.value) {
    // Firefox - tampilkan instruksi khusus
    const message = isDesktop.value ? 
      'Klik icon menu (3 garis) di kanan atas, lalu pilih "Install Site to Applications"' :
      'Ketuk menu (3 titik) lalu pilih "Install"';
    
    if (window.$toast) {
      window.$toast.info(message, { duration: 8000 });
    } else {
      alert(message);
    }
  }
  
  // Sembunyikan pop-up
  showInstallPrompt.value = false;
};

// Reset semua status PWA saat logout
const resetPWAPromptStatus = () => {
  localStorage.removeItem('pwaInstallPromptClosed');
  localStorage.removeItem('pwaInstallPromptDelayed');
};

onMounted(() => {
  // Deteksi platform dan browser
  detectPlatformAndBrowser();
  
  // Tambahkan listener untuk beforeinstallprompt
  window.addEventListener('beforeinstallprompt', beforeInstallPromptHandler);
  
  // Periksa apakah PWA sudah terinstal
  window.addEventListener('appinstalled', () => {
    console.log('PWA berhasil diinstal');
    isStandalone.value = true;
    showInstallPrompt.value = false;
    
    // Tambahkan pesan toast jika tersedia
    if (window.$toast) {
      window.$toast.success('Aplikasi berhasil diinstal!');
    }
  });
  
  // Untuk browser yang tidak mendukung beforeinstallprompt
  // Periksa eligibility setelah page load
  nextTick(() => {
    setTimeout(() => {
      checkInstallationEligibility();
    }, 3000);
  });
  
  // Listen untuk perubahan display mode (jika PWA sudah diinstal)
  const mediaQuery = window.matchMedia('(display-mode: standalone)');
  mediaQuery.addEventListener('change', (e) => {
    isStandalone.value = e.matches;
    if (isStandalone.value) {
      showInstallPrompt.value = false;
    }
  });
});

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', beforeInstallPromptHandler);
  
  // Cleanup media query listener
  const mediaQuery = window.matchMedia('(display-mode: standalone)');
  mediaQuery.removeEventListener('change', () => {});
});

// Ekspos fungsi resetPWAPromptStatus agar dapat diimpor dari komponen lain
defineExpose({
  resetPWAPromptStatus
});
</script> 