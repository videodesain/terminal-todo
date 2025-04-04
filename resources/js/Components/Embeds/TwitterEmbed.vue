<!-- TwitterEmbed.vue - Komponen khusus untuk embed Twitter dengan berbagai strategi loading -->
<template>
  <div class="twitter-embed-wrapper">
    <!-- Loading State -->
    <div v-if="loading" class="twitter-embed-loading">
      <div class="loading-spinner"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="twitter-embed-error">
      <p>{{ error }}</p>
      <button 
        @click="reloadEmbed"
        class="reload-button"
      >
        Coba Lagi
      </button>
    </div>
    
    <!-- Twitter Embed Content -->
    <div v-else-if="embedHtml" ref="twitterContainer" class="twitter-embed-container" v-html="embedHtml"></div>
    
    <!-- Fallback Static Preview -->
    <div v-else-if="tweetId && !embedHtml" class="twitter-embed-static">
      <div class="twitter-static-content">
        <div class="twitter-static-header">
          <img 
            :src="authorAvatar || '/default-avatar.png'" 
            :alt="authorName"
            class="twitter-avatar"
            @error="setDefaultAvatar"
          >
          <div class="twitter-author">
            <div class="twitter-name">{{ authorName || 'Twitter User' }}</div>
            <div class="twitter-username">@{{ authorUsername || 'twitter' }}</div>
          </div>
        </div>
        <div class="twitter-static-body">
          <p>{{ tweetText || 'Tweet tidak tersedia' }}</p>
          <img 
            v-if="thumbnailUrl" 
            :src="thumbnailUrl" 
            class="twitter-media"
            @error="handleImageError"
          >
        </div>
        <div class="twitter-static-footer">
          <span v-if="createdAt" class="twitter-date">{{ formatDate(createdAt) }}</span>
          <a 
            :href="`https://twitter.com/i/web/status/${tweetId}`" 
            target="_blank" 
            rel="noopener noreferrer"
            class="twitter-view-link"
          >
            <svg class="twitter-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
            <span>Lihat di Twitter</span>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Fallback jika tidak ada Twitter ID atau kontent lainnya -->
    <div v-else class="twitter-embed-error">
      <p>Tweet tidak tersedia</p>
      <a 
        :href="url" 
        target="_blank" 
        rel="noopener noreferrer"
        class="twitter-view-link"
      >
        Lihat di Twitter
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';

const props = defineProps({
  url: {
    type: String,
    required: true
  },
  tweetId: {
    type: String,
    default: null
  },
  metaData: {
    type: Object,
    default: () => ({})
  },
  orientation: {
    type: String,
    default: 'landscape',
    validator: (value) => ['landscape', 'portrait'].includes(value)
  }
});

const twitterContainer = ref(null);
const loading = ref(true);
const error = ref(null);
const embedHtml = ref(null);

// Data dari meta_data
const authorName = computed(() => props.metaData?.author_name || null);
const authorUsername = computed(() => props.metaData?.author_username || null);
const authorAvatar = computed(() => props.metaData?.author_avatar || null);
const tweetText = computed(() => props.metaData?.text || props.metaData?.description || null);
const thumbnailUrl = computed(() => props.metaData?.thumbnail_url || null);
const createdAt = computed(() => props.metaData?.created_at || null);

// Mendapatkan ID tweet dari URL jika tidak disediakan
const extractTweetId = (url) => {
  if (!url) return null;
  
  // Handle format @https://publish.twitter.com/?query=...
  if (url.includes('publish.twitter.com') && url.includes('query=')) {
    try {
      // Ekstrak URL asli yang ada dalam parameter query
      let queryParam = new URLSearchParams(url.split('?')[1]).get('query');
      if (queryParam) {
        // Decode URL parameter
        queryParam = decodeURIComponent(queryParam);
        // Recursive call untuk URL asli
        return extractTweetId(queryParam);
      }
    } catch (error) {
      console.error('Error parsing publish URL:', error);
    }
  }
  
  // Handle format @https://x.com/username/status/id
  if (url.startsWith('@https://')) {
    url = url.substring(1);
  }
  
  // Handle direct input status ID
  if (/^\d+$/.test(url)) {
    return url;
  }
  
  try {
    // Handle normal URL format
    const urlObj = new URL(url);
    // Support both twitter.com and x.com domains
    if (!urlObj.hostname.includes('twitter.com') && !urlObj.hostname.includes('x.com')) {
      return null;
    }
    
    const pathParts = urlObj.pathname.split('/');
    const statusIndex = pathParts.indexOf('status');
    if (statusIndex !== -1 && pathParts[statusIndex + 1]) {
      return pathParts[statusIndex + 1].split('?')[0];
    }
    
    return null;
  } catch (error) {
    // Handle other formats
    
    // Format: username/status/id
    let matches = url.match(/\/status\/(\d+)/);
    if (matches && matches[1]) {
      return matches[1];
    }
    
    // Format: twitter.com/username/status/id
    matches = url.match(/twitter\.com\/[^\/]+\/status\/(\d+)/i);
    if (matches && matches[1]) {
      return matches[1];
    }
    
    // Format: x.com/username/status/id
    matches = url.match(/x\.com\/[^\/]+\/status\/(\d+)/i);
    if (matches && matches[1]) {
      return matches[1];
    }
    
    console.error('Error extracting tweet ID:', error);
    return null;
  }
};

