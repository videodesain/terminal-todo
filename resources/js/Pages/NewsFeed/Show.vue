<template>
  <AuthenticatedLayout :title="feed.title">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl leading-tight text-gray-800 dark:text-gray-200">
          Detail Feed
        </h2>
        <div class="flex items-center space-x-4" v-if="$page.props.auth.user.id === feed.user_id">
          <Link
            :href="route('news-feeds.edit', feed.id)"
            class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
          >
            Edit
          </Link>
          <button
            @click="showDeleteModal = true"
            class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
          >
            Hapus
          </button>
        </div>
      </div>
    </template>

    <!-- Modal Konfirmasi Hapus -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
          Konfirmasi Penghapusan
        </h2>

        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
          Apakah Anda yakin ingin menghapus feed ini? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="mt-6 flex justify-end space-x-3">
          <SecondaryButton @click="showDeleteModal = false">
            Batal
          </SecondaryButton>

          <DangerButton
            class="ml-3"
            @click="destroy"
          >
            Hapus Feed
          </DangerButton>
        </div>
      </div>
    </Modal>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="max-w-3xl mx-auto">
              <div class="mb-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full capitalize">{{ feed.type }}</span>
                    <span>•</span>
                    <span>{{ feed.site_name }}</span>
                    <span>•</span>
                    <span>{{ formatDate(feed.created_at) }}</span>
                  </div>
                  
                </div>
                <h1 class="text-3xl font-bold mt-4 text-gray-900 dark:text-white">{{ feed.title }}</h1>
                <div class="flex items-center mt-4 space-x-2">
                  <img 
                    :src="feed.user.profile_photo_url || '/default-avatar.png'" 
                    :alt="feed.user.name" 
                    class="w-8 h-8 rounded-full object-cover bg-gray-200 dark:bg-gray-700"
                    @error="$event.target.src = '/default-avatar.png'"
                  >
                  <span class="text-gray-600 dark:text-gray-300">{{ feed.user.name }}</span>
                </div>
              </div>

              <!-- Image Preview -->
              <div v-if="feed.type === 'image'" class="mb-8">
                <div class="relative aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden shadow-lg cursor-pointer" @click="openLightbox">
                  <img 
                    :src="feed.image_url_full" 
                    :alt="feed.title" 
                    class="w-full h-full object-contain"
                  >
                </div>
              </div>

              <!-- Lightbox -->
              <div v-if="showLightbox" 
                   class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90"
                   @click="closeLightbox">
                <div class="relative max-w-[90vw] max-h-[90vh]">
                  <img 
                    :src="feed.image_url_full" 
                    :alt="feed.title" 
                    class="max-w-full max-h-[90vh] object-contain"
                    @click.stop
                  >
                  <button 
                    @click="closeLightbox" 
                    class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors duration-200"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <div v-if="feed.image_url && feed.type !== 'image'" class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-6">
                <img :src="feed.type === 'news' || feed.type === 'video' ? feed.image_url : feed.image_url_full" :alt="feed.title" class="w-full h-full object-cover">
              </div>

              <div v-if="feed.type === 'video' && feed.video_url" class="aspect-video bg-gray-100 dark:bg-gray-700 mb-6 rounded-lg overflow-hidden shadow-lg">
                <iframe
                  :src="feed.video_url"
                  class="w-full h-full"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                ></iframe>
              </div>

              <!-- Social Media Embed -->
              <div v-if="feed.type === 'social_media'" class="mb-6 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                <!-- Platform Header -->
                <div class="px-4 py-3 flex flex-col sm:flex-row items-start sm:items-center sm:justify-between gap-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                  <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center"
                         :class="{
                           'bg-blue-600': feed.meta_data?.platform === 'facebook',
                           'bg-blue-400': feed.meta_data?.platform === 'twitter',
                           'bg-gradient-to-r from-purple-500 to-pink-500': feed.meta_data?.platform === 'instagram',
                           'bg-black': feed.meta_data?.platform === 'tiktok',
                           'bg-red-600': feed.meta_data?.platform === 'youtube' || feed.meta_data?.platform === 'youtube_shorts'
                         }">
                      <svg v-if="feed.meta_data?.platform === 'facebook'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                      </svg>
                      <svg v-else-if="feed.meta_data?.platform === 'twitter'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                      </svg>
                      <svg v-else-if="feed.meta_data?.platform === 'instagram'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white capitalize">{{ feed.meta_data?.platform }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ feed.meta_data?.author_name || feed.site_name }}</p>
                    </div>
                  </div>
                  <!-- Tombol URL original dengan margin-top pada mobile -->
                  <a 
                    :href="feed.url" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="inline-flex items-center px-3 py-1 text-sm bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg transition-colors duration-200 w-full sm:w-auto justify-center sm:justify-start"
                  >
                    <span>Lihat Original</span>
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>

                <!-- Container untuk Embed dengan ukuran portrait -->
                <div class="overflow-hidden p-4 flex justify-center">
                  <div class="social-media-container-portrait">
                    <!-- Menggunakan EmbedManager untuk semua jenis media sosial -->
                    <EmbedManager 
                      :url="feed.url" 
                      :metaData="feed.meta_data" 
                      orientation="portrait"
                    />
                  </div>
                </div>
              </div>

              <div class="prose dark:prose-invert max-w-none mb-8">
                <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed">{{ feed.description }}</p>
              </div>

              <!-- Video Information -->
              <div v-if="feed.type === 'video' && feed.meta_data" class="mt-8 border-t pt-8">
                <div class="flex items-center space-x-4 mb-6">
                  <a :href="feed.meta_data.author_url" target="_blank" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                    <span class="font-medium">{{ feed.meta_data.author_name }}</span>
                  </a>
                  <div class="flex items-center space-x-2 text-gray-500">
                    <span v-if="feed.meta_data.view_count">{{ formatNumber(feed.meta_data.view_count) }} x ditonton</span>
                    <span v-if="feed.meta_data.publish_date">• {{ formatDate(feed.meta_data.publish_date) }}</span>
                  </div>
                </div>
              </div>

              <!-- Article Content -->
              <div v-if="feed.meta_data?.content && feed.type === 'news'" class="mt-8 border-t dark:border-gray-700 pt-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Konten Artikel</h2>
                <div class="prose dark:prose-invert max-w-none">
                  <div class="text-gray-700 dark:text-gray-300 leading-relaxed space-y-4" v-html="formatContent(feed.meta_data.content)"></div>
                </div>
              </div>

              <!-- Information Section - Different for each type -->
              <div class="mt-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                <!-- News Information -->
                <div v-if="feed.type === 'news'">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Artikel</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Sumber</p>
                      <p class="text-gray-900 dark:text-white">{{ feed.site_name }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Publikasi</p>
                      <p class="text-gray-900 dark:text-white">{{ formatDate(feed.created_at) }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Domain</p>
                      <p class="text-gray-900 dark:text-white">{{ getDomain(feed.url) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Image Information -->
                <div v-if="feed.type === 'image'">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Gambar</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Dimensi</p>
                      <p class="text-gray-900 dark:text-white" v-if="feed.meta_data?.dimensions">
                        {{ feed.meta_data.dimensions.width }} x {{ feed.meta_data.dimensions.height }} piksel
                      </p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Ukuran File</p>
                      <p class="text-gray-900 dark:text-white">{{ formatFileSize(feed.meta_data?.file_size) }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Tipe File</p>
                      <p class="text-gray-900 dark:text-white">{{ feed.meta_data?.mime_type }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Upload</p>
                      <p class="text-gray-900 dark:text-white">{{ formatDate(feed.created_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Video Information -->
                <div v-if="feed.type === 'video'">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Video</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Channel</p>
                      <p class="text-gray-900 dark:text-white">{{ feed.meta_data?.author_name }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Views</p>
                      <p class="text-gray-900 dark:text-white">{{ formatNumber(feed.meta_data?.view_count) }} x ditonton</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Platform</p>
                      <p class="text-gray-900 dark:text-white">{{ feed.site_name }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Upload</p>
                      <p class="text-gray-900 dark:text-white">{{ formatDate(feed.meta_data?.publish_date || feed.created_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Social Media Information -->
                <div v-if="feed.type === 'social_media'">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Post</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Platform</p>
                      <p class="text-gray-900 dark:text-white capitalize">{{ feed.platform }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Post</p>
                      <p class="text-gray-900 dark:text-white">{{ formatDate(feed.created_at) }}</p>
                    </div>
                    <div v-if="feed.meta_data?.author_name">
                      <p class="text-sm text-gray-500 dark:text-gray-400">Author</p>
                      <p class="text-gray-900 dark:text-white">{{ feed.meta_data.author_name }}</p>
                    </div>
                    <div v-if="feed.meta_data?.engagement">
                      <p class="text-sm text-gray-500 dark:text-gray-400">Engagement</p>
                      <p class="text-gray-900 dark:text-white">{{ formatNumber(feed.meta_data.engagement) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons - Different for each type -->
              <div class="border-t dark:border-gray-700 pt-8 mt-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                  <!-- News Action -->
                  <a
                    v-if="feed.type === 'news' && feed.url"
                    :href="feed.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                  >
                    <span>Buka di {{ feed.site_name }}</span>
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>

                  <!-- Image Action -->
                  <a
                    v-if="feed.type === 'image'"
                    :href="feed.image_url_full"
                    download
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                  >
                    <span>Download Gambar</span>
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </a>

                  <!-- Video Action -->
                  <a
                    v-if="feed.type === 'video' && feed.url"
                    :href="feed.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                  >
                    <span>Tonton di {{ feed.site_name }}</span>
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </a>
                </div>
              </div>

              <div v-if="showCopiedMessage" class="fixed bottom-4 right-4 bg-gray-800 dark:bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg">
                URL berhasil disalin!
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import EmbedManager from '@/Components/Embeds/EmbedManager.vue'

const props = defineProps({
  feed: Object
})

const showDeleteModal = ref(false)
const showCopiedMessage = ref(false)
const showLightbox = ref(false)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getDomain = (url) => {
  try {
    return new URL(url).hostname.replace('www.', '')
  } catch {
    return ''
  }
}

const getTwitterShareUrl = () => {
  if (typeof window !== 'undefined') {
    return `https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(props.feed.title)}`
  }
  return '#'
}

const copyToClipboard = async () => {
  if (typeof window === 'undefined') return

  try {
    await navigator.clipboard.writeText(window.location.href)
    showCopiedMessage.value = true
    setTimeout(() => {
      showCopiedMessage.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const destroy = () => {
  router.delete(route('news-feeds.destroy', props.feed.id))
}

const formatContent = (content) => {
  if (!content) return '';
  // Split content by newlines and wrap each paragraph in <p> tags
  return content.split('\n\n')
    .filter(p => p.trim())
    .map(p => `<p>${p}</p>`)
    .join('');
}

const formatNumber = (number) => {
  return new Intl.NumberFormat('id-ID').format(number);
}

const formatFileSize = (bytes) => {
  if (!bytes) return '';
  const units = ['B', 'KB', 'MB', 'GB'];
  let size = bytes;
  let unitIndex = 0;
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex++;
  }
  
  return `${size.toFixed(1)} ${units[unitIndex]}`;
}

const openLightbox = () => {
  showLightbox.value = true
  // Prevent scrolling when lightbox is open
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  showLightbox.value = false
  // Restore scrolling
  document.body.style.overflow = 'auto'
}

// Handle escape key to close lightbox
onMounted(() => {
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && showLightbox.value) {
      closeLightbox()
    }
  })
})
</script>

<style>
/* Styling untuk embed disediakan oleh komponen-komponen EmbedManager */
.social-media-container-portrait {
  width: 100%;
  max-width: 500px; /* Lebar maksimum container */
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow: hidden;
  border-radius: 0.5rem;
}

/* Memastikan tampilan portrait untuk setiap platform */
.social-media-container-portrait :deep(.twitter-embed-wrapper),
.social-media-container-portrait :deep(.instagram-embed-wrapper),
.social-media-container-portrait :deep(.facebook-embed-wrapper),
.social-media-container-portrait :deep(.tiktok-embed-wrapper),
.social-media-container-portrait :deep(.youtube-embed-wrapper),
.social-media-container-portrait :deep(.default-embed-wrapper) {
  width: 100% !important;
  min-height: 600px !important;
  max-height: none !important;
  margin: 0 auto !important;
  border-radius: 0.5rem;
  overflow: hidden;
}

/* Khusus untuk Twitter */
.social-media-container-portrait :deep(.twitter-embed-iframe) {
  min-height: 600px !important;
  width: 100% !important;
  max-width: 100% !important;
  border: none !important;
}

/* Khusus untuk TikTok */
.social-media-container-portrait :deep(.tiktok-embed-container) {
  min-height: 750px !important;
  width: 100% !important;
  max-width: 100% !important;
}

/* Khusus untuk Instagram */
.social-media-container-portrait :deep(.instagram-embed-iframe) {
  min-height: 750px !important;
  width: 100% !important;
  background-color: white;
}

/* Khusus untuk Facebook */
.social-media-container-portrait :deep(.facebook-embed-container) {
  min-height: 750px !important;
  width: 100% !important;
}

/* Khusus untuk YouTube */
.social-media-container-portrait :deep(.youtube-embed-iframe) {
  min-height: 500px !important;
  aspect-ratio: 9/16 !important; /* Portrait mode untuk YouTube */
  width: 100% !important;
}

/* Khusus untuk YouTube Shorts */
.social-media-container-portrait :deep(.youtube-shorts-iframe) {
  min-height: 650px !important;
  aspect-ratio: 9/16 !important;
  width: 100% !important;
  max-width: 360px !important; /* Shorts biasanya lebih sempit */
  margin: 0 auto !important;
}

/* Style untuk dark mode */
.dark .social-media-container-portrait :deep(.instagram-embed-iframe),
.dark .social-media-container-portrait :deep(.twitter-embed-iframe),
.dark .social-media-container-portrait :deep(.facebook-embed-iframe) {
  background-color: #1f2937 !important;
  border: 1px solid #374151 !important;
}

/* Media query untuk layar kecil */
@media (max-width: 640px) {
  .social-media-container-portrait {
    max-width: 100%;
  }
  
  .social-media-container-portrait :deep(.youtube-shorts-iframe) {
    max-width: 100% !important;
  }
}
</style> 