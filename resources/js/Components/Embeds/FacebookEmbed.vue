<!-- FacebookEmbed.vue - Komponen khusus untuk embed Facebook -->
<template>
  <div class="facebook-embed-wrapper" :class="{ 'portrait-mode': orientation === 'portrait' }">
    <!-- Loading State -->
    <div v-if="loading" class="facebook-embed-loading">
      <div class="loading-spinner"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="facebook-embed-error">
      <p>{{ error }}</p>
      <button 
        @click="reloadEmbed"
        class="reload-button"
      >
        Coba Lagi
      </button>
    </div>
    
    <!-- Facebook embed container -->
    <div v-else ref="facebookContainer" class="facebook-embed-container">
      <!-- Menggunakan FB SDK -->
      <div v-if="!embedUrl" class="fb-post" :data-href="url" :data-width="orientation === 'portrait' ? '350' : '500'"></div>
      
      <!-- Fallback ke iframe jika tersedia embeddedUrl -->
      <iframe 
        v-else-if="embedUrl"
        :src="embedUrl"
        class="facebook-embed-iframe"
        :class="{ 'portrait-iframe': orientation === 'portrait' }"
        frameborder="0"
        scrolling="no"
        allowtransparency="true"
        allowfullscreen="true"
        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
        @load="onIframeLoad"
        @error="onIframeError"
      ></iframe>
      
      <!-- Fallback jika URL tidak valid -->
      <div v-else class="facebook-embed-error">
        <p>Konten Facebook tidak tersedia</p>
        <a 
          :href="url" 
          target="_blank" 
          rel="noopener noreferrer"
          class="facebook-link"
        >
          Lihat di Facebook
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  url: {
    type: String,
    required: true
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

const facebookContainer = ref(null);
const loading = ref(true);
const error = ref(null);

// Memuat Facebook SDK
const loadFacebookSDK = () => {
  return new Promise((resolve, reject) => {
    // Cek apakah SDK sudah dimuat
    if (window.FB) {
      resolve(window.FB);
      return;
    }
    
    // Siapkan callback global untuk FB.init()
    window.fbAsyncInit = function() {
      window.FB.init({
        appId: '', // App ID dapat dikosongkan untuk embed publik
        xfbml: true,
        version: 'v18.0'
      });
      
      resolve(window.FB);
    };
    
    // Hapus script lama jika ada
    const existingScript = document.querySelector('script#facebook-jssdk');
    if (existingScript) {
      existingScript.remove();
    }
    
    // Buat script SDK baru
    const script = document.createElement('script');
    script.id = 'facebook-jssdk';
    script.src = 'https://connect.facebook.net/id_ID/sdk.js';
    script.async = true;
    script.defer = true;
    script.crossOrigin = 'anonymous';
    
    script.onerror = (err) => {
      reject(new Error('Failed to load Facebook SDK'));
    };
    
    // Tambahkan SDK ke dokumen
    const firstScript = document.getElementsByTagName('script')[0];
    firstScript.parentNode.insertBefore(script, firstScript);
  });
};

// Parse/render semua XFBML dalam halaman
const parseFacebookXFBML = () => {
  if (window.FB && window.FB.XFBML) {
    window.FB.XFBML.parse(facebookContainer.value);
    return true;
  }
  return false;
};

// Event handler saat iframe dimuat
const onIframeLoad = () => {
  loading.value = false;
};

// Event handler saat iframe gagal dimuat
const onIframeError = (e) => {
  console.error('Facebook iframe failed to load', e);
  error.value = 'Gagal memuat embed Facebook';
  loading.value = false;
};

// Reload embed jika terjadi error
const reloadEmbed = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    await loadFacebookSDK();
    
    if (!props.embedUrl && facebookContainer.value) {
      // Gunakan FB SDK untuk me-render ulang
      const success = parseFacebookXFBML();
      if (!success) {
        throw new Error('Facebook SDK failed to parse XFBML');
      }
    }
  } catch (err) {
    console.error('Failed to reload Facebook embed', err);
    error.value = 'Gagal memuat embed Facebook';
  } finally {
    // Jika menggunakan iframe, loading akan selesai saat iframe di-load
    if (!props.embedUrl) {
      loading.value = false;
    }
  }
};

// Setup saat komponen di-mount
onMounted(async () => {
  try {
    if (!props.embedUrl) {
      await loadFacebookSDK();
      
      if (facebookContainer.value) {
        // Parse XFBML setelah SDK dimuat
        const success = parseFacebookXFBML();
        if (!success) {
          throw new Error('Facebook SDK failed to parse XFBML');
        }
      }
    }
  } catch (err) {
    console.error('Failed to initialize Facebook embed', err);
    error.value = 'Gagal memuat embed Facebook';
  } finally {
    // Jika menggunakan iframe, loading akan selesai saat iframe di-load
    if (!props.embedUrl) {
      loading.value = false;
    }
  }
});

// Cleanup saat komponen di-unmount
onUnmounted(() => {
  // Clean up jika diperlukan
});
</script>

<style scoped>
.facebook-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 550px;
  margin: 0 auto;
  min-height: 200px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.facebook-embed-wrapper.portrait-mode {
  max-width: 400px;
  min-height: 650px;
}

.facebook-embed-container {
  width: 100%;
  height: 100%;
  min-height: 200px;
}

.facebook-embed-iframe {
  width: 100%;
  height: 100%;
  min-height: 450px;
  border: none;
}

.facebook-embed-iframe.portrait-iframe {
  min-height: 750px;
}

.portrait-mode .facebook-embed-container {
  min-height: 650px;
}

.facebook-embed-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
  width: 100%;
  background-color: #f9fafb;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-top-color: #1877f2;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.facebook-embed-error {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 200px;
  width: 100%;
  background-color: #f9fafb;
  color: #4b5563;
  text-align: center;
  padding: 1rem;
}

.reload-button {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #1877f2;
  color: white;
  border-radius: 9999px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.reload-button:hover {
  background-color: #166bda;
}

.facebook-link {
  display: inline-flex;
  align-items: center;
  margin-top: 0.75rem;
  padding: 0.5rem 1rem;
  background-color: #1877f2;
  color: white;
  border-radius: 9999px;
  font-size: 0.875rem;
  text-decoration: none;
  transition: background-color 0.2s;
}

.facebook-link:hover {
  background-color: #166bda;
}

/* Dark mode */
:deep(.dark) .facebook-embed-loading,
:deep(.dark) .facebook-embed-error {
  background-color: #1f2937;
  color: #9ca3af;
}

:deep(.dark) .loading-spinner {
  border-color: rgba(255, 255, 255, 0.1);
  border-top-color: #1877f2;
}
</style> 