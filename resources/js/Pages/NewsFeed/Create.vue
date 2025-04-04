<template>
  <AuthenticatedLayout title="Tambah Feed Baru">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Tambah Feed Baru
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
                >
                  <option value="news">Berita</option>
                  <option value="video">Video</option>
                  <option value="image">Gambar</option>
                  <option value="social_media">Media Sosial</option>
                </SelectInput>
                <InputError class="mt-2" :message="form.errors.type" />
              </div>

              <!-- Form untuk tipe image -->
              <template v-if="form.type === 'image'">
                <div class="space-y-4">
                  <div>
                    <InputLabel for="image" value="Upload Gambar" />
                    <input
                      type="file"
                      id="image"
                      accept="image/*"
                      @change="handleImageUpload"
                      @error="handleImageError"
                      class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300
                        hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"
                    />
                    <InputError class="mt-2" :message="form.errors.image" />
                  </div>

                  <div v-if="imagePreview" class="mt-4">
                    <div class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                      <img :src="imagePreview" class="w-full h-full object-contain" />
                    </div>
                  </div>

                  <div>
                    <InputLabel for="title" value="Judul" />
                    <TextInput
                      id="title"
                      type="text"
                      class="mt-1 block w-full dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700"
                      v-model="form.title"
                      placeholder="Judul gambar"
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
                      placeholder="Deskripsi gambar"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                  </div>
                </div>
              </template>

              <!-- Form untuk tipe social -->
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
                      placeholder="https://www.facebook.com/..."
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

              <div v-if="preview" class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Preview</h3>
                <div class="mt-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                  <!-- Preview untuk media sosial -->
                  <div v-if="form.type === 'social_media' && preview" class="space-y-4">
                    <div class="flex items-center space-x-2">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center"
                           :class="{
                             'bg-blue-600': preview.platform === 'facebook',
                             'bg-blue-400': preview.platform === 'twitter',
                             'bg-gradient-to-r from-purple-500 to-pink-500': preview.platform === 'instagram',
                             'bg-black': preview.platform === 'tiktok'
                           }">
                        <svg v-if="preview.platform === 'facebook'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <svg v-else-if="preview.platform === 'twitter'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                        <svg v-else-if="preview.platform === 'instagram'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                        </svg>
                        <svg v-else-if="preview.platform === 'tiktok'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 015.2-2.32V9.39a8.16 8.16 0 005.52 2.31V8.1a6.09 6.09 0 01-3.77-1.41z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-medium text-gray-600 dark:text-gray-300 capitalize">{{ preview.platform }}</span>
                    </div>

                    <!-- Embed jika ada (prioritas tertinggi) -->
                    <div v-if="preview.embed_url" class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                      <iframe
                        :src="preview.embed_url"
                        class="w-full h-full"
                        frameborder="0"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      ></iframe>
                    </div>
                    
                    <!-- HTML embed jika tidak ada embed_url -->
                    <div v-else-if="preview.html && preview.platform !== 'twitter'" class="bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden p-4" v-html="preview.html"></div>
                    
                    <!-- Thumbnail jika tidak ada embed_url dan html -->
                    <div v-else-if="preview.thumbnail_url" class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                      <img :src="getProxyImageUrl(preview.thumbnail_url)" 
                           :alt="preview.title" 
                           class="w-full h-full object-cover"
                           @error="handleImageError">
                    </div>
                    
                    <!-- Twitter preview dengan EmbedManager -->
                    <div v-else-if="preview.platform === 'twitter'" class="twitter-preview-container">
                      <!-- Fallback image jika EmbedManager tidak bisa memuat -->
                      <div class="twitter-fallback-preview">
                        <div class="twitter-preview-header">
                          <div class="twitter-icon">
                            <svg class="w-full h-full text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                          </div>
                          <div class="twitter-preview-info">
                            <div class="twitter-preview-username">@{{ extractUsername(form.url) || 'twitter' }}</div>
                          </div>
                        </div>
                        <div class="twitter-preview-content">
                          <p class="twitter-preview-text">{{ preview.description || 'Tweet ini akan ditampilkan di halaman feed.' }}</p>
                          <div v-if="preview.thumbnail_url" class="twitter-preview-image">
                            <img :src="getProxyImageUrl(preview.thumbnail_url)" @error="handleImageError" class="rounded-lg w-full h-auto" />
                          </div>
                          <a :href="form.url" target="_blank" rel="noopener noreferrer" class="twitter-link-button mt-4">
                            Lihat di Twitter
                          </a>
                        </div>
                      </div>
                    </div>

                    <!-- Metadata -->
                    <div v-if="preview.title || preview.description" class="space-y-2">
                      <h4 v-if="preview.title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ preview.title }}</h4>
                      <p v-if="preview.description" class="text-sm text-gray-600 dark:text-gray-400">{{ preview.description }}</p>
                    </div>

                  </div>

                  <!-- Preview untuk tipe lain -->
                  <div v-else>
                    <div v-if="preview.image_url" class="aspect-video bg-gray-100 dark:bg-gray-700 mb-4">
                      <img :src="preview.image_url" :alt="preview.title" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <div v-if="preview.video_url" class="aspect-video bg-gray-100 dark:bg-gray-700 mb-4">
                      <iframe
                        :src="preview.video_url"
                        class="w-full h-full rounded-lg"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
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

