<!-- YouTubeEmbed.vue - Komponen khusus untuk embed YouTube (mendukung video biasa dan shorts) -->
<template>
  <div 
    class="youtube-embed-wrapper"
    :class="{ 'youtube-shorts-wrapper': isShorts }"
  >
    <iframe
      v-if="embedUrl"
      :src="embedUrl"
      class="youtube-embed-iframe"
      :class="{ 'youtube-shorts-iframe': isShorts }"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      allowfullscreen
    ></iframe>
    <div v-else-if="videoId" class="youtube-embed-iframe">
      <iframe
        :src="`https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1`"
        class="youtube-embed-iframe"
        :class="{ 'youtube-shorts-iframe': isShorts }"
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
  }
});

// Checked apakah video ini adalah YouTube Shorts
const isShorts = computed(() => {
  return props.platform === 'youtube_shorts' || props.url?.includes('/shorts/');
});
</script>

<style scoped>
.youtube-embed-wrapper {
  position: relative;
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  aspect-ratio: 16/9;
  border-radius: 0.5rem;
  overflow: hidden;
}

.youtube-shorts-wrapper {
  max-width: 400px;
  aspect-ratio: 9/16;
}

.youtube-embed-iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.youtube-embed-error {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
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