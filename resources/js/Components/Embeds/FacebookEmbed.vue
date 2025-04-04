<!-- FacebookEmbed.vue - Komponen khusus untuk embed Facebook -->
<template>
  <div class="facebook-embed-wrapper" :class="{ 'portrait-mode': orientation === 'portrait' }">
    <!-- Loading State -->
    <div v-if="loading" class="facebook-embed-loading">
      <div class="loading-spinner"></div>
    </div>
    
    <!-- Error State with Fallback Content -->
    <div v-else-if="error" class="facebook-embed-error">
      <p>{{ error }}</p>
      
      <!-- Fallback content for share URLs -->
      <div v-if="url.includes('/share/p/')" class="fb-fallback-container" v-html="createFallbackContent()"></div>
      
      <button 
        v-else
        @click="reloadEmbed"
        class="reload-button"
      >
        Coba Lagi
      </button>
    </div>
    
    <!-- Direct Iframe Embed -->
    <div v-else-if="useDirectIframe && directIframeUrl" class="facebook-direct-embed">
      <iframe 
        :src="directIframeUrl" 
        class="facebook-iframe"
        :class="{ 'portrait-iframe': orientation === 'portrait' }"
        frameborder="0"
        scrolling="no"
        allowtransparency="true"
        allowfullscreen="true"
        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
      ></iframe>
    </div>
    
    <!-- Facebook embed container for SDK method -->
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
import { ref, onMounted, onUnmounted, computed } from 'vue';

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
const useDirectIframe = ref(true); // Ubah ke true untuk selalu menggunakan iframe langsung

// Ekstrak post ID dari URL
const extractedPostId = computed(() => {
  // Coba ekstrak ID post dari berbagai format URL Facebook
  if (!props.url) return null;
  
  try {
    const url = new URL(props.url);
    
    // Format baru: facebook.com/share/p/XXXXX/
    const shareMatch = props.url.match(/\/share\/p\/([^\/\?]+)/);
    if (shareMatch && shareMatch[1]) return shareMatch[1];
    
    // Format: facebook.com/permalink.php?story_fbid=123&id=456
    const storyFbId = url.searchParams.get('story_fbid');
    if (storyFbId) return storyFbId;
    
    // Format: facebook.com/123/posts/456
    const postsMatch = props.url.match(/\/posts\/(\d+)/);
    if (postsMatch && postsMatch[1]) return postsMatch[1];
    
    // Format: facebook.com/photo/?fbid=123
    const fbid = url.searchParams.get('fbid');
    if (fbid) return fbid;
    
    // Format: facebook.com/photo.php?fbid=123
    if (fbid) return fbid;
    
    // Format: facebook.com/pfbid02XXXXXX
    const pfbidMatch = props.url.match(/\/pfbid0([a-zA-Z0-9]+)/);
    if (pfbidMatch && pfbidMatch[1]) return `pfbid0${pfbidMatch[1]}`;
    
    // Format: facebook.com/groups/123/posts/456
    const groupPostMatch = props.url.match(/\/groups\/[^\/]+\/posts\/(\d+)/);
    if (groupPostMatch && groupPostMatch[1]) return groupPostMatch[1];
    
    // Format: facebook.com/watch/?v=123
    const videoId = url.searchParams.get('v');
    if (videoId) return videoId;
    
    // Format: facebook.com/user/videos/123
    const videosMatch = props.url.match(/\/videos\/(\d+)/);
    if (videosMatch && videosMatch[1]) return videosMatch[1];
    
  } catch (e) {
    console.error('Error parsing Facebook URL:', e);
  }
  
  return null;
});

// Ekstrak halaman/profil dari URL
const extractedPage = computed(() => {
  if (!props.url) return null;
  
  try {
    // Format: facebook.com/username/posts/123
    const pageMatch = props.url.match(/facebook\.com\/([^\/\?]+)/);
    if (pageMatch && pageMatch[1] && !['permalink.php', 'photo.php', 'photo', 'watch', 'groups'].includes(pageMatch[1])) {
      return pageMatch[1];
    }
  } catch (e) {
    console.error('Error extracting Facebook page:', e);
  }
  
  return null;
});

