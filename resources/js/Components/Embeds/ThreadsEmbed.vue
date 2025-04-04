<template>
  <div class="threads-embed-wrapper" :class="{ 'portrait-mode': orientation === 'portrait' }">
    <!-- Threads menolak iframe, jadi kita hanya menampilkan thumbnail dengan link -->
    <div class="threads-static-container">
      <a :href="url" target="_blank" rel="noopener noreferrer" class="threads-link-container">
        <div class="thread-thumbnail">
          <img :src="thumbnailUrl" alt="Thread thumbnail" class="thread-preview-image" />
          
          <!-- Overlay dengan info Threads -->
          <div class="thread-info-overlay">
            <div class="threads-logo">
              <svg class="thread-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.587 1.5 12.077c0-3.282.931-6.008 2.693-7.893C5.958 2.296 8.184 1.083 11 1.083h.007c2.546.018 4.686.903 6.173 2.56 1.528 1.704 2.32 4.067 2.32 6.905c0 3.83-2.756 7.502-7.314 7.502c-1.867 0-3.5-1.084-4.158-2.7c-.024-.057-.05-.11-.07-.167c-.413.354-1.019.895-1.428 1.213c-.238.188-.483.372-.718.548c.305 1.067 1.26 2.118 2.054 2.956c1.372 1.438 3.515 2.204 6.402 2.224c2.812 0 5.204-.978 6.741-2.77C22.374 17.658 23 15.234 23 12.1c0-3.213-.842-5.854-2.444-7.658C18.817 2.44 16.099 1.044 12.12 1.008h-.01c-3.034 0-5.626 1.32-7.278 3.714C3.426 7.177 2.33 10.408 2.33 14.06c.002 3.038.569 5.278 1.737 6.892C5.321 22.73 7.195 23.632 9.75 23.864c.038-.847.32-1.932.994-3.253a5.213 5.213 0 0 1-.892-.299a3.468 3.468 0 0 1-.773-.442c-1.302-1.013-1.962-2.952-1.962-5.75c0-2.322.553-4.228 1.613-5.53"/>
              </svg>
            </div>
            <div class="thread-text">
              <p class="thread-username">@{{ username }}</p>
              <p class="thread-view-prompt">Lihat di Threads</p>
            </div>
          </div>
        </div>
      </a>
      
      <!-- Teks informasi di bawah thumbnail -->
      <div class="threads-info-footer">
        <p class="thread-info-text">Konten ini hanya dapat dilihat langsung di Threads</p>
        <a :href="url" target="_blank" rel="noopener noreferrer" class="view-original-button">
          Lihat Asli
          <svg class="external-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';

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
    default: 'landscape'
  },
  metaData: {
    type: Object,
    default: () => ({})
  }
});

const loading = ref(false); // Ubah default loading ke false
const error = ref(null);
const thumbnailUrl = computed(() => {
  // Coba ambil thumbnail dari metaData jika tersedia
  if (props.metaData && props.metaData.thumbnail_url) {
    const originalUrl = props.metaData.thumbnail_url;
    
    // Cek apakah URL berasal dari domain Instagram/Facebook yang terblokir CORS
    if (originalUrl.includes('scontent-') || 
        originalUrl.includes('cdninstagram') || 
        originalUrl.includes('fbcdn.net')) {
      console.log('[ThreadsEmbed] Menggunakan proxy untuk thumbnail:', originalUrl);
      // Gunakan layanan proxy untuk menghindari CORS
      return `/api/proxy-image?url=${encodeURIComponent(originalUrl)}`;
    }
    
    return originalUrl;
  }
  
  // Jika tidak ada thumbnail, gunakan default image untuk Threads
  return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
});

// Ubah logika template untuk menampilkan thumbnail saja, karena Threads menolak iframe
const showThumbnailOnly = true;

