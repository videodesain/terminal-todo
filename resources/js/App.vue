<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo & Menu -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/" class="text-xl font-bold text-gray-900 dark:text-white">
                Task Manager
              </router-link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center">
            <div class="ml-3 relative">
              <div class="flex items-center space-x-4">
                <span class="text-gray-700 dark:text-gray-300">{{ user?.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <router-view></router-view>
    </main>
    
    <!-- PWA Install Prompt -->
    <PWAInstallPrompt />
  </div>
</template>

<script>
import PWAInstallPrompt from './Components/PWAInstallPrompt.vue';

export default {
  name: 'App',
  components: {
    PWAInstallPrompt
  },
  data() {
    return {
      user: null
    }
  },
  async created() {
    try {
      // Ambil data user dari API
      const response = await axios.get('/api/user')
      this.user = response.data
    } catch (error) {
      console.error('Error fetching user:', error)
    }
  }
}
</script>

<style>
/* Tambahkan style global di sini jika diperlukan */
</style> 