const preview = ref(null)
const imagePreview = ref(null)
const form = useForm({
  url: '',
  type: 'news',
  image: null,
  title: '',
  description: '',
  meta_data: null
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
    
    console.log('[Create] Preview response:', response.data);
    
    preview.value = response.data
    if (form.type === 'social_media') {
      form.meta_data = response.data
      
      // Deteksi platform berdasarkan URL jika belum terdeteksi
      if (!form.meta_data.platform) {
        if (url.includes('instagram')) {
          form.meta_data.platform = 'instagram';
        } else if (url.includes('twitter.com') || url.includes('x.com')) {
          form.meta_data.platform = 'twitter';
          console.log('[Create] Mendeteksi platform Twitter');
        } else if (url.includes('facebook.com') || url.includes('fb.com')) {
          form.meta_data.platform = 'facebook';
        } else if (url.includes('tiktok.com')) {
          form.meta_data.platform = 'tiktok';
        }
      }
      
      // Debug dan perbaikan khusus untuk Twitter
      if (form.meta_data.platform === 'twitter' || url.includes('twitter.com') || url.includes('x.com') || 
          (form.meta_data.html && form.meta_data.html.includes('twitter-tweet'))) {
        
        console.log('[Create] Twitter metadata:', form.meta_data);
        // Pastikan platform diset ke twitter
        form.meta_data.platform = 'twitter';
        
        // Salin twitter:image ke thumbnail_url jika tidak ada thumbnail
        if (!form.meta_data.thumbnail_url && form.meta_data.twitter_image) {
          form.meta_data.thumbnail_url = form.meta_data.twitter_image;
          console.log('[Create] Menggunakan twitter_image sebagai thumbnail:', form.meta_data.thumbnail_url);
        }
        
        // Test thumbnail URL
        if (form.meta_data.thumbnail_url) {
          const testImg = new Image();
          testImg.onload = () => console.log('[Create] Thumbnail URL valid:', form.meta_data.thumbnail_url);
          testImg.onerror = () => console.error('[Create] Thumbnail URL tidak valid:', form.meta_data.thumbnail_url);
          testImg.src = form.meta_data.thumbnail_url;
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

const handleImageUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return

  form.image = file
  
  // Create preview URL
  imagePreview.value = URL.createObjectURL(file)
}

const handleImageError = (event) => {
  console.error('[Create] Error loading image:', event.target.src);
  // Hapus event handler untuk mencegah loop
  event.target.onerror = null;
  // Tampilkan gambar fallback
  event.target.src = 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
}

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
  }

  form.processing = true
  
  if (form.type === 'image') {
    form.post(route('news-feeds.store'), {
      forceFormData: true,
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        form.reset()
        imagePreview.value = null
      },
      onError: (errors) => {
        console.error('Upload errors:', errors)
      },
      onFinish: () => {
        form.processing = false
      }
    })
  } else {
    form.post(route('news-feeds.store'), {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        form.reset()
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

watch(() => form.type, (newType) => {
  form.reset('url', 'image', 'title', 'description', 'meta_data')
  preview.value = null
  imagePreview.value = null
})

watch(() => form.url, async (newUrl) => {
  if (newUrl && form.type !== 'image') {
    await fetchPreview(newUrl)
  } else {
    preview.value = null
    form.meta_data = null
  }
})

const getProxyImageUrl = (url) => {
  if (!url) {
    console.log('[Create] URL gambar kosong, menggunakan fallback');
    return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
  }
  
  // Debug untuk thumbnail Twitter
  if (url && typeof url === 'string') {
    console.log('[Create] URL gambar asli:', url);
  } else {
    console.log('[Create] URL bukan string:', typeof url);
    return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
  }
  
  // Cek apakah URL berasal dari domain yang bermasalah dengan CORS
  if (url.includes('scontent-') || 
      url.includes('cdninstagram') || 
      url.includes('fbcdn.net') ||
      url.includes('twimg.com') ||
      url.includes('twitter.com') ||
      url.includes('pbs.twimg')) {
    console.log('[Create] Menggunakan proxy untuk thumbnail:', url);
    return `/api/proxy-image?url=${encodeURIComponent(url)}`;
  }
  
  return url;
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

/* Style untuk Twitter fallback preview */
.twitter-fallback-preview {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
  background-color: #ffffff;
  padding: 1rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.dark .twitter-fallback-preview {
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