// Format tanggal untuk tampilan
const formatDate = (dateString) => {
  if (!dateString) return '';
  
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    console.error('Error formatting date:', error);
    return '';
  }
};

// Event handler untuk error pada avatar
const setDefaultAvatar = (event) => {
  event.target.src = '/default-avatar.png';
};

// Event handler untuk error pada gambar thumbnail
const handleImageError = (event) => {
  event.target.style.display = 'none';
};

// Strategi 1: Menggunakan oEmbed API
const useOEmbedStrategy = async (tweetId) => {
  try {
    // Tentukan tema (dark/light)
    const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    
    // Construct oEmbed URL
    const oembedUrl = `https://publish.twitter.com/oembed?url=https://twitter.com/i/web/status/${tweetId}&omit_script=true&align=center&dnt=true&theme=${currentTheme}&lang=id&maxheight=${props.orientation === 'portrait' ? '700' : '400'}`;
    
    // Fetch data dari oEmbed API
    const response = await fetch(oembedUrl);
    if (!response.ok) {
      throw new Error(`oEmbed API error: ${response.status}`);
    }
    
    const data = await response.json();
    
    // Set HTML dari response
    embedHtml.value = data.html;
    
    // Muat Twitter widget script
    await loadTwitterWidgetsScript();
    
    return true;
  } catch (error) {
    console.warn('oEmbed strategy failed:', error);
    return false;
  }
};

// Strategi 2: Menggunakan iframe langsung
const useIframeStrategy = (tweetId) => {
  try {
    // Tentukan tema (dark/light)
    const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    
    // Set HTML untuk iframe
    embedHtml.value = `
      <div class="twitter-embed-iframe-container ${props.orientation === 'portrait' ? 'portrait' : 'landscape'}">
        <iframe
          src="https://platform.twitter.com/embed/Tweet.html?id=${tweetId}&theme=${currentTheme}&lang=id"
          frameborder="0"
          scrolling="no"
          allowtransparency="true"
          allowfullscreen="true"
          class="twitter-embed-iframe ${props.orientation === 'portrait' ? 'twitter-embed-iframe-portrait' : ''}"
        ></iframe>
      </div>
    `;
    
    return true;
  } catch (error) {
    console.warn('iframe strategy failed:', error);
    return false;
  }
};

// Strategi 3: Menggunakan tampilan statis fallback
// Dalam kasus ini, kita tidak mengubah embedHtml.value,
// sehingga template akan menampilkan fallback statis

// Memuat Twitter widgets script
const loadTwitterWidgetsScript = () => {
  return new Promise((resolve) => {
    // Cek apakah script sudah dimuat
    if (window.twttr && window.twttr.widgets) {
      nextTick(() => {
        window.twttr.widgets.load(twitterContainer.value);
        resolve();
      });
      return;
    }
    
    // Cek apakah script sedang dimuat (element ada tapi twttr belum tersedia)
    const existingScript = document.querySelector('script[src*="platform.twitter.com/widgets.js"]');
    if (existingScript) {
      // Tunggu script selesai
      const checkTwitter = setInterval(() => {
        if (window.twttr && window.twttr.widgets) {
          clearInterval(checkTwitter);
          nextTick(() => {
            window.twttr.widgets.load(twitterContainer.value);
            resolve();
          });
        }
      }, 250);
      
      setTimeout(() => {
        clearInterval(checkTwitter);
        resolve(); // Resolve anyway setelah timeout
      }, 5000);
      
      return;
    }
    
    // Buat script baru
    const script = document.createElement('script');
    script.src = 'https://platform.twitter.com/widgets.js';
    script.charset = 'utf-8';
    script.async = true;
    
    script.onload = () => {
      // Tunggu sampai twttr tersedia
      const checkTwitter = setInterval(() => {
        if (window.twttr && window.twttr.widgets) {
          clearInterval(checkTwitter);
          nextTick(() => {
            window.twttr.widgets.load(twitterContainer.value);
            resolve();
          });
        }
      }, 250);
      
      setTimeout(() => {
        clearInterval(checkTwitter);
        resolve(); // Resolve anyway setelah timeout
      }, 5000);
    };
    
    script.onerror = () => {
      console.warn('Error loading Twitter widgets script');
      resolve();
    };
    
    document.head.appendChild(script);
  });
};

