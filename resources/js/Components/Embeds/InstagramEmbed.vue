<!-- InstagramEmbed.vue - Komponen khusus untuk embed Instagram -->
<template>
  <div class="instagram-embed-wrapper" :class="{ 'portrait-mode': orientation === 'portrait' }">
    <!-- Loading State -->
    <div v-if="loading" class="instagram-embed-loading">
      <div class="loading-spinner"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="instagram-embed-error">
      <p>{{ error }}</p>
      <button 
        @click="reloadEmbed"
        class="reload-button"
      >
        Coba Lagi
      </button>
    </div>
    
    <!-- Embed dengan HTML disediakan langsung (dipilih jika tersedia) -->
    <div 
      v-else-if="html" 
      ref="instagramContainer" 
      class="instagram-embed-container"
      v-html="html"
    ></div>
    
    <!-- Fallback ke iframe jika tidak ada HTML langsung -->
    <iframe 
      v-else-if="embedUrl"
      :src="embedUrl"
      class="instagram-embed-iframe"
      :class="{ 'portrait-iframe': orientation === 'portrait' }"
      frameborder="0"
      scrolling="no"
      allowtransparency="true"
      allowfullscreen="true"
      @load="onIframeLoad"
      @error="onIframeError"
    ></iframe>
    
    <!-- Fallback jika semua cara lain gagal -->
    <div v-else class="instagram-embed-error">
      <p>Konten Instagram tidak tersedia</p>
      <a 
        :href="url" 
        target="_blank" 
        rel="noopener noreferrer"
        class="instagram-link"
      >
        Lihat di Instagram
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
  url: {
    type: String,
    required: true
  },
  html: {
    type: String,
    default: null
  },
  embedUrl: {
    type: String,
    default: null
  },
  orientation: {
    type: String,
    default: 'landscape',
    validator: (value) => ['landscape', 'portrait'].includes(value)
  }
});

const instagramContainer = ref(null);
const loading = ref(true);
const error = ref(null);

// Memuat script Instagram
const loadInstagramScript = () => {
  return new Promise((resolve, reject) => {
    // Cek apakah script sudah dimuat
    if (window.instgrm && window.instgrm.Embeds && window.instgrm.Embeds.process) {
      resolve();
      return;
    }
    
    // Hapus script lama jika ada
    const existingScript = document.querySelector('script[src*="instagram.com/embed.js"]');
    if (existingScript) {
      existingScript.remove();
    }
    
    // Buat script baru
    const script = document.createElement('script');
    script.async = true;
    script.defer = true;
    script.src = '//www.instagram.com/embed.js';
    
    script.onload = () => {
      // Tunggu sampai instgrm tersedia
      const checkInstagram = setInterval(() => {
        if (window.instgrm && window.instgrm.Embeds && window.instgrm.Embeds.process) {
          clearInterval(checkInstagram);
          resolve();
        }
      }, 200);
      
      // Set timeout jika terlalu lama
      setTimeout(() => {
        clearInterval(checkInstagram);
        if (window.instgrm && window.instgrm.Embeds && window.instgrm.Embeds.process) {
          resolve();
        } else {
          reject(new Error('Instagram Embeds failed to initialize in time'));
        }
      }, 5000);
    };
    
    script.onerror = (err) => {
      reject(new Error('Failed to load Instagram embed script'));
    };
    
    document.body.appendChild(script);
  });
};

// Proses embed Instagram
const processInstagramEmbeds = () => {
  if (window.instgrm && window.instgrm.Embeds && window.instgrm.Embeds.process) {
    window.instgrm.Embeds.process();
    return true;
  }
  return false;
};

// Event handler saat iframe dimuat
const onIframeLoad = () => {
  loading.value = false;
};

// Event handler saat iframe error
const onIframeError = (e) => {
  console.error('Instagram iframe failed to load', e);
  error.value = 'Gagal memuat embed Instagram';
  loading.value = false;
};

// Reload embed jika terjadi error
const reloadEmbed = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    await loadInstagramScript();
    await nextTick();
    
    if (instagramContainer.value) {
      const success = processInstagramEmbeds();
      if (!success) {
        error.value = 'Tidak dapat memuat Instagram. Silakan coba lagi nanti.';
      }
    }
  } catch (err) {
    console.error('Failed to reload Instagram embed', err);
    error.value = 'Gagal memuat embed Instagram';
  } finally {
    loading.value = false;
  }
};

// Setup pada mount
onMounted(async () => {
  try {
    if (props.html && instagramContainer.value) {
      await loadInstagramScript();
      processInstagramEmbeds();
    }
  } catch (err) {
    console.error('Failed to initialize Instagram embed', err);
    error.value = 'Gagal memuat embed Instagram';
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.instagram-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  min-height: 200px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.instagram-embed-wrapper.portrait-mode {
  max-width: 500px;
  min-height: 900px; /* Sesuaikan dengan Show.vue */
}

.instagram-embed-container {
  width: 100%;
  height: 100%;
  min-height: 500px;
  display: flex;
  justify-content: center;
  overflow: hidden;
}

.instagram-embed-wrapper.portrait-mode .instagram-embed-container {
  min-height: 900px; /* Sesuaikan dengan Show.vue */
}

.instagram-embed-iframe {
  width: 100%;
  min-height: 500px;
  border: none;
  background-color: white;
  border-radius: 0.5rem;
  overflow: hidden;
}

.instagram-embed-iframe.portrait-iframe {
  min-height: 900px; /* Sesuaikan dengan Show.vue */
  height: 100%;
}

.instagram-embed-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 300px;
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

.instagram-embed-error {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 300px;
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

.instagram-link {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #E1306C;
  color: white;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
  text-decoration: none;
}

.instagram-link:hover {
  background-color: #c13584;
}

/* Dark mode */
:deep(.dark) .instagram-embed-loading,
:deep(.dark) .instagram-embed-error {
  background-color: #1f2937;
  color: #9ca3af;
}

:deep(.dark) .loading-spinner {
  border-color: rgba(255, 255, 255, 0.1);
  border-top-color: #ffffff;
}
</style> 