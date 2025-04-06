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
                  v-for="type in ['all', 'news', 'video', 'image', 'social_media']" 
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
              <div v-else class="masonry-grid">
                <div v-for="feed in group" 
                     :key="feed.id" 
                     class="masonry-item">
                  <div class="feed-card bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
                    <!-- Card media section -->
                    <div class="feed-card-media">
                      <!-- Image/Video Preview -->
                      <Link v-if="feed.image_url" 
                            :href="route('news-feeds.show', feed.id)" 
                            class="block w-full h-full">
                        <img :src="feed.type === 'news' || feed.type === 'video' ? feed.image_url : feed.image_url_full" 
                             :alt="feed.title" 
                             class="w-full h-full object-cover">
                      </Link>

                      <!-- Social Media Preview -->
                      <div v-else-if="feed.type === 'social_media'" class="w-full h-full">
                        <!-- Platform Header -->
                        <div class="absolute top-2 left-2 z-20 flex items-center space-x-2 bg-black/70 text-white px-2 py-1 rounded-md">
                          <div class="w-6 h-6 rounded-full flex items-center justify-center"
                               :class="{
                                 'bg-blue-600': feed.meta_data?.platform === 'facebook',
                                 'bg-blue-400': feed.meta_data?.platform === 'twitter',
                                 'bg-gradient-to-r from-purple-500 to-pink-500': feed.meta_data?.platform === 'instagram',
                                 'bg-black': feed.meta_data?.platform === 'tiktok',
                                 'bg-red-600': feed.meta_data?.platform === 'youtube' || feed.meta_data?.platform === 'youtube_shorts',
                                 'bg-gradient-to-r from-gray-700 to-gray-900': feed.meta_data?.platform === 'threads'
                               }">
                            <svg v-if="feed.meta_data?.platform === 'facebook'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <svg v-else-if="feed.meta_data?.platform === 'twitter'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            <svg v-else-if="feed.meta_data?.platform === 'instagram'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                            <svg v-else-if="feed.meta_data?.platform === 'tiktok'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 015.2-2.32V9.39a8.16 8.16 0 005.52 2.31V8.1a6.09 6.09 0 01-3.77-1.41z"/>
                            </svg>
                            <svg v-else-if="feed.meta_data?.platform === 'youtube' || feed.meta_data?.platform === 'youtube_shorts'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                            <svg v-else-if="feed.meta_data?.platform === 'threads'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.587 1.5 12.077c0-3.282.931-6.008 2.693-7.893C5.958 2.296 8.184 1.083 11 1.083h.007c2.546.018 4.686.903 6.173 2.56 1.528 1.704 2.32 4.067 2.32 6.905c0 3.83-2.756 7.502-7.314 7.502c-1.867 0-3.5-1.084-4.158-2.7c-.024-.057-.05-.11-.07-.167c-.413.354-1.019.895-1.428 1.213c-.238.188-.483.372-.718.548c.305 1.067 1.26 2.118 2.054 2.956c1.372 1.438 3.515 2.204 6.402 2.224c2.812 0 5.204-.978 6.741-2.77C22.374 17.658 23 15.234 23 12.1c0-3.213-.842-5.854-2.444-7.658C18.817 2.44 16.099 1.044 12.12 1.008h-.01c-3.034 0-5.626 1.32-7.278 3.714C3.426 7.177 2.33 10.408 2.33 14.06c.002 3.038.569 5.278 1.737 6.892C5.321 22.73 7.195 23.632 9.75 23.864c.038-.847.32-1.932.994-3.253a5.213 5.213 0 0 1-.892-.299a3.468 3.468 0 0 1-.773-.442c-1.302-1.013-1.962-2.952-1.962-5.75c0-2.322.553-4.228 1.613-5.53c1.04-1.268 2.541-1.895 4.588-1.914h.022c2.947 0 4.281 1.136 5.043 2.136c.736.966 1.1 2.24 1.1 3.86c0 1.668-.364 3.14-1.057 4.278c-.675 1.108-1.676 1.68-2.97 1.68c-1.922 0-2.76-1.452-2.76-2.75c0-.565.22-1.053.44-1.536c.214-.474.458-1.01.458-1.708c0-.513-.162-.927-.496-1.264c-.325-.324-.796-.5-1.426-.5c-.845 0-1.511.325-1.947.946c-.49.691-.734 1.616-.734 2.764c0 1.298.345 2.32 1.053 3.136c.254-.203.522-.392.8-.568a1.441 1.441 0 0 1-.016-.211c0-.24.066-.471.188-.686c.498-.94 1.43-1.474 2.566-1.474c1.583 0 2.98.941 3.646 2.348a4.8 4.8 0 0 0 .823-2.757c0-1.33-.292-2.287-.85-2.988c-.548-.684-1.477-1.038-2.87-1.038h-.014c-1.673.017-2.773.527-3.427 1.326c-.817.993-1.226 2.576-1.226 4.595c0 2.255.483 3.766 1.323 4.532c.316.288.65.49 1.016.645c.05-.678.218-1.593.587-2.623a3.132 3.132 0 0 0-1.01-1.21c-.2-1.437.068-3.16.718-3.947c.33-.4.863-.857 1.926-.87h.006c1.028 0 1.694.575 1.98 1.017c.344.53.523 1.31.523 2.251a5.39 5.39 0 0 1-.5 2.322c.043.587.34 1.233.936 1.233c.53 0 .947-.244 1.315-.849c.487-.798.752-1.96.752-3.286c0-1.33-.28-2.308-.814-3.019c-.583-.776-1.58-1.153-3.015-1.153h-.01c-1.615.015-2.702.467-3.334 1.388c-.864 1.261-1.002 3.398-.346 5.338c.02.033.035.068.051.104c.5.66.122 1.28.133 1.285c-.212-.134-.436-.258-.671-.372c-.702-1.212-.85-3.13-.368-4.62c-.313.258-.602.592-.863.99c-.522.79-.832 1.86-.91 3.16a8.384 8.384 0 0 0-1.85-1.218c.34.643.643 1.368.908 2.152c.16.475.306.968.438 1.475c.13.507.245 1.025.342 1.553c.237 1.285.384 2.631.435 4.03c.776-.06 1.467-.293 2.104-.698"/>
                            </svg>
                          </div>
                          <span class="text-xs capitalize">{{ feed.meta_data?.author_name || feed.meta_data?.platform }}</span>
                        </div>

                        <!-- TikTok Preview -->
                        <div v-if="feed.meta_data?.platform === 'tiktok'" class="w-full h-full">
                          <!-- Thumbnail dengan Play Button -->
                          <div class="relative w-full h-full">
                            <img v-if="feed.meta_data?.thumbnail_url"
                                 :src="feed.meta_data.thumbnail_url"
                                 :alt="feed.title"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                              <div class="w-12 h-12 rounded-full bg-black/50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                              </div>
                            </div>
                            <!-- Platform Badge -->
                            <div class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded-md flex items-center space-x-1">
                              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 015.2-2.32V9.39a8.16 8.16 0 005.52 2.31V8.1a6.09 6.09 0 01-3.77-1.41z"/>
                              </svg>
                              <span>TikTok</span>
                            </div>
                          </div>
                        </div>

                        <!-- Twitter Preview -->
                        <div v-else-if="feed.meta_data?.platform === 'twitter'" class="w-full h-full">
                          <!-- Thumbnail dengan Play Button -->
                          <div class="relative w-full h-full">
                            <img v-if="feed.meta_data?.thumbnail_url"
                                 :src="getProxyImageUrl(feed.meta_data.thumbnail_url)"
                                 :alt="feed.title || feed.meta_data?.description || 'Twitter post'"
                                 class="w-full h-full object-cover"
                                 @error="handleTwitterImageError($event, feed)">
                            <!-- Fallback Twitter image jika tidak ada thumbnail -->
                            <img v-else-if="feed.meta_data?.twitter_image"
                                 :src="getProxyImageUrl(feed.meta_data.twitter_image)"
                                 :alt="feed.title || feed.meta_data?.description || 'Twitter post'"
                                 class="w-full h-full object-cover"
                                 @error="handleTwitterImageError($event, feed)">
                            <!-- Fallback jika tidak ada thumbnail -->
                            <div v-else class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center">
                              <svg class="w-16 h-16 text-white/80" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                              </svg>
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                              <div class="w-12 h-12 rounded-full bg-blue-400/70 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                              </div>
                            </div>
                            <!-- Platform Badge -->
                            <div class="absolute bottom-2 right-2 bg-blue-400/90 text-white text-xs px-2 py-1 rounded-md flex items-center space-x-1">
                              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                              </svg>
                              <span>Twitter</span>
                            </div>
                          </div>
                        </div>

                        <!-- Threads Preview -->
                        <div v-else-if="feed.meta_data?.platform === 'threads'" class="w-full h-full">
                          <!-- Thumbnail dengan Logo Threads -->
                          <div class="relative w-full h-full">
                            <img v-if="feed.meta_data?.thumbnail_url"
                                 :src="getProxyImageUrl(feed.meta_data.thumbnail_url)"
                                 :alt="feed.title"
                                 class="w-full h-full object-cover">
                            <!-- Fallback jika tidak ada thumbnail -->
                            <div v-else class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                              <svg class="w-16 h-16 text-white/80" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.587 1.5 12.077c0-3.282.931-6.008 2.693-7.893C5.958 2.296 8.184 1.083 11 1.083h.007c2.546.018 4.686.903 6.173 2.56 1.528 1.704 2.32 4.067 2.32 6.905c0 3.83-2.756 7.502-7.314 7.502c-1.867 0-3.5-1.084-4.158-2.7c-.024-.057-.05-.11-.07-.167c-.413.354-1.019.895-1.428 1.213c-.238.188-.483.372-.718.548c.305 1.067 1.26 2.118 2.054 2.956c1.372 1.438 3.515 2.204 6.402 2.224c2.812 0 5.204-.978 6.741-2.77C22.374 17.658 23 15.234 23 12.1c0-3.213-.842-5.854-2.444-7.658C18.817 2.44 16.099 1.044 12.12 1.008h-.01c-3.034 0-5.626 1.32-7.278 3.714C3.426 7.177 2.33 10.408 2.33 14.06c.002 3.038.569 5.278 1.737 6.892C5.321 22.73 7.195 23.632 9.75 23.864c.038-.847.32-1.932.994-3.253a5.213 5.213 0 0 1-.892-.299a3.468 3.468 0 0 1-.773-.442c-1.302-1.013-1.962-2.952-1.962-5.75c0-2.322.553-4.228 1.613-5.53c1.04-1.268 2.541-1.895 4.588-1.914h.022c2.947 0 4.281 1.136 5.043 2.136c.736.966 1.1 2.24 1.1 3.86c0 1.668-.364 3.14-1.057 4.278"/>
                              </svg>
                            </div>
                            <!-- Platform Badge -->
                            <div class="absolute bottom-2 right-2 bg-gradient-to-r from-gray-700 to-gray-900 text-white text-xs px-2 py-1 rounded-md flex items-center space-x-1">
                              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.587 1.5 12.077c0-3.282.931-6.008 2.693-7.893C5.958 2.296 8.184 1.083 11 1.083h.007c2.546.018 4.686.903 6.173 2.56 1.528 1.704 2.32 4.067 2.32 6.905c0 3.83-2.756 7.502-7.314 7.502c-1.867 0-3.5-1.084-4.158-2.7c-.024-.057-.05-.11-.07-.167c-.413.354-1.019.895-1.428 1.213c-.238.188-.483.372-.718.548c.305 1.067 1.26 2.118 2.054 2.956c1.372 1.438 3.515 2.204 6.402 2.224c2.812 0 5.204-.978 6.741-2.77C22.374 17.658 23 15.234 23 12.1c0-3.213-.842-5.854-2.444-7.658C18.817 2.44 16.099 1.044 12.12 1.008h-.01c-3.034 0-5.626 1.32-7.278 3.714C3.426 7.177 2.33 10.408 2.33 14.06c.002 3.038.569 5.278 1.737 6.892C5.321 22.73 7.195 23.632 9.75 23.864c.038-.847.32-1.932.994-3.253a5.213 5.213 0 0 1-.892-.299a3.468 3.468 0 0 1-.773-.442c-1.302-1.013-1.962-2.952-1.962-5.75c0-2.322.553-4.228 1.613-5.53c1.04-1.268 2.541-1.895 4.588-1.914h.022c2.947 0 4.281 1.136 5.043 2.136c.736.966 1.1 2.24 1.1 3.86c0 1.668-.364 3.14-1.057 4.278"/>
                              </svg>
                              <span>Threads</span>
                            </div>
                          </div>
                        </div>

                        <!-- YouTube Shorts Preview -->
                        <div v-else-if="feed.meta_data?.platform === 'youtube_shorts'" class="w-full h-full">
                          <div class="relative w-full h-full">
                            <!-- Gunakan thumbnail dari meta_data atau generate dari video ID -->
                            <img v-if="feed.meta_data?.thumbnail_url"
                                 :src="feed.meta_data.thumbnail_url"
                                 :alt="feed.title"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                              <div class="w-12 h-12 rounded-full bg-red-600/90 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                              </div>
                            </div>
                            <!-- Platform Badge -->
                            <div class="absolute bottom-2 right-2 bg-red-600/90 text-white text-xs px-2 py-1 rounded-md flex items-center space-x-1">
                              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                              </svg>
                              <span>Shorts</span>
                            </div>
                          </div>
                        </div>

                        <!-- Other Social Media Preview -->
                        <div v-else class="w-full h-full">
                          <img v-if="feed.meta_data?.thumbnail_url"
                               :src="feed.meta_data.thumbnail_url"
                               :alt="feed.title"
                               class="w-full h-full object-cover">
                          <!-- Fallback jika tidak ada thumbnail -->
                          <div v-else class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                          </div>
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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 hover:text-blue-600 dark:hover:text-blue-400 line-clamp-2">
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
        </div>

        <!-- Tampilkan "Tidak ada data" jika tidak ada feed yang ditemukan -->
        <div v-if="Object.keys(groupedFeeds).length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center mt-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Tidak ada data yang ditemukan</h3>
          <p class="text-gray-600 dark:text-gray-400">
            {{ selectedType !== 'all' || selectedPeriod !== 'all' 
              ? `Tidak ada feed ${selectedType !== 'all' ? selectedType === 'news' ? 'berita' : selectedType === 'video' ? 'video' : selectedType === 'image' ? 'gambar' : 'sosial media' : ''} yang ditemukan ${selectedPeriod === 'week' ? 'minggu ini' : selectedPeriod === 'today' ? 'hari ini' : selectedPeriod === 'month' ? 'bulan ini' : selectedPeriod === 'year' ? 'tahun ini' : ''}.` 
              : 'Belum ada feed yang ditambahkan.' }}
          </p>
          <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <Link
              :href="route('news-feeds.create')"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg"
            >
              Tambah Feed Baru
            </Link>
            <button
              v-if="selectedPeriod !== 'all'"
              @click="selectedPeriod = 'all'"
              class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg"
            >
              Lihat Semua Feed
            </button>
          </div>
        </div>

        <!-- Pagination hanya ditampilkan jika ada data -->
        <Pagination v-if="feeds.links && feeds.links.length > 3" :links="feeds.links" class="mt-6" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref, computed, onMounted, nextTick, watch } from 'vue'