// Reload embed jika terjadi error
const reloadEmbed = async () => {
  loading.value = true;
  error.value = null;
  embedHtml.value = null;
  
  try {
    await loadEmbed();
  } catch (err) {
    console.error('Failed to reload Twitter embed', err);
    error.value = 'Gagal memuat embed Twitter';
  } finally {
    loading.value = false;
  }
};

// Fungsi utama untuk memuat embed
const loadEmbed = async () => {
  // Dapatkan tweet ID
  let tweetId = props.tweetId;
  if (!tweetId) {
    tweetId = extractTweetId(props.url);
  }
  
  if (!tweetId) {
    throw new Error('Tidak dapat menemukan ID tweet');
  }
  
  // Coba menggunakan strategi secara berurutan
  // 1. oEmbed API (cara yang paling direkomendasikan Twitter)
  const oEmbedSuccess = await useOEmbedStrategy(tweetId);
  if (oEmbedSuccess) return;
  
  // 2. iframe langsung (fallback pertama)
  const iframeSuccess = useIframeStrategy(tweetId);
  if (iframeSuccess) return;
  
  // 3. Fallback statis (fallback terakhir)
  // Tidak perlu return value karena komponen akan menampilkan fallback
};

// Setup pada mount
onMounted(async () => {
  try {
    await loadEmbed();
  } catch (err) {
    console.error('Failed to initialize Twitter embed', err);
    error.value = 'Gagal memuat embed Twitter';
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.twitter-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 550px;
  margin: 0 auto;
  min-height: 200px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.twitter-embed-container {
  width: 100%;
  height: 100%;
  min-height: 200px;
}

.twitter-embed-loading {
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
  border-top-color: #1da1f2;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.twitter-embed-error {
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
  background-color: #1da1f2;
  color: white;
  border-radius: 9999px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.reload-button:hover {
  background-color: #1a91da;
}

/* iframe embed */
.twitter-embed-iframe-container {
  width: 100%;
  min-height: 400px;
  height: 100%;
}

.twitter-embed-iframe-container.portrait {
  min-height: 650px;
}

.twitter-embed-iframe {
  width: 100%;
  height: 100%;
  min-height: 400px;
  border: none;
}

.twitter-embed-iframe-portrait {
  min-height: 650px !important;
  height: 100%;
}

/* static embed */
.twitter-embed-static {
  width: 100%;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  overflow: hidden;
  background-color: white;
}

.twitter-static-content {
  padding: 1rem;
}

.twitter-static-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.twitter-avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 9999px;
  object-fit: cover;
}

.twitter-author {
  display: flex;
  flex-direction: column;
}

.twitter-name {
  font-weight: 600;
  color: #111827;
  font-size: 1rem;
}

.twitter-username {
  color: #6b7280;
  font-size: 0.875rem;
}

.twitter-static-body {
  color: #1f2937;
  line-height: 1.5;
  margin-bottom: 0.75rem;
}

.twitter-media {
  margin-top: 0.75rem;
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.twitter-static-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #e5e7eb;
  padding-top: 0.75rem;
  margin-top: 0.75rem;
}

.twitter-date {
  font-size: 0.875rem;
  color: #6b7280;
}

.twitter-view-link {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 1rem;
  background-color: #1da1f2;
  color: white;
  border-radius: 9999px;
  font-size: 0.875rem;
  text-decoration: none;
  transition: background-color 0.2s;
}

.twitter-view-link:hover {
  background-color: #1a91da;
}

.twitter-icon {
  width: 1.125rem;
  height: 1.125rem;
}

/* Dark mode */
:deep(.dark) .twitter-embed-loading,
:deep(.dark) .twitter-embed-error {
  background-color: #1f2937;
  color: #9ca3af;
}

:deep(.dark) .loading-spinner {
  border-color: rgba(255, 255, 255, 0.1);
  border-top-color: #1da1f2;
}

:deep(.dark) .twitter-embed-static {
  background-color: #1f2937;
  border-color: #374151;
}

:deep(.dark) .twitter-name {
  color: #f9fafb;
}

:deep(.dark) .twitter-username {
  color: #9ca3af;
}

:deep(.dark) .twitter-static-body {
  color: #d1d5db;
}

:deep(.dark) .twitter-static-footer {
  border-color: #374151;
}

:deep(.dark) .twitter-date {
  color: #9ca3af;
}
</style> 