// Template fallback konten statis
const createFallbackContent = () => {
  return `
    <div class="fb-fallback-post">
      <div class="fb-fallback-header">
        <div class="fb-fallback-avatar"></div>
        <div class="fb-fallback-info">
          <div class="fb-fallback-name">Konten Facebook</div>
          <div class="fb-fallback-date">Tidak dapat menampilkan konten asli</div>
        </div>
      </div>
      <div class="fb-fallback-body">
        <p>Konten ini tidak dapat ditampilkan karena pembatasan dari Facebook.</p>
        <p>Silakan klik tautan di bawah untuk melihat konten asli di Facebook.</p>
      </div>
      <div class="fb-fallback-footer">
        <a href="${props.url}" target="_blank" rel="noopener noreferrer" class="fb-fallback-link">
          Lihat di Facebook
        </a>
      </div>
    </div>
  `;
};

// Buat URL untuk iframe langsung
const directIframeUrl = computed(() => {
  // Jika embedUrl sudah tersedia, gunakan itu
  if (props.embedUrl) {
    return props.embedUrl;
  }
  
  try {
    // Format share URL /share/p/ memerlukan penanganan khusus
    // Pada format ini, kita tidak bisa menggunakan plugins/post.php
    if (props.url.includes('/share/p/')) {
      console.log('Format URL share tidak didukung:', props.url);
      // Gunakan URL yang lebih umum untuk diarahkan ke halaman Facebook
      return null;
    }
    
    // Untuk video Facebook
    if (props.url.includes('/watch') || props.url.includes('/videos/')) {
      const videoId = extractedPostId.value;
      if (videoId) {
        return `https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(props.url)}&show_text=true&width=560&t=0&appId=966242223397117`;
      }
    }
    
    // Untuk postingan di group
    if (props.url.includes('/groups/')) {
      return `https://www.facebook.com/plugins/post.php?href=${encodeURIComponent(props.url)}&show_text=true&width=${props.orientation === 'portrait' ? '350' : '500'}&appId=966242223397117`;
    }
    
    // Untuk foto
    if (props.url.includes('/photo.php') || props.url.includes('/photo?')) {
      return `https://www.facebook.com/plugins/post.php?href=${encodeURIComponent(props.url)}&show_text=true&width=${props.orientation === 'portrait' ? '350' : '500'}&appId=966242223397117`;
    }
    
    // Default untuk post biasa
    return `https://www.facebook.com/plugins/post.php?href=${encodeURIComponent(props.url)}&show_text=true&width=${props.orientation === 'portrait' ? '350' : '500'}&appId=966242223397117`;
  } catch (e) {
    console.error('Error creating Facebook embed URL:', e);
    // Fallback untuk URL yang tidak valid
    return null;
  }
});

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
    // Log URL untuk debugging
    console.log('Mencoba memuat ulang URL:', props.url);
    console.log('Iframe URL:', directIframeUrl.value);
    
    // Gunakan metode iframe langsung sebagai fallback
    useDirectIframe.value = true;
    setTimeout(() => {
      loading.value = false;
    }, 1000);
  } catch (err) {
    console.error('Failed to reload Facebook embed', err);
    error.value = 'Gagal memuat embed Facebook';
    loading.value = false;
  }
};

