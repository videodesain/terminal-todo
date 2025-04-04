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
    
    <!-- Direct Iframe Embed -->
    <div v-else-if="useDirectIframe" class="tiktok-direct-embed">
      <iframe 
        :src="directIframeUrl" 
        class="tiktok-iframe"
        :class="{ 'portrait-iframe': orientation === 'portrait' }"
        frameborder="0" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
      ></iframe>
    </div>
    
    <!-- TikTok Embed Content -->
    <div 
      v-else
      ref="tiktokContainer"
      class="tiktok-embed-container"
      v-html="generatedHtml"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';

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
const useDirectIframe = ref(true); // Tetap true untuk selalu menggunakan iframe langsung

// Ekstrak username dan videoId dari URL jika belum ada
const extractedInfo = computed(() => {
  let videoId = props.videoId;
  let username = null;
  
  if (!videoId && props.url) {
    // Coba ekstrak videoId dari URL
    const videoMatch = props.url.match(/\/video\/(\d+)/);
    if (videoMatch && videoMatch[1]) {
      videoId = videoMatch[1];
    }
    
    // Coba ekstrak username dari URL
    const usernameMatch = props.url.match(/tiktok\.com\/@([^\/]+)/);
    if (usernameMatch && usernameMatch[1]) {
      username = usernameMatch[1];
    }
  }
  
  return { videoId, username };
});

// Buat URL untuk iframe langsung - tanpa parameter lang=id
const directIframeUrl = computed(() => {
  const { videoId, username } = extractedInfo.value;
  
  if (videoId && username) {
    return `https://www.tiktok.com/embed/v2/${videoId}`;
  } else if (videoId) {
    return `https://www.tiktok.com/embed/v2/${videoId}`;
  } else {
    // Jika tidak ada ID, gunakan URL langsung tanpa parameter tambahan
    const baseUrl = new URL(props.url);
    // Pastikan tidak ada duplikasi parameter
    if (!baseUrl.searchParams.has('embedFrom')) {
      baseUrl.searchParams.append('embedFrom', 'oembed');
    }
    return baseUrl.toString();
  }
});

// Buat HTML untuk embed jika tidak disediakan
const generatedHtml = computed(() => {
  // Gunakan HTML yang disediakan jika ada
  if (props.html) {
    return props.html;
  }
  
  // Buat HTML berbasis URL jika tidak ada HTML
  const { videoId, username } = extractedInfo.value;
  
  if (videoId) {
    // Format standar embed TikTok
    if (username) {
      return `
        <blockquote class="tiktok-embed" cite="https://www.tiktok.com/@${username}/video/${videoId}" 
          data-video-id="${videoId}" 
          style="max-width: 605px; min-width: 325px;">
          <section></section>
        </blockquote>
      `;
    } else {
      // Jika tidak ada username, gunakan format minimal
      return `
        <blockquote class="tiktok-embed" cite="https://www.tiktok.com/video/${videoId}" 
          data-video-id="${videoId}" 
          style="max-width: 605px; min-width: 325px;">
          <section></section>
        </blockquote>
      `;
    }
  }
  
  // Gunakan URL langsung jika tidak ada videoId
  if (props.url) {
    return `
      <blockquote class="tiktok-embed" cite="${props.url}" 
        style="max-width: 605px; min-width: 325px;">
        <section></section>
      </blockquote>
    `;
  }
  
  return null;
});

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
  
  // Coba menggunakan metode iframe langsung
  useDirectIframe.value = true;
  
  setTimeout(() => {
    loading.value = false;
  }, 1000);
};

// Setup saat komponen di-mount
onMounted(async () => {
  try {
    console.log('TikTok embed mounting with URL:', props.url, 'Orientation:', props.orientation);
    
    // Gunakan metode iframe langsung secara default
    if (useDirectIframe.value) {
      console.log('Using direct iframe with URL:', directIframeUrl.value);
      setTimeout(() => {
        loading.value = false;
      }, 1000);
      return;
    }
    
    // Kode lama untuk pendekatan blockquote
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
      const reloadAttempts = [0, 1000, 2000, 3000, 5000]; // Tambah interval lebih lama
      let intervalIds = [];
      
      reloadAttempts.forEach((delay, index) => {
        const intervalId = setTimeout(() => {
          reloadTikTokEmbed();
          
          // Pada percobaan terakhir, cek apakah embed berhasil
          if (index === reloadAttempts.length - 1) {
            // Jika container kosong, munculkan error
            if (tiktokContainer.value && 
                (!tiktokContainer.value.querySelector('iframe') || 
                 tiktokContainer.value.innerHTML.trim() === generatedHtml.value.trim())) {
              // Fallback ke iframe langsung
              useDirectIframe.value = true;
              console.log('Fallback to direct iframe after failed attempts');
            }
          }
        }, delay);
        
        intervalIds.push(intervalId);
      });
      
      // Clean up timeouts dan observer pada unmount
      onUnmounted(() => {
        observer.disconnect();
        intervalIds.forEach(id => clearTimeout(id));
      });
    }
  } catch (err) {
    console.error('Failed to initialize TikTok embed', err);
    // Fallback ke metode iframe langsung
    useDirectIframe.value = true;
  } finally {
    // Delay sebelum menghilangkan loading state
    setTimeout(() => {
      loading.value = false;
    }, 2000); // Tunggu 2 detik minimum
  }
});
</script>

<style scoped>
.tiktok-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  min-height: 200px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.tiktok-embed-wrapper.portrait-mode {
  max-width: 350px; /* Kembali ke 350px untuk mode portrait */
  min-height: 850px; /* Sesuaikan dengan Show.vue */
}

.tiktok-embed-container,
.tiktok-direct-embed {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 450px; /* Diatur ke 450px */
}

.tiktok-embed-wrapper.portrait-mode .tiktok-direct-embed {
  min-height: 850px; /* Sesuaikan dengan Show.vue */
  height: 100%;
}

.tiktok-iframe {
  width: 100%;
  height: 100%;
  min-height: 550px; /* Diatur ke 550px */
  border: none;
  background-color: white;
  border-radius: 0.5rem;
  overflow: hidden;
}

.tiktok-iframe.portrait-iframe {
  min-height: 850px; /* Sesuaikan dengan Show.vue */
  height: 100%; 
  aspect-ratio: 9/16; /* Tetap menjaga aspect ratio 9/16 */
}

.tiktok-embed-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 300px; /* Diatur ke 300px */
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
  height: 300px; /* Diatur ke 300px */
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

:deep(.dark) .tiktok-iframe {
  background-color: transparent;
}
</style> 