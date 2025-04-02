<template>
  <AuthenticatedLayout title="News Feed">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          News Feed
        </h2>
        <Link
          :href="route('news-feeds.create')"
          class="inline-flex items-center px-3 py-2 md:px-4 md:py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
        >
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            <span class="hidden md:inline ml-2">Tambah Feed</span>
          </span>
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filter Section -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
          <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-4">
            <!-- Filter Tipe -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
              <span class="text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">Tipe:</span>
              <div class="flex flex-wrap gap-2">
                <button 
                  v-for="type in ['all', 'news', 'video', 'image', 'social']" 
                  :key="type"
                  @click="selectedType = type"
                  class="px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg text-sm font-medium transition-colors duration-200 whitespace-nowrap"
                  :class="{
                    'bg-gradient-to-r from-blue-600 to-purple-600 text-white': selectedType === type,
                    'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600': selectedType !== type
                  }"
                >
                  {{ type === 'all' ? 'Semua' : type === 'news' ? 'Berita' : type === 'video' ? 'Video' : type === 'image' ? 'Gambar' : 'Sosial Media' }}
                </button>
              </div>
            </div>

            <!-- Filter Tanggal -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
              <span class="text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">Periode:</span>
              <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <select 
                  v-model="selectedPeriod"
                  class="w-full sm:w-auto px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="all">Semua Waktu</option>
                  <option value="today">Hari Ini</option>
                  <option value="week">Minggu Ini</option>
                  <option value="month">Bulan Ini</option>
                  <option value="year">Tahun Ini</option>
                  <option value="custom">Rentang Kustom</option>
                </select>

                <!-- Custom Date Range -->
                <div v-if="selectedPeriod === 'custom'" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                  <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input 
                      type="date" 
                      v-model="startDate"
                      class="w-full sm:w-auto px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <span class="text-gray-500 whitespace-nowrap">sampai</span>
                    <input 
                      type="date" 
                      v-model="endDate"
                      class="w-full sm:w-auto px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-2 sm:mt-0">
            Total: {{ filteredFeeds.length }} feed
          </div>
        </div>

        <div class="space-y-8">
          <!-- Monthly Groups -->
          <div v-for="(group, month) in groupedFeeds" :key="month" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="border-b border-gray-200 dark:border-gray-700 p-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ month }}</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ group.length }} feed</span>
              </div>
            </div>
            
            <div class="p-6">
              <div v-if="group.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">Belum ada feed yang ditambahkan.</p>
              </div>
              <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="feed in group" :key="feed.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
                  <Link :href="route('news-feeds.show', feed.id)" class="block hover:opacity-75">
                    <div v-if="feed.image_url" class="aspect-video bg-gray-100 dark:bg-gray-700">
                      <img :src="feed.type === 'news' || feed.type === 'video' ? feed.image_url : feed.image_url_full" :alt="feed.title" class="w-full h-full object-cover">
                    </div>
                    <div v-else-if="feed.video_url" class="aspect-video bg-gray-100 dark:bg-gray-700">
                      <video :src="feed.video_url" class="w-full h-full object-cover" controls></video>
                    </div>
                    <!-- Social Media Embed -->
                    <div v-else-if="feed.type === 'social_media'" class="aspect-video bg-gray-100 dark:bg-gray-700">
                      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden h-full flex flex-col">
                        <!-- Header: Platform & Author Info -->
                        <div class="p-2.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                          <div class="flex items-center space-x-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center"
                                 :class="{
                                   'bg-blue-600': feed.meta_data?.platform === 'facebook',
                                   'bg-blue-400': feed.meta_data?.platform === 'twitter',
                                   'bg-gradient-to-r from-purple-500 to-pink-500': feed.meta_data?.platform === 'instagram',
                                   'bg-black': feed.meta_data?.platform === 'tiktok'
                                 }">
                              <svg v-if="feed.meta_data?.platform === 'facebook'" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                              </svg>
                              <svg v-else-if="feed.meta_data?.platform === 'twitter'" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                              </svg>
                              <svg v-else-if="feed.meta_data?.platform === 'instagram'" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                              </svg>
                              <svg v-else-if="feed.meta_data?.platform === 'tiktok'" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 015.2-2.32V9.39a8.16 8.16 0 005.52 2.31V8.1a6.09 6.09 0 01-3.77-1.41z"/>
                              </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300 capitalize">{{ feed.meta_data?.platform }}</span>
                            <span v-if="feed.meta_data?.author_name" class="text-xs text-gray-500 dark:text-gray-400">@{{ feed.meta_data.author_name }}</span>
                          </div>
                          <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(feed.created_at, false) }}</span>
                        </div>

                        <!-- Main Content Section - Make this larger -->
                        <div class="flex-grow flex flex-col">
                          <!-- Embed Content or Thumbnail -->
                          <div v-if="feed.meta_data?.embed_url || feed.meta_data?.thumbnail_url" class="flex-grow">
                            <div v-if="feed.meta_data?.embed_url" class="w-full h-48 md:h-56 bg-gray-100 dark:bg-gray-700">
                              <iframe
                                :src="feed.meta_data.embed_url"
                                class="w-full h-full"
                                frameborder="0"
                                :allow="getIframePermissions(feed.meta_data.platform)"
                                allowfullscreen
                              ></iframe>
                            </div>
                            <div v-else-if="feed.meta_data?.thumbnail_url" class="w-full h-48 md:h-56 bg-gray-100 dark:bg-gray-700">
                              <img :src="feed.meta_data.thumbnail_url" :alt="feed.title || 'Social media post'" class="w-full h-full object-cover">
                            </div>
                          </div>
                          
                          <!-- Raw HTML preview jika tidak ada embed_url atau thumbnail -->
                          <div v-else-if="feed.meta_data?.html" class="bg-gray-100 dark:bg-gray-700 p-3 flex-grow" v-html="feed.meta_data.html"></div>
                        </div>

                        <!-- Footer with title or caption -->
                        <div v-if="feed.title || feed.meta_data?.original_title" class="p-3 border-t border-gray-200 dark:border-gray-700">
                          <h4 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2">
                            {{ feed.title || (feed.meta_data?.original_title && feed.meta_data?.original_title.length > 60 ? feed.meta_data?.original_title.substring(0, 60) + '...' : feed.meta_data?.original_title) }}
                          </h4>
                        </div>
                      </div>
                    </div>
                    <div class="p-4">
                      <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                        <span class="capitalize">{{ feed.type }}</span>
                        <span>•</span>
                        <span>{{ feed.site_name }}</span>
                      </div>
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ feed.title }}</h3>
                      <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2">{{ feed.description }}</p>
                      <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                          <img 
                            :src="feed.user.profile_photo_url || '/default-avatar.png'" 
                            :alt="feed.user.name" 
                            class="w-6 h-6 rounded-full object-cover bg-gray-200 dark:bg-gray-700"
                            @error="$event.target.src = '/default-avatar.png'"
                          >
                          <span class="text-sm text-gray-600 dark:text-gray-300">{{ feed.user.name }}</span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(feed.created_at, true) }}</span>
                      </div>
                    </div>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <Pagination :links="feeds.links" class="mt-6" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  feeds: Object
})