// Setup saat komponen di-mount
onMounted(async () => {
  // Log URL untuk debugging
  console.log('Facebook embed URL:', props.url);
  
  try {
    // Format URL share khusus
    if (props.url.includes('/share/p/')) {
      console.log('Format URL share terdeteksi, gunakan fallback');
      
      // Tampilkan fallback untuk URL share
      useDirectIframe.value = false;
      error.value = 'Konten URL Facebook dengan format share tidak dapat ditampilkan melalui embed.';
      loading.value = false;
      return;
    }
    
    // Gunakan iframe langsung secara default
    if (useDirectIframe.value) {
      if (directIframeUrl.value) {
        console.log('Menggunakan direct iframe:', directIframeUrl.value);
        setTimeout(() => {
          loading.value = false;
        }, 1500);
      } else {
        // Jika URL tidak valid atau tidak bisa di-embed
        error.value = 'Konten Facebook ini tidak dapat ditampilkan melalui embed.';
        loading.value = false;
      }
      return;
    }
    
    // Kode untuk metode SDK (hanya sebagai fallback)
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
    // Fallback ke metode iframe langsung
    useDirectIframe.value = true;
    if (!directIframeUrl.value) {
      error.value = 'Konten Facebook tidak tersedia';
    }
    setTimeout(() => {
      loading.value = false;
    }, 1000);
  } finally {
    // Jika tidak menggunakan iframe langsung dan tidak ada embedUrl
    if (!useDirectIframe.value && !props.embedUrl) {
      setTimeout(() => {
        loading.value = false;
      }, 2000);
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
  max-width: 100%;
  margin: 0 auto;
  min-height: 200px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.facebook-embed-wrapper.portrait-mode {
  max-width: 500px;
  min-height: 450px;
}

.facebook-embed-container,
.facebook-direct-embed {
  width: 100%;
  height: 100%;
  min-height: 450px;
}

.facebook-embed-iframe,
.facebook-iframe {
  width: 100%;
  height: 100%;
  min-height: 550px;
  border: none;
  background-color: white;
  border-radius: 0.5rem;
  overflow: hidden;
}

.facebook-iframe.portrait-iframe,
.facebook-embed-iframe.portrait-iframe {
  min-height: 750px;
}

.portrait-mode .facebook-embed-container,
.portrait-mode .facebook-direct-embed {
  min-height: 650px;
}

.facebook-embed-loading {
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
  border-top-color: #1877f2; /* Warna Facebook */
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
  background-color: #1877f2; /* Warna Facebook */
  color: white;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.reload-button:hover {
  background-color: #166fe5;
}

.facebook-link {
  color: #1877f2;
  text-decoration: underline;
  margin-top: 0.5rem;
}

/* Menghilangkan scrollbar */
.facebook-embed-container,
.facebook-direct-embed {
  -ms-overflow-style: none !important;
  scrollbar-width: none !important;
}

.facebook-embed-container::-webkit-scrollbar,
.facebook-direct-embed::-webkit-scrollbar {
  display: none !important;
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

:deep(.dark) .facebook-iframe,
:deep(.dark) .facebook-embed-iframe {
  background-color: transparent;
}

:deep(.dark) .facebook-link {
  color: #1877f2;
}

/* Fallback styling untuk post yang tidak dapat di-embed */
.fb-fallback-post {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  border: 1px solid #dddfe2;
  border-radius: 8px;
  background-color: white;
  font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
  overflow: hidden;
}

.fb-fallback-header {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #dddfe2;
}

.fb-fallback-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #e4e6eb;
  margin-right: 8px;
}

.fb-fallback-info {
  display: flex;
  flex-direction: column;
}

.fb-fallback-name {
  font-weight: 600;
  font-size: 14px;
  color: #050505;
}

.fb-fallback-date {
  font-size: 12px;
  color: #65676b;
}

.fb-fallback-body {
  padding: 12px 16px;
  font-size: 14px;
  line-height: 1.5;
  color: #050505;
}

.fb-fallback-footer {
  padding: 8px 16px 16px;
  border-top: 1px solid #dddfe2;
  text-align: center;
}

.fb-fallback-link {
  display: inline-block;
  padding: 8px 16px;
  background-color: #1877f2;
  color: white;
  font-weight: 600;
  font-size: 14px;
  border-radius: 4px;
  text-decoration: none;
}

.fb-fallback-link:hover {
  background-color: #166fe5;
}

:deep(.dark) .fb-fallback-post {
  background-color: #242526;
  border-color: #3e4042;
}

:deep(.dark) .fb-fallback-header {
  border-color: #3e4042;
}

:deep(.dark) .fb-fallback-avatar {
  background-color: #3a3b3c;
}

:deep(.dark) .fb-fallback-name {
  color: #e4e6eb;
}

:deep(.dark) .fb-fallback-date {
  color: #b0b3b8;
}

:deep(.dark) .fb-fallback-body {
  color: #e4e6eb;
}

:deep(.dark) .fb-fallback-footer {
  border-color: #3e4042;
}
</style> 