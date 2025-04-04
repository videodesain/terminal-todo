<!-- EmbedManager.vue - Komponen utama untuk mengelola semua jenis embed sosial media -->
<template>
  <div class="embed-manager-wrapper" :class="{ 'portrait-mode': orientation === 'portrait' }">
    <!-- Twitter Embed -->
    <TwitterEmbed
      v-if="platform === 'twitter' || platform === 'x'"
      :url="url"
      :tweetId="embedDetails.id"
      :metaData="metaData"
      :orientation="orientation"
    />
    
    <!-- YouTube Embed -->
    <YouTubeEmbed
      v-else-if="platform === 'youtube' || platform === 'youtubeShorts'"
      :url="url"
      :videoId="embedDetails.id"
      :embedUrl="embedDetails.embedUrl"
      :platform="platform"
      :orientation="orientation"
    />
    
    <!-- TikTok Embed -->
    <TikTokEmbed
      v-else-if="platform === 'tiktok'"
      :url="url"
      :videoId="embedDetails.id"
      :html="embedDetails.html"
      :orientation="orientation"
    />
    
    <!-- Instagram Embed -->
    <InstagramEmbed
      v-else-if="platform === 'instagram'"
      :url="url"
      :html="embedDetails.html"
      :embedUrl="embedDetails.embedUrl"
      :orientation="orientation"
    />
    
    <!-- Facebook Embed -->
    <FacebookEmbed
      v-else-if="platform === 'facebook'"
      :url="url"
      :embedUrl="embedDetails.embedUrl"
      :orientation="orientation"
    />
    
    <!-- Threads Embed -->
    <ThreadsEmbed
      v-else-if="platform === 'threads'"
      :url="url"
      :embedUrl="embedDetails.embedUrl"
      :orientation="orientation"
      :metaData="metaData"
    />
    
    <!-- Default Embed sebagai fallback -->
    <DefaultEmbed
      v-else
      :url="url"
      :embedUrl="embedDetails.embedUrl"
      :platform="platform"
      :orientation="orientation"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import TwitterEmbed from './TwitterEmbed.vue';
import YouTubeEmbed from './YouTubeEmbed.vue';
import TikTokEmbed from './TikTokEmbed.vue';
import InstagramEmbed from './InstagramEmbed.vue';
import FacebookEmbed from './FacebookEmbed.vue';
import DefaultEmbed from './DefaultEmbed.vue';
import ThreadsEmbed from './ThreadsEmbed.vue';

const props = defineProps({
  // URL asli dari embed
  url: {
    type: String,
    required: true
  },
  // Metadata tambahan untuk embed
  metaData: {
    type: Object,
    default: () => ({})
  },
  // Orientasi tampilan (portrait/landscape)
  orientation: {
    type: String,
    default: 'landscape',
    validator: (value) => ['landscape', 'portrait'].includes(value)
  }
});

// Data tambahan tentang embed
const embedDetails = ref({
  id: null,
  embedUrl: null,
  html: null
});

// Deteksi platform berdasarkan URL
const platform = computed(() => {
  const url = props.url;
  
  // Terkadang URL dimulai dengan @ - hapus jika ada
  const cleanUrl = url.startsWith('@') ? url.substring(1) : url;
  
  // Twitter / X
  if (cleanUrl.includes('twitter.com') || cleanUrl.includes('x.com') || 
      cleanUrl.includes('publish.twitter.com')) {
    // Debug khusus untuk Twitter
    if (props.metaData) {
      console.log('[EmbedManager] Twitter metadata detected:', {
        url: props.url,
        thumbnail_url: props.metaData.thumbnail_url,
        metaData: props.metaData
      });
    }
    return 'twitter';
  }
  
  // YouTube
  if (cleanUrl.includes('youtube.com') || cleanUrl.includes('youtu.be')) {
    // Cek apakah ini YouTube Shorts
    if (cleanUrl.includes('/shorts/')) {
      return 'youtubeShorts';
    }
    return 'youtube';
  }
  
  // TikTok
  if (cleanUrl.includes('tiktok.com')) {
    return 'tiktok';
  }
  
  // Instagram
  if (cleanUrl.includes('instagram.com')) {
    return 'instagram';
  }
  
  // Threads
  if (cleanUrl.includes('threads.net')) {
    return 'threads';
  }
  
  // Facebook
  if (cleanUrl.includes('facebook.com') || cleanUrl.includes('fb.com') || 
      cleanUrl.includes('fb.watch')) {
    return 'facebook';
  }
  
  // Deteksi platform lainnya
  // ...
  
  // Default jika tidak ada yang cocok
  return 'unknown';
});