const selectedType = ref('all')
const selectedPeriod = ref('week')
const startDate = ref('')
const endDate = ref('')

// Filter feeds berdasarkan tipe dan periode yang dipilih
const filteredFeeds = computed(() => {
  let filtered = props.feeds.data

  // Filter berdasarkan tipe
  if (selectedType.value !== 'all') {
    filtered = filtered.filter(feed => feed.type === selectedType.value)
  }

  // Filter berdasarkan periode
  if (selectedPeriod.value !== 'all') {
    const now = new Date()
    filtered = filtered.filter(feed => {
      const feedDate = new Date(feed.created_at)
      
      switch (selectedPeriod.value) {
        case 'today':
          return feedDate.toDateString() === now.toDateString()
        case 'week':
          const weekStart = new Date(now.setDate(now.getDate() - now.getDay()))
          return feedDate >= weekStart
        case 'month':
          const monthStart = new Date(now.getFullYear(), now.getMonth(), 1)
          return feedDate >= monthStart
        case 'year':
          const yearStart = new Date(now.getFullYear(), 0, 1)
          return feedDate >= yearStart
        case 'custom':
          if (!startDate.value || !endDate.value) return true
          return feedDate >= new Date(startDate.value) && feedDate <= new Date(endDate.value)
        default:
          return true
      }
    })
  }

  return filtered
})

// Kelompokkan feeds berdasarkan bulan
const groupedFeeds = computed(() => {
  const groups = {}
  
  filteredFeeds.value.forEach(feed => {
    const date = new Date(feed.created_at)
    const monthYear = new Intl.DateTimeFormat('id-ID', { 
      year: 'numeric',
      month: 'long'
    }).format(date)
    
    if (!groups[monthYear]) {
      groups[monthYear] = []
    }
    groups[monthYear].push(feed)
  })
  
  // Urutkan bulan dari yang terbaru
  return Object.fromEntries(
    Object.entries(groups).sort((a, b) => {
      const dateA = new Date(a[1][0].created_at)
      const dateB = new Date(b[1][0].created_at)
      return dateB - dateA
    })
  )
})

const formatDate = (date, showDay = false) => {
  const options = {
    year: 'numeric',
    month: 'long',
    day: showDay ? 'numeric' : undefined
  }
  return new Date(date).toLocaleDateString('id-ID', options)
}

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
    default:
      return '';
  }
}

const getAspectRatio = (platform) => {
  switch (platform) {
    case 'facebook':
    case 'twitter':
      return '1.91/1';
    case 'instagram':
      return '1/1';
    case 'tiktok':
      return '9/16';
    default:
      return '16/9';
  }
}
</script> 