const props = defineProps({
  feeds: Object
})

// Logging untuk debugging jumlah data
console.log('Total feeds dari server:', props.feeds.data.length);
console.log('Total feeds di pagination:', props.feeds.total);
console.log('Pagination links:', props.feeds.links);

const selectedType = ref('all')
const selectedPeriod = ref('week')
const startDate = ref('')
const endDate = ref('')

// Filter feeds berdasarkan tipe dan periode yang dipilih
const filteredFeeds = computed(() => {
  console.log('Filtering feeds. Data awal:', props.feeds.data.length);
  
  let filtered = [...props.feeds.data]; // Gunakan spread operator untuk membuat copy baru
  console.log('Data setelah copy:', filtered.length);

  // Filter berdasarkan tipe
  if (selectedType.value !== 'all') {
    console.log('Filtering by type:', selectedType.value);
    if (selectedType.value === 'social_media') {
      // Khusus untuk filter social_media
      filtered = filtered.filter(feed => feed.type === 'social_media')
    } else {
      filtered = filtered.filter(feed => feed.type === selectedType.value)
    }
    console.log('Data setelah filter tipe:', filtered.length);
  }

  // Filter berdasarkan periode
  if (selectedPeriod.value !== 'all') {
    console.log('Filtering by period:', selectedPeriod.value);
    const now = new Date()
    filtered = filtered.filter(feed => {
      const feedDate = new Date(feed.created_at)
      
      switch (selectedPeriod.value) {
        case 'today':
          return feedDate.toDateString() === now.toDateString()
        case 'week':
          const weekStart = new Date();
          weekStart.setDate(now.getDate() - 7); // 7 hari terakhir, bukan sejak awal minggu
          return feedDate >= weekStart
        case 'month':
          const monthStart = new Date(now.getFullYear(), now.getMonth(), 1)
          return feedDate >= monthStart
        case 'year':
          const yearStart = new Date(now.getFullYear(), 0, 1)
          return feedDate >= yearStart
        case 'custom':
          if (!startDate.value || !endDate.value) return true
          const start = new Date(startDate.value);
          const end = new Date(endDate.value);
          end.setHours(23, 59, 59, 999); // Set ke akhir hari
          return feedDate >= start && feedDate <= end
        default:
          return true
      }
    })
    console.log('Data setelah filter periode:', filtered.length);
  }

  return filtered
})

