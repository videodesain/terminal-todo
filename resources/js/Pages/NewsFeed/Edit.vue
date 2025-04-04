<template>
  <AuthenticatedLayout :title="'Edit - ' + feed.title">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Edit Feed
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <InputLabel for="type" value="Tipe" />
                <SelectInput
                  id="type"
                  class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
                  v-model="form.type"
                  required
                  @change="handleTypeChange"
                >
                  <option value="news">Berita</option>
                  <option value="video">Video</option>
                  <option value="image">Gambar</option>
                  <option value="social_media">Media Sosial</option>
                </SelectInput>
                <InputError class="mt-2" :message="form.errors.type" />
              </div>

              <template v-if="form.type === 'social_media'">
                <div class="space-y-4">
                  <div>
                    <InputLabel for="url" value="URL Media Sosial" />
                    <TextInput
                      id="url"
                      type="url"
                      class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
                      v-model="form.url"
                      required
                      placeholder="https://www.instagram.com/..."
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      Mendukung link dari Facebook, Twitter/X, Instagram, dan TikTok
                    </p>
                    <InputError class="mt-2" :message="form.errors.url" />
                  </div>

                  <div>
                    <InputLabel for="title" value="Judul" />
                    <TextInput
                      id="title"
                      type="text"
                      class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
                      v-model="form.title"
                      placeholder="Judul post media sosial"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                  </div>

                  <div>
                    <InputLabel for="description" value="Deskripsi" />
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                      placeholder="Deskripsi post media sosial"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                  </div>
                </div>
              </template>

              <div v-else-if="form.type === 'image'">
                <div class="mb-4">
                  <InputLabel for="title" value="Judul" />
                  <TextInput
                    id="title"
                    type="text"
                    class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
                    v-model="form.title"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="mb-4">
                  <InputLabel for="description" value="Deskripsi" />
                  <textarea
                    id="description"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    v-model="form.description"
                    rows="4"
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mb-4">
                  <InputLabel for="image" value="Gambar" />
                  <input
                    type="file"
                    id="image"
                    class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-md file:border-0
                      file:text-sm file:font-semibold
                      file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300
                      hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"
                    @input="form.image = $event.target.files[0]"
                    accept="image/*"
                  />
                  <InputError class="mt-2" :message="form.errors.image" />
                  
                  <div v-if="feed.image_url_full" class="mt-2">
                    <img :src="feed.image_url_full" alt="Current image" class="max-w-xs rounded">
                  </div>
                </div>
              </div>

              <!-- Form untuk tipe lain (news/video) -->
              <div v-else-if="form.type !== 'image'">
                <InputLabel for="url" value="URL" />
                <TextInput
                  id="url"
                  type="url"
                  class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
                  v-model="form.url"
                  required
                  :placeholder="form.type === 'video' ? 'https://www.youtube.com/watch?v=...' : 'https://example.com/article'"
                />
                <InputError class="mt-2" :message="form.errors.url" />
              </div>

              <div v-if="preview && form.type !== 'image'" class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Preview</h3>
                <div class="mt-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <div v-if="form.type === 'social_media'" class="space-y-4">
                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                      <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full capitalize">Media</span>
                      <span>•</span>
                      <span>{{ preview.provider_name || preview.site_name || '' }}</span>
                    </div>
                    
                    <!-- Twitter preview dengan EmbedManager -->
                    <div v-else-if="preview.platform === 'twitter' || form.url?.includes('twitter.com') || form.url?.includes('x.com')" class="twitter-preview-container">
                      <EmbedManager 
                        :url="form.url" 
                        :metaData="preview" 
                        orientation="portrait"
                      />
                    </div>
                    
                    <!-- Preview dari embed URL -->
                    <div v-else-if="preview.embed_url" class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                      <iframe
                        :src="preview.embed_url"
                        class="w-full h-full"
                        frameborder="0"
                        allowfullscreen
                      ></iframe>
                    </div>
                    
                    <!-- Preview dari thumbnail -->
                    <div v-else-if="preview.thumbnail_url" class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                      <img :src="getProxyImageUrl(preview.thumbnail_url)" :alt="preview.title" class="w-full h-full object-cover">
                    </div>
                    
                    <!-- Preview dari HTML -->
                    <div v-else-if="preview.html && preview.platform !== 'twitter'" class="bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden p-4" v-html="preview.html"></div>
                    
                    <!-- Metadata -->
                    <div v-if="preview.title" class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ preview.title }}
                    </div>
                    <div v-if="preview.description" class="text-sm text-gray-600 dark:text-gray-300">
                      {{ preview.description }}
                    </div>
                  </div>

                  <div v-else>
                    <div v-if="preview.image_url" class="aspect-video bg-gray-100 dark:bg-gray-700 mb-4">
                      <img :src="preview.image_url" :alt="preview.title" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <div v-if="preview.video_url" class="aspect-video bg-gray-100 dark:bg-gray-700 mb-4">
                      <video :src="preview.video_url" class="w-full h-full object-cover rounded-lg" controls></video>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ preview.title }}</h4>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">{{ preview.description }}</p>
                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                      {{ preview.site_name }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-4">
                <Link
                  :href="route('news-feeds.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-3"
                >
                  Batal
                </Link>
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { ref, watch } from 'vue'
import axios from 'axios'
import TwitterEmbed from '@/Components/Embeds/TwitterEmbed.vue'
import EmbedManager from '@/Components/Embeds/EmbedManager.vue'

const props = defineProps({
  feed: Object
})

const preview = ref(null)
const form = useForm({
  url: props.feed.url,
  title: props.feed.title,
  description: props.feed.description,
  type: props.feed.type,
  image: null,
  meta_data: props.feed.meta_data
})

const fetchPreview = async (url) => {
  try {
    // Reset error terlebih dahulu
    form.clearErrors('url')
    form.processing = true
    
    const response = await axios.post(route('news-feeds.preview'), { 
      url,
      type: form.type 
    })
    
    if (response.data.error) {
      console.error('Error in preview response:', response.data.error)
      form.errors.url = `Error: ${response.data.error}`
      preview.value = null
      form.meta_data = null
      return
    }
    
    console.log('[Edit] Preview response:', response.data);
    
    preview.value = response.data
    if (form.type === 'social_media') {
      form.meta_data = response.data
      
      // Deteksi platform berdasarkan URL jika belum terdeteksi
      if (!form.meta_data.platform) {
        if (url.includes('instagram')) {
          form.meta_data.platform = 'instagram';
        } else if (url.includes('twitter.com') || url.includes('x.com')) {
          form.meta_data.platform = 'twitter';
          console.log('[Edit] Mendeteksi platform Twitter');
        } else if (url.includes('facebook.com') || url.includes('fb.com')) {
          form.meta_data.platform = 'facebook';
        } else if (url.includes('tiktok.com')) {
          form.meta_data.platform = 'tiktok';
        }
      }
      
      // Perbaikan khusus untuk Twitter
      if (form.meta_data.platform === 'twitter' || url.includes('twitter.com') || url.includes('x.com') || 
          (form.meta_data.html && form.meta_data.html.includes('twitter-tweet'))) {
        
        console.log('[Edit] Twitter metadata:', form.meta_data);
        // Pastikan platform diset ke twitter
        form.meta_data.platform = 'twitter';
        
        // Salin twitter:image ke thumbnail_url jika tidak ada thumbnail
        if (!form.meta_data.thumbnail_url && form.meta_data.twitter_image) {
          form.meta_data.thumbnail_url = form.meta_data.twitter_image;
          console.log('[Edit] Menggunakan twitter_image sebagai thumbnail:', form.meta_data.thumbnail_url);
        }
      }
    }
  } catch (error) {
    console.error('Error fetching preview:', error)
    form.errors.url = `Tidak dapat mengambil preview: ${error.response?.data?.error || error.message}`
    preview.value = null
    form.meta_data = null
  } finally {
    form.processing = false
  }
}

// Fungsi untuk menggunakan proxy pada gambar yang bermasalah CORS
const getProxyImageUrl = (url) => {
  if (!url) {
    console.log('[Edit] URL gambar kosong, menggunakan fallback');
    return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
  }
  
  // Debug untuk thumbnail Twitter
  if (url && typeof url === 'string') {
    console.log('[Edit] URL gambar asli:', url);
  } else {
    console.log('[Edit] URL bukan string:', typeof url);
    return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
  }
  
  // Cek apakah URL berasal dari domain yang bermasalah dengan CORS
  if (url.includes('scontent-') || 
      url.includes('cdninstagram') || 
      url.includes('fbcdn.net') ||
      url.includes('twimg.com') ||
      url.includes('twitter.com') ||
      url.includes('pbs.twimg')) {
    console.log('[Edit] Menggunakan proxy untuk thumbnail:', url);
    return `/api/proxy-image?url=${encodeURIComponent(url)}`;
  }
  
  return url;
};

const handleTypeChange = () => {
  if (form.type === 'image') {
    preview.value = null
    form.meta_data = null
  } else if (form.url) {
    fetchPreview(form.url)
  }
}

watch(() => form.url, async (newUrl) => {
  if (newUrl && form.type !== 'image') {
    await fetchPreview(newUrl)
  } else {
    preview.value = null
    form.meta_data = null
  }
})

const submit = () => {
  // Reset semua error
  form.clearErrors()
  
  if (form.type === 'social_media') {
    if (!form.url) {
      form.errors.url = 'URL media sosial harus diisi'
      return
    }
    if (!form.meta_data) {
      form.errors.url = 'Preview tidak berhasil diambil. Silakan coba lagi.'
      return
    }
    
    // Pastikan minimal ada platform dan title
    if (!form.meta_data.platform) {
      form.meta_data.platform = form.url.includes('instagram') ? 'instagram' : ''
    }
    
    // Jika menggunakan title dan description dari form, pastikan dimasukkan ke dalam request
    const putData = {
      url: form.url,
      title: form.title,
      description: form.description,
      type: form.type,
      meta_data: form.meta_data
    }
    
    form.processing = true
    
    form.put(route('news-feeds.update', props.feed.id), {
      data: putData,
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        preview.value = null
      },
      onError: (errors) => {
        console.error('Submit errors:', errors)
      },
      onFinish: () => {
        form.processing = false
      }
    })
  } else if (form.type === 'image' && form.image) {
    form.post(route('news-feeds.update', props.feed.id), {
      forceFormData: true,
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        preview.value = null
      },
      onError: (errors) => {
        console.error('Upload errors:', errors)
      },
      onFinish: () => {
        form.processing = false
      }
    })
  } else {
    form.put(route('news-feeds.update', props.feed.id), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        preview.value = null
      },
      onError: (errors) => {
        console.error('Submit errors:', errors)
      },
      onFinish: () => {
        form.processing = false
      }
    })
  }
}

