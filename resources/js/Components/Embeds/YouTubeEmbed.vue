<!-- YouTubeEmbed.vue - Komponen khusus untuk embed YouTube (mendukung video biasa dan shorts) -->
<template>
  <div 
    class="youtube-embed-wrapper"
    :class="{ 
      'youtube-shorts-wrapper': isShorts,
      'portrait-mode': orientation === 'portrait' || isShorts
    }"
  >
    <iframe
      v-if="embedUrl"
      :src="embedUrl"
      class="youtube-embed-iframe"
      :class="{ 
        'youtube-shorts-iframe': isShorts,
        'youtube-portrait-iframe': orientation === 'portrait' && !isShorts
      }"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      allowfullscreen
    ></iframe>
    <div v-else-if="videoId" class="youtube-embed-container">
      <iframe
        :src="`https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1`"
        class="youtube-embed-iframe"
        :class="{ 
          'youtube-shorts-iframe': isShorts,
          'youtube-portrait-iframe': orientation === 'portrait' && !isShorts
        }"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
      ></iframe>
    </div>
    <div v-else class="youtube-embed-error">
      <p>Video tidak tersedia</p>
      <p class="text-sm">{{ url }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  url: {
    type: String,
    required: true
  },
  videoId: {
    type: String,
    default: null
  },
  embedUrl: {
    type: String,
    default: null
  },
  platform: {
    type: String,
    default: 'youtube'
  },
  orientation: {
    type: String,
    default: 'landscape',
    validator: (value) => ['landscape', 'portrait'].includes(value)
  }
});

// Mendeteksi apakah video adalah YouTube Shorts
const isShorts = computed(() => {
  return props.platform === 'youtubeShorts' || props.url.includes('/shorts/');
});
</script>

<style scoped>
.youtube-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 550px;
  margin: 0 auto;
  border-radius: 0.75rem;
  overflow: hidden;
  background-color: #000;
}

.youtube-embed-wrapper.portrait-mode {
  max-width: 400px;
}

.youtube-embed-wrapper.youtube-shorts-wrapper {
  max-width: 340px;
}

.youtube-embed-iframe {
  width: 100%;
  aspect-ratio: 16/9;
  border: none;
}

.youtube-embed-iframe.youtube-portrait-iframe {
  aspect-ratio: 9/16;
  min-height: 650px;
}

.youtube-embed-iframe.youtube-shorts-iframe {
  aspect-ratio: 9/16;
  min-height: 600px;
}

.youtube-embed-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  width: 100%;
  background-color: #f9fafb;
  color: #4b5563;
  text-align: center;
  padding: 1rem;
}

:deep(.dark) .youtube-embed-error {
  background-color: #1f2937;
  color: #9ca3af;
}
</style> 