// Logging setiap kali filter berubah
watch([selectedType, selectedPeriod, startDate, endDate], (newValues, oldValues) => {
  console.log('Filter berubah:', { 
    type: newValues[0], 
    period: newValues[1], 
    filteredCount: filteredFeeds.value.length 
  });
});

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
      return 'autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share';
    case 'instagram':
      return 'autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share';
    case 'tiktok':
      return 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen';
    default:
      return 'autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share';
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
    case 'youtube':
      return '16/9';
    case 'youtube_shorts':
      return '9/16';
    default:
      return '16/9';
  }
}

const handleTikTokError = (event, feed) => {
  console.error('TikTok embed error:', event);
  
  // Coba gunakan native TikTok embed script
  if (feed.meta_data?.platform === 'tiktok') {
    // Mencari div parent iframe untuk mengganti isinya
    const parentElement = event.target.parentElement;
    if (parentElement) {
      // Hapus iframe yang error
      parentElement.innerHTML = '';
      
      // Buat div untuk TikTok embed
      const tikTokDiv = document.createElement('div');
      
      // Tambahkan HTML jika tersedia
      if (feed.meta_data?.html) {
        tikTokDiv.innerHTML = feed.meta_data.html;
        parentElement.appendChild(tikTokDiv);
        
        // Load TikTok embed script
        const script = document.createElement('script');
        script.src = 'https://www.tiktok.com/embed.js';
        script.async = true;
        document.body.appendChild(script);
      } else if (feed.meta_data?.thumbnail_url) {
        // Jika tidak ada HTML, tampilkan thumbnail
        const img = document.createElement('img');
        img.src = feed.meta_data.thumbnail_url;
        img.alt = feed.title || 'TikTok post';
        img.classList.add('w-full', 'h-full', 'object-contain');
        parentElement.appendChild(img);
      }
    }
  }
}