// Ekstrak username Twitter dari URL
const extractUsername = (url) => {
  if (!url) return null;
  
  try {
    // Format twitter.com/username
    let matches = url.match(/twitter\.com\/([^\/]+)/i);
    if (matches && matches[1]) {
      return matches[1];
    }
    
    // Format x.com/username
    matches = url.match(/x\.com\/([^\/]+)/i);
    if (matches && matches[1]) {
      return matches[1];
    }
  } catch (error) {
    console.error('Error extracting Twitter username:', error);
  }
  
  return null;
};
</script>

<style>
.twitter-preview-overlay {
  width: 100%;
  min-height: 200px;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.dark .twitter-preview-overlay {
  border-color: #374151;
  background-color: #1f2937;
}

.twitter-embed-wrapper {
  margin: 0 !important;
}

/* Style untuk Twitter preview sederhana */
.twitter-static-preview {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
  background-color: #ffffff;
  padding: 1rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.dark .twitter-static-preview {
  background-color: #15202b;
  border-color: #38444d;
  color: #ffffff;
}

.twitter-preview-header {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
}

.twitter-icon {
  width: 24px;
  height: 24px;
  color: #1da1f2;
  margin-right: 0.5rem;
}

.twitter-preview-info {
  display: flex;
  flex-direction: column;
}

.twitter-preview-name {
  font-weight: 700;
  font-size: 0.9rem;
}

.dark .twitter-preview-name {
  color: #ffffff;
}

.twitter-preview-username {
  font-size: 0.85rem;
  color: #536471;
}

.dark .twitter-preview-username {
  color: #8899a6;
}

.twitter-preview-content {
  margin-top: 0.5rem;
}

.twitter-preview-text {
  margin-bottom: 1rem;
  font-size: 1rem;
  line-height: 1.4;
  word-break: break-word;
}

.twitter-link-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background-color: #1da1f2;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  transition: background-color 0.2s;
  text-decoration: none;
}

.twitter-link-button:hover {
  background-color: #1a91da;
}

.dark .twitter-link-button {
  background-color: #1d9bf0;
}

.dark .twitter-link-button:hover {
  background-color: #1a8cd8;
}

.twitter-preview-container {
  width: 100%;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  background-color: #f9fafb;
  min-height: 350px; /* Minimum height for Twitter embed */
  display: flex;
  flex-direction: column;
}

.dark .twitter-preview-container {
  border-color: #374151;
  background-color: #1f2937;
}

.twitter-preview-container :deep(.embed-manager-wrapper) {
  width: 100%;
  height: 100%;
}

.twitter-preview-container :deep(.twitter-embed-wrapper) {
  width: 100%;
  min-height: 350px;
  margin: 0 !important;
  border-radius: 0;
  overflow: hidden;
}
</style> 