// Extract ID dan URL berdasarkan platform
const extractDetailsFromUrl = () => {
  const url = props.url;
  const platformType = platform.value;
  
  console.log(`[EmbedManager] Memproses URL: ${url}`);
  
  // Reset data
  embedDetails.value = {
    id: null,
    embedUrl: null,
    html: null
  };
  
  // Jika metaData sudah mencakup embedUrl, gunakan itu
  if (props.metaData && props.metaData.embed_url) {
    embedDetails.value.embedUrl = props.metaData.embed_url;
    console.log(`[EmbedManager] Menggunakan embedUrl dari metaData: ${embedDetails.value.embedUrl}`);
  }
  
  // Ekstrak informasi berdasarkan platform
  switch (platformType) {
    case 'twitter':
      // Id akan diekstrak di dalam komponen TwitterEmbed
      break;
      
    case 'youtube':
      // Format YouTube standar: youtube.com/watch?v=VIDEO_ID
      try {
        const videoId = new URL(url).searchParams.get('v');
        if (videoId) {
          embedDetails.value.id = videoId;
          // Tambahkan parameter untuk tampilan lebih baik
          const aspectRatio = props.orientation === 'portrait' ? '9:16' : '16:9';
          embedDetails.value.embedUrl = `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1`;
        }
      } catch (error) {
        // Format alternatif: youtu.be/VIDEO_ID
        const matches = url.match(/youtu\.be\/([^?&]+)/);
        if (matches && matches[1]) {
          embedDetails.value.id = matches[1];
          embedDetails.value.embedUrl = `https://www.youtube.com/embed/${matches[1]}?rel=0&modestbranding=1`;
        }
      }
      break;
      
    case 'youtubeShorts':
      // Format: youtube.com/shorts/VIDEO_ID
      const shortsMatches = url.match(/\/shorts\/([^?&]+)/);
      if (shortsMatches && shortsMatches[1]) {
        embedDetails.value.id = shortsMatches[1];
        embedDetails.value.embedUrl = `https://www.youtube.com/embed/${shortsMatches[1]}?rel=0&modestbranding=1`;
      }
      break;
      
    case 'tiktok':
      // Format: tiktok.com/@username/video/VIDEO_ID
      let username = null;
      let videoId = null;

      // Ekstrak ID video dan username dari URL
      const tiktokMatches = url.match(/\/video\/(\d+)/);
      if (tiktokMatches && tiktokMatches[1]) {
        videoId = tiktokMatches[1];
        embedDetails.value.id = videoId;
      }

      // Coba ekstrak username dari URL
      const usernameMatch = url.match(/tiktok\.com\/@([^\/]+)/);
      if (usernameMatch && usernameMatch[1]) {
        username = usernameMatch[1];
      }

      // Buat HTML embed jika ada videoId
      if (videoId) {
        if (username) {
          embedDetails.value.html = `
            <blockquote class="tiktok-embed" cite="https://www.tiktok.com/@${username}/video/${videoId}" 
              data-video-id="${videoId}" 
              style="max-width: 605px; min-width: 325px;">
              <section></section>
            </blockquote>
          `;
        } else {
          embedDetails.value.html = `
            <blockquote class="tiktok-embed" cite="https://www.tiktok.com/video/${videoId}" 
              data-video-id="${videoId}" 
              style="max-width: 605px; min-width: 325px;">
              <section></section>
            </blockquote>
          `;
        }
      } else if (url) {
        // Fallback menggunakan URL langsung jika tidak ada ID
        embedDetails.value.html = `
          <blockquote class="tiktok-embed" cite="${url}" 
            style="max-width: 605px; min-width: 325px;">
            <section></section>
          </blockquote>
        `;
      }
      break;
      
    case 'instagram':
      // Format: instagram.com/p/CODE/ atau instagram.com/reel/CODE/
      const instaMatches = url.match(/instagram\.com\/(p|reel|tv)\/([^/?]+)/);
      if (instaMatches && instaMatches[2]) {
        const code = instaMatches[2];
        embedDetails.value.id = code;
        embedDetails.value.embedUrl = `https://www.instagram.com/${instaMatches[1]}/${code}/embed/`;
      }
      break;
      
    case 'facebook':
      // Untuk Facebook kita akan menggunakan langsung URL untuk iframe
      // Format: facebook.com/plugins/post.php?href=URL_ENCODED_POST_URL
      try {
        const fbUrl = new URL(url);
        
        // Deteksi format URL /share/ baru - tidak gunakan untuk embed
        if (url.includes('/share/p/')) {
          console.log(`[EmbedManager] Terdeteksi format URL share Facebook, tidak bisa di-embed langsung`);
          // Jangan set embedUrl untuk format ini, biarkan komponen FacebookEmbed menanganinya
          embedDetails.value.embedUrl = null;
        }
        else if (fbUrl.pathname.includes('/plugins/')) {
          // URL embed sudah diberikan
          embedDetails.value.embedUrl = url;
        } else {
          // URL konten langsung, perlu dikonversi ke URL embed
          const encodedUrl = encodeURIComponent(url);
          embedDetails.value.embedUrl = `https://www.facebook.com/plugins/post.php?href=${encodedUrl}&show_text=true&width=500&appId=966242223397117`;
        }
      } catch (error) {
        console.error('Error parsing Facebook URL:', error);
      }
      break;
      
    case 'threads':
      // Untuk platform Threads, gunakan direct iframe
      try {
        console.log(`[EmbedManager] Memproses Threads URL: ${url}`);
        
        // Gunakan embed URL dari metadata jika tersedia
        if (props.metaData && props.metaData.embed_url) {
          console.log(`[EmbedManager] Menggunakan embed_url dari metaData untuk Threads: ${props.metaData.embed_url}`);
          embedDetails.value.embedUrl = props.metaData.embed_url;
          break;
        }
        
        // Format 1: threads.net/@username/post/12345
        const threadMatch = url.match(/threads\.net\/(?:@)?([^\/]+)\/post\/([^\/\?]+)/);
        if (threadMatch && threadMatch[2]) {
          const username = threadMatch[1];
          const postId = threadMatch[2];
          
          console.log(`[EmbedManager] Threads Match pattern 1: username=${username}, postId=${postId}`);
          embedDetails.value.id = postId;
          embedDetails.value.embedUrl = `https://www.threads.net/embed/post/${postId}?width=550`;
          break;
        }
        
        // Format 2: threads.net/p/12345 atau threads.net/post/12345
        const altThreadMatch = url.match(/threads\.net\/(p|post)\/([^\/\?]+)/);
        if (altThreadMatch && altThreadMatch[2]) {
          const postId = altThreadMatch[2];
          
          console.log(`[EmbedManager] Threads Match pattern 2: postId=${postId}`);
          embedDetails.value.id = postId;
          embedDetails.value.embedUrl = `https://www.threads.net/embed/post/${postId}?width=550`;
          break;
        }
        
        // Format 3: threads.net/t/12345
        const tThreadMatch = url.match(/threads\.net\/t\/([^\/\?]+)/);
        if (tThreadMatch && tThreadMatch[1]) {
          const postId = tThreadMatch[1];
          
          console.log(`[EmbedManager] Threads Match pattern 3: postId=${postId}`);
          embedDetails.value.id = postId;
          embedDetails.value.embedUrl = `https://www.threads.net/embed/post/${postId}?width=550`;
          break;
        }
        
        // Fallback: jika tidak ada match, gunakan URL langsung
        console.log(`[EmbedManager] Threads tidak match pattern, menggunakan URL langsung`);
        embedDetails.value.embedUrl = url;
      } catch (error) {
        console.error('[EmbedManager] Error parsing Threads URL:', error);
        embedDetails.value.embedUrl = url;
      }
      break;
      
    // Tambahkan case lain untuk platform yang didukung
      
    default:
      // Untuk platform yang tidak didukung, gunakan URL asli
      embedDetails.value.embedUrl = url;
      break;
  }
  
  // Cek apakah meta_data memiliki info HTML
  if (props.metaData && props.metaData.html) {
    embedDetails.value.html = props.metaData.html;
  }
  
  // Gunakan ID dari meta_data jika tersedia
  if (props.metaData && props.metaData.id) {
    embedDetails.value.id = props.metaData.id;
  }

  // Log info untuk debugging
  console.log(`[EmbedManager] Platform: ${platformType}, ID: ${embedDetails.value.id}, HTML: ${embedDetails.value.html ? 'Yes' : 'No'}`);
};

// Setup pada mount
onMounted(() => {
  extractDetailsFromUrl();
});
</script>

<style scoped>
.embed-manager-wrapper {
  width: 100%;
  margin: 1.5rem 0;
  display: flex;
  justify-content: center;
}

.portrait-mode {
  max-width: 500px;
  margin: 0 auto;
}

@media (max-width: 640px) {
  .portrait-mode {
    max-width: 100%;
  }
}
</style> 