const directEmbedUrl = computed(() => {
  if (props.embedUrl) return props.embedUrl;
  
  // Kita tetap memproses URL untuk debugging, meskipun tidak menggunakan iframe
  console.log('[ThreadsEmbed] Mencoba parse URL untuk direct iframe:', props.url);
  
  // Format 1: threads.net/@username/post/12345
  const threadMatch = props.url.match(/threads\.net\/(?:@)?([^\/]+)\/post\/([^\/\?]+)/);
  if (threadMatch && threadMatch[2]) {
    const username = threadMatch[1];
    const postId = threadMatch[2];
    
    console.log('[ThreadsEmbed] Match pattern 1: username=', username, 'postId=', postId);
    // Meskipun kita selesai memproses URL, iframe akan ditolak oleh Threads
    return `https://www.threads.net/embed/post/${postId}?width=550`;
  }
  
  // Format 2: threads.net/p/12345 atau threads.net/post/12345
  const altThreadMatch = props.url.match(/threads\.net\/(p|post)\/([^\/\?]+)/);
  if (altThreadMatch && altThreadMatch[2]) {
    const postId = altThreadMatch[2];
    
    console.log('[ThreadsEmbed] Match pattern 2: postId=', postId);
    return `https://www.threads.net/embed/post/${postId}?width=550`;
  }
  
  // Format 3: threads.net/t/12345
  const tThreadMatch = props.url.match(/threads\.net\/t\/([^\/\?]+)/);
  if (tThreadMatch && tThreadMatch[1]) {
    const postId = tThreadMatch[1];
    
    console.log('[ThreadsEmbed] Match pattern 3: postId=', postId);
    return `https://www.threads.net/embed/post/${postId}?width=550`;
  }
  
  // Format 4: /threads/12345 (URL relative)
  const relativeThreadMatch = props.url.match(/\/threads\/([^\/\?]+)/);
  if (relativeThreadMatch && relativeThreadMatch[1]) {
    const postId = relativeThreadMatch[1];
    
    console.log('[ThreadsEmbed] Match pattern 4: postId=', postId);
    return `https://www.threads.net/embed/post/${postId}?width=550`;
  }
  
  // Format 5: Jika ada ID yang eksplisit dalam URL, coba ekstrak
  const explicitIdMatch = props.url.match(/[&?]id=([^&]+)/);
  if (explicitIdMatch && explicitIdMatch[1]) {
    const postId = explicitIdMatch[1];
    
    console.log('[ThreadsEmbed] Match pattern 5: postId=', postId);
    return `https://www.threads.net/embed/post/${postId}?width=550`;
  }
  
  // Fallback: gunakan URL langsung jika merupakan URL embed Threads
  if (props.url.includes('threads.net/embed/')) {
    console.log('[ThreadsEmbed] URL sudah dalam format embed, menggunakan langsung');
    return props.url;
  }
  
  console.log('[ThreadsEmbed] Tidak dapat mengenali format URL');
  return null;
});

// Mengekstrak username dari URL jika tersedia
const username = computed(() => {
  // Coba ambil dari metaData
  if (props.metaData && props.metaData.author_name) {
    return props.metaData.author_name;
  }
  
  // Coba ekstraksi dari URL
  const threadMatch = props.url.match(/threads\.net\/(?:@)?([^\/]+)\/post\/([^\/\?]+)/);
  if (threadMatch && threadMatch[1] && threadMatch[1] !== 'p' && threadMatch[1] !== 'post') {
    return threadMatch[1];
  }
  
  return 'Threads';
});

onMounted(() => {
  console.log('[ThreadsEmbed] Threads menolak iframe, menampilkan thumbnail statis');
  console.log('[ThreadsEmbed] Thumbnail URL:', thumbnailUrl.value);
});
</script>

<style>
.threads-embed-wrapper {
  width: 100%;
  max-width: 550px;
  margin: 0 auto;
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background-color: white;
}

.threads-embed-wrapper.portrait-mode {
  min-height: 500px;
}

.threads-static-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  border-radius: 8px;
  overflow: hidden;
}

.threads-link-container {
  display: block;
  width: 100%;
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s ease;
}

.threads-link-container:hover {
  transform: translateY(-2px);
}

.thread-thumbnail {
  position: relative;
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  background-color: #f5f5f5;
  max-width: 100%;
  margin: 0 auto;
}

.thread-preview-image {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
  aspect-ratio: 4/5;
}

.thread-info-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px;
  background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
  color: white;
  display: flex;
  align-items: center;
}

.threads-logo {
  width: 32px;
  height: 32px;
  background-color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
}

.thread-icon {
  width: 18px;
  height: 18px;
  color: black;
}

.thread-text {
  display: flex;
  flex-direction: column;
}

.thread-username {
  font-weight: bold;
  font-size: 14px;
  margin: 0;
}

.thread-view-prompt {
  font-size: 12px;
  margin: 2px 0 0 0;
  opacity: 0.8;
}

.threads-info-footer {
  padding: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background-color: #f9f9f9;
  border-top: 1px solid #eee;
}

.thread-info-text {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #666;
}

.view-original-button {
  display: inline-flex;
  align-items: center;
  padding: 8px 16px;
  background: linear-gradient(90deg, #5f3dc4 0%, #7048e8 100%);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
  text-decoration: none;
}

.view-original-button:hover {
  background: linear-gradient(90deg, #7048e8 0%, #845ef7 100%);
}

.external-link-icon {
  width: 16px;
  height: 16px;
  margin-left: 8px;
}

/* Dark mode support */
.dark .threads-embed-wrapper {
  background-color: #1f2937;
}

.dark .threads-static-container {
  background-color: #1f2937;
}

.dark .thread-thumbnail {
  background-color: #374151;
}

.dark .threads-info-footer {
  background-color: #111827;
  border-top-color: #374151;
}

.dark .thread-info-text {
  color: #9ca3af;
}

.dark .threads-logo {
  background-color: #e5e7eb;
}
</style> 