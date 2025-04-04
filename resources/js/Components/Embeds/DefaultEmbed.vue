<!-- DefaultEmbed.vue - Komponen fallback untuk platform yang tidak memiliki komponen khusus -->
<template>
  <div class="default-embed-container">
    <iframe 
      v-if="embedUrl"
      :src="embedUrl"
      class="default-embed-iframe"
      :style="{ aspectRatio: getAspectRatio(platform) }"
      frameborder="0"
      :allow="getIframePermissions(platform)"
      allowfullscreen
    ></iframe>
    <div v-else class="embed-error">
      Tidak dapat menampilkan konten
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  url: {
    type: String,
    required: true
  },
  embedUrl: {
    type: String,
    default: null
  },
  platform: {
    type: String,
    default: 'default'
  }
});

// Fungsi untuk mendapatkan aspect ratio berdasarkan platform
const getAspectRatio = (platform) => {
  switch (platform) {
    case 'facebook':
      return '1.91/1';
    case 'twitter':
      return '1.91/1';
    case 'instagram':
      return '1/1';
    case 'tiktok':
      return '9/16';
    case 'youtube_shorts':
      return '9/16';
    default:
      return '16/9'; // Default untuk YouTube dan platform lainnya
  }
};

// Fungsi untuk mendapatkan permission iframe berdasarkan platform
const getIframePermissions = (platform) => {
  switch (platform) {
    case 'facebook':
      return 'autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share';
    case 'twitter':
      return 'web-share';
    case 'instagram':
      return 'encrypted-media';
    case 'tiktok':
      return 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    case 'youtube':
    case 'youtube_shorts':
      return 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    default:
      return 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
  }
};
</script>

<style scoped>
.default-embed-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  min-height: 300px;
}

.default-embed-iframe {
  width: 100%;
  height: 100%;
  border: none;
  min-height: 300px;
}

.embed-error {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f3f4f6;
  border-radius: 0.5rem;
  padding: 2rem;
  color: #6b7280;
  font-size: 0.875rem;
}

:deep(.dark) .embed-error {
  background-color: #374151;
  color: #9ca3af;
}
</style> 