// Fungsi untuk inisialisasi Masonry
const initMasonry = () => {
  nextTick(() => {
    const grids = document.querySelectorAll('.masonry-grid');
    grids.forEach(grid => {
      const items = grid.querySelectorAll('.masonry-item');
      let maxHeight = 0;
      
      // Reset heights
      items.forEach(item => {
        item.style.gridRowEnd = 'unset';
        maxHeight = Math.max(maxHeight, item.clientHeight);
      });

      // Set spanning
      items.forEach(item => {
        const rowSpan = Math.ceil(item.clientHeight / (maxHeight / 10));
        item.style.gridRowEnd = `span ${rowSpan}`;
      });
    });
  });
};

// Watch untuk perubahan feeds dan filter
watch([filteredFeeds, selectedType, selectedPeriod], () => {
  nextTick(() => {
    initMasonry();
  });
});

onMounted(() => {
  initMasonry();
  
  // Debug info untuk Twitter
  props.feeds.data.forEach(feed => {
    if (feed.meta_data?.platform === 'twitter') {
      console.log('[Index] Twitter metadata:', feed.meta_data);
      console.log('[Index] Twitter feed data:', {
        id: feed.id,
        title: feed.title,
        url: feed.url,
        thumbnail: feed.meta_data?.thumbnail_url
      });
      
      // Cek jika thumbnail URL ada tapi tidak dirender
      if (feed.meta_data?.thumbnail_url) {
        // Buat test image untuk validasi URL
        const testImg = new Image();
        testImg.onload = () => console.log('[Index] Thumbnail URL valid:', feed.meta_data.thumbnail_url);
        testImg.onerror = () => console.error('[Index] Thumbnail URL tidak valid:', feed.meta_data.thumbnail_url);
        testImg.src = feed.meta_data.thumbnail_url;
      }
    }
  });
  
  // Load TikTok script
  const loadTikTokScript = () => {
    if (!document.querySelector('script[src*="tiktok.com/embed.js"]')) {
      const script = document.createElement('script');
      script.src = 'https://www.tiktok.com/embed.js';
      script.async = true;
      document.body.appendChild(script);
      
      script.onload = () => {
        if (window.TikTok) {
          window.TikTok.reloadEmbeds();
        }
      };
    }
  };

  loadTikTokScript();
});

