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
                  <!-- Card media section -->
                  <div v-if="feed.image_url" class="aspect-video bg-gray-100 dark:bg-gray-700">
                    <Link :href="route('news-feeds.show', feed.id)" class="block hover:opacity-75">
                      <img :src="feed.type === 'news' || feed.type === 'video' ? feed.image_url : feed.image_url_full" :alt="feed.title" class="w-full h-full object-cover">
                    </Link>
                  </div>
                  <div v-else-if="feed.video_url" class="aspect-video bg-gray-100 dark:bg-gray-700">
                    <Link :href="route('news-feeds.show', feed.id)" class="block hover:opacity-75">
                      <video :src="feed.video_url" class="w-full h-full object-cover" controls></video>
                    </Link>
                  </div>
                  <!-- Social Media Embed -->
                  <div v-else-if="feed.type === 'social_media'" class="aspect-video bg-gray-100 dark:bg-gray-700 relative">
                    <Link :href="route('news-feeds.show', feed.id)" class="absolute inset-0 z-10 hover:bg-black/10 transition-colors duration-200"></Link>
                    <div v-if="feed.meta_data?.thumbnail_url" class="w-full h-full">
                      <img :src="feed.meta_data.thumbnail_url" :alt="feed.title || 'Social media'" class="w-full h-full object-cover">
                    </div>
                    <div v-else-if="feed.meta_data?.html" class="w-full h-full p-2 overflow-hidden" v-html="feed.meta_data.html"></div>
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <div class="bg-gray-200 dark:bg-gray-700 rounded-full p-4">
                        <svg class="w-10 h-10 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/>
                          <path d="M13 7h-2v6h6v-2h-4z"/>
                        </svg>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Card content section -->
                  <div class="p-4">
                    <Link :href="route('news-feeds.show', feed.id)" class="block">
                      <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                        <span class="capitalize">{{ feed.type === 'social_media' ? 'media' : feed.type }}</span>
                        <span>•</span>
                        <span>{{ feed.site_name || feed.meta_data?.provider_name || '' }}</span>
                      </div>
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 hover:text-blue-600 dark:hover:text-blue-400">
                        {{ feed.title || feed.meta_data?.original_title || feed.meta_data?.title || 'Untitled' }}
                      </h3>
                      <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2">
                        {{ feed.description || feed.meta_data?.description || '' }}
                      </p>
                    </Link>
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