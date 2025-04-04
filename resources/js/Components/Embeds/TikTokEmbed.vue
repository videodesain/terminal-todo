<!-- TikTokEmbed.vue - Komponen khusus untuk embed TikTok -->
<template>
  <div class="tiktok-embed-wrapper" :class="{ 'portrait-mode': orientation === 'portrait' }">
    <!-- Loading State -->
    <div v-if="loading" class="tiktok-embed-loading">
      <div class="loading-spinner"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="tiktok-embed-error">
      <p>{{ error }}</p>
      <button 
        @click="reloadEmbed"
        class="reload-button"
      >
        Coba Lagi
      </button>
    </div>
    
    <!-- TikTok Embed Content -->
    <div 
      v-else
      ref="tiktokContainer"
      class="tiktok-embed-container"
      v-html="html"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  url: {
    type: String,
    required: true
  },
  videoId: {
    type: String,
    default: null
  },
  html: {
    type: String,
    default: null
  },
  orientation: {
    type: String,
    default: 'landscape',
    validator: (value) => ['landscape', 'portrait'].includes(value)
  }
});

const tiktokContainer = ref(null);
const loading = ref(true);
const error = ref(null);
const tiktokLoaded = ref(false);

// Fungsi untuk memuat script TikTok
const loadTikTokScript = () => {
  return new Promise((resolve, reject) => {
    // Jika widget sudah dimuat, langsung resolve
    if (window.TikTok && tiktokLoaded.value) {
      resolve();
      return;
    }

    // Hapus script lama jika ada
    const existingScript = document.querySelector('script[src*="tiktok.com/embed.js"]');
    if (existingScript) {
      existingScript.remove();
    }

    // Buat script baru
    const script = document.createElement('script');
    script.src = 'https://www.tiktok.com/embed.js';
    script.async = true;

    // Handler untuk sukses
    script.onload = () => {
      let attempts = 0;
      const maxAttempts = 50;
      
      const checkTikTok = setInterval(() => {
        attempts++;
        if (window.TikTok) {
          clearInterval(checkTikTok);
          tiktokLoaded.value = true;
          resolve();
        } else if (attempts >= maxAttempts) {
          clearInterval(checkTikTok);
          console.warn('TikTok widget load delayed, continuing anyway');
          resolve();
        }
      }, 200);
    };

    // Handler untuk error
    script.onerror = (err) => {
      console.warn('TikTok script failed to load', err);
      reject(new Error('Failed to load TikTok embed script'));
    };

    // Tambahkan script ke document
    document.body.appendChild(script);
  });
};

// Fungsi untuk reload TikTok embeds
const reloadTikTokEmbed = () => {
  if (window.TikTok) {
    try {
      window.TikTok.reloadEmbeds();
      return true;
    } catch (error) {
      console.warn('Error reloading TikTok embeds:', error);
      return false;
    }
  }
  return false;
};

// Fungsi untuk reload embed jika terjadi error
const reloadEmbed = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    await loadTikTokScript();
    await nextTick();
    
    if (tiktokContainer.value) {
      // Setup MutationObserver untuk memantau perubahan
      const observer = new MutationObserver(() => {
        reloadTikTokEmbed();
      });

      observer.observe(tiktokContainer.value, {
        childList: true,
        subtree: true,
        attributes: true
      });

      // Multiple reload attempts dengan interval
      const reloadAttempts = [0, 1000, 2000, 3000];
      let success = false;
      
      for (const delay of reloadAttempts) {
        await new Promise(resolve => setTimeout(resolve, delay));
        success = reloadTikTokEmbed();
        if (success) break;
      }
      
      if (!success) {
        error.value = 'Tidak dapat memuat TikTok. Silakan coba lagi nanti.';
      }
    }
  } catch (err) {
    console.error('Failed to reload TikTok embed', err);
    error.value = 'Gagal memuat embed TikTok';
  } finally {
    loading.value = false;
  }
};

// Setup pada mount
onMounted(async () => {
  try {
    await loadTikTokScript();
    
    if (tiktokContainer.value) {
      // Setup MutationObserver untuk memantau perubahan
      const observer = new MutationObserver(() => {
        reloadTikTokEmbed();
      });

      observer.observe(tiktokContainer.value, {
        childList: true,
        subtree: true,
        attributes: true
      });

      // Multiple reload attempts dengan interval
      const reloadAttempts = [0, 1000, 2000, 3000];
      reloadAttempts.forEach(delay => {
        setTimeout(reloadTikTokEmbed, delay);
      });
      
      // Clean up observer pada unmount
      onUnmounted(() => {
        observer.disconnect();
      });
    }
  } catch (err) {
    console.error('Failed to initialize TikTok embed', err);
    error.value = 'Gagal memuat embed TikTok';
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.tiktok-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 550px;
  margin: 0 auto;
  min-height: 200px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.tiktok-embed-wrapper.portrait-mode {
  max-width: 340px;
  min-height: 750px;
}

.tiktok-embed-container {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 750px;
}

.tiktok-embed-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 750px;
  width: 100%;
  background-color: #f9fafb;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-top-color: #000000;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.tiktok-embed-error {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 750px;
  width: 100%;
  background-color: #f9fafb;
  color: #4b5563;
  text-align: center;
  padding: 1rem;
}

.reload-button {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #000000;
  color: white;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.reload-button:hover {
  background-color: #333333;
}

/* Menghilangkan scrollbar */
.tiktok-embed-container {
  -ms-overflow-style: none !important;
  scrollbar-width: none !important;
}

.tiktok-embed-container::-webkit-scrollbar {
  display: none !important;
}

/* Dark mode */
:deep(.dark) .tiktok-embed-loading,
:deep(.dark) .tiktok-embed-error {
  background-color: #1f2937;
  color: #9ca3af;
}

:deep(.dark) .loading-spinner {
  border-color: rgba(255, 255, 255, 0.1);
  border-top-color: #ffffff;
}
</style> 