// Fungsi helper untuk mendapatkan aspect ratio class
const getCardAspectClass = (feed) => {
  if (feed.type === 'social_media') {
    if (feed.meta_data?.platform === 'tiktok') {
      return 'aspect-tiktok';
    } else if (feed.meta_data?.platform === 'instagram') {
      return 'aspect-instagram';
    }
  } else if (feed.type === 'image') {
    return 'aspect-image';
  }
  return 'aspect-news';
};

// Fungsi untuk menggunakan proxy pada gambar yang bermasalah CORS
const getProxyImageUrl = (url) => {
  if (!url) {
    console.log('[Index] URL gambar kosong, menggunakan fallback');
    return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
  }
  
  // Debug untuk thumbnail Twitter
  if (url && typeof url === 'string') {
    console.log('[Index] URL gambar asli:', url);
  } else {
    console.log('[Index] URL bukan string:', typeof url);
    return 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
  }
  
  // Cek apakah URL berasal dari domain yang bermasalah dengan CORS
  if (url.includes('scontent-') || 
      url.includes('cdninstagram') || 
      url.includes('fbcdn.net') ||
      url.includes('twimg.com') ||
      url.includes('twitter.com') ||
      url.includes('pbs.twimg')) {
    console.log('[Index] Menggunakan proxy untuk thumbnail:', url);
    return `/api/proxy-image?url=${encodeURIComponent(url)}`;
  }
  
  return url;
};

const handleTwitterImageError = (event, feed) => {
  console.error('[Index] Twitter image load error:', event.target.src);
  
  // Reset error handler untuk mencegah infinite loop
  event.target.onerror = null;
  
  // Coba URL alternatif jika ada
  if (feed.meta_data?.twitter_image && event.target.src !== getProxyImageUrl(feed.meta_data.twitter_image)) {
    console.log('[Index] Mencoba menggunakan twitter_image sebagai fallback');
    event.target.src = getProxyImageUrl(feed.meta_data.twitter_image);
    return;
  }
  
  // Cek apakah ada URL gambar yang tersedia di image_url
  if (feed.image_url && event.target.src !== feed.image_url) {
    console.log('[Index] Mencoba menggunakan image_url sebagai fallback');
    event.target.src = feed.image_url;
    return;
  }
  
  // Fallback terakhir - gunakan gambar default Twitter
  event.target.src = 'https://abs.twimg.com/responsive-web/client-web/icon-default.522d363a.png';
  
  // Atur ukuran dan style untuk gambar fallback
  event.target.classList.remove('object-cover');
  event.target.classList.add('object-contain', 'p-8');
};
</script>

<style>
/* Masonry Grid Layout */
.masonry-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  grid-auto-rows: 10px;
  grid-gap: 1.5rem;
}

.masonry-item {
  break-inside: avoid;
  width: 100%;
  max-width: 350px;
  margin: 0 auto;
}

/* Card styling */
.feed-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.feed-card-media {
  position: relative;
  aspect-ratio: 16/9;
  width: 100%;
  overflow: hidden;
  background: #f3f4f6;
}

/* Khusus untuk konten TikTok menggunakan aspect ratio portrait */
.feed-card-media:has(div[class*="tiktok"]) {
  aspect-ratio: 9/16;
}

.feed-card-media img,
.feed-card-media video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* TikTok specific styling */
.tiktok-embed-container {
  position: relative !important;
  width: 100% !important;
  height: 100% !important;
  overflow: hidden !important;
  aspect-ratio: 9/16;
}

.tiktok-embed-container iframe,
.tiktok-embed-container blockquote {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  border: none !important;
  margin: 0 !important;
  padding: 0 !important;
}

/* Preview untuk TikTok */
[class*="tiktok"] img {
  aspect-ratio: 9/16;
  object-fit: cover;
  width: 100%;
}

/* Hide scrollbars */
.tiktok-embed-container {
  -ms-overflow-style: none !important;
  scrollbar-width: none !important;
}

.tiktok-embed-container::-webkit-scrollbar {
  display: none !important;
}

/* Hover effects */
.feed-card-media:hover .hover-overlay {
  opacity: 1;
}

/* Platform badges */
.platform-badge {
  @apply absolute bottom-2 right-2 text-white text-xs px-2 py-1 rounded-md flex items-center space-x-1;
}

.platform-badge.tiktok {
  @apply bg-black/70;
}

.platform-badge.youtube {
  @apply bg-red-600/90;
}

/* Play button styling */
.play-button {
  @apply w-12 h-12 rounded-full flex items-center justify-center;
  background: rgba(0, 0, 0, 0.5);
  transition: transform 0.2s;
}

.play-button:hover {
  transform: scale(1.1);
}

.play-button.youtube {
  @apply bg-red-600/90;
}

.play-button.tiktok {
  @apply bg-black/50;
}
</style> 