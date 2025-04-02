<template>
  <div class="task-create">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Buat Task Baru</h1>
      <router-link 
        to="/tasks" 
        class="text-gray-600 hover:text-gray-900"
      >
        Kembali ke Daftar Task
      </router-link>
    </div>

    <form @submit.prevent="submitForm" class="bg-white shadow rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Judul Task
          </label>
          <input 
            v-model="form.title"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            required
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Kategori
          </label>
          <select 
            v-model="form.category_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Pilih Kategori</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Platform
          </label>
          <select 
            v-model="form.platform_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Pilih Platform</option>
            <option v-for="platform in platforms" :key="platform.id" :value="platform.id">
              {{ platform.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tim
          </label>
          <select 
            v-model="form.team_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Pilih Tim</option>
            <option v-for="team in teams" :key="team.id" :value="team.id">
              {{ team.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Prioritas
          </label>
          <select 
            v-model="form.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            required
          >
            <option value="low">Rendah</option>
            <option value="medium">Sedang</option>
            <option value="high">Tinggi</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Status
          </label>
          <select 
            v-model="form.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            required
          >
            <option value="pending">Pending</option>
            <option value="in_progress">Dalam Proses</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tanggal Mulai
          </label>
          <input 
            v-model="form.start_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tanggal Selesai
          </label>
          <input 
            v-model="form.due_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Deskripsi
          </label>
          <textarea 
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Assignee
          </label>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="user in users" :key="user.id" class="flex items-center">
              <input 
                type="checkbox"
                :id="'user-' + user.id"
                :value="user.id"
                v-model="form.assignee_ids"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label :for="'user-' + user.id" class="ml-2 block text-sm text-gray-900">
                {{ user.name }}
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <button 
          type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          :disabled="loading"
        >
          {{ loading ? 'Menyimpan...' : 'Simpan Task' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TaskCreate',
  data() {
    return {
      form: {
        title: '',
        description: '',
        category_id: '',
        platform_id: '',
        team_id: '',
        priority: 'medium',
        status: 'pending',
        start_date: '',
        due_date: '',
        assignee_ids: []
      },
      categories: [],
      platforms: [],
      teams: [],
      users: [],
      loading: false
    }
  },
  async created() {
    try {
      const [categoriesRes, platformsRes, teamsRes, usersRes] = await Promise.all([
        axios.get('/api/categories'),
        axios.get('/api/platforms'),
        axios.get('/api/teams'),
        axios.get('/api/users')
      ])
      this.categories = categoriesRes.data
      this.platforms = platformsRes.data
      this.teams = teamsRes.data
      this.users = usersRes.data
    } catch (error) {
      console.error('Error fetching data:', error)
    }
  },
  methods: {
    async submitForm() {
      this.loading = true
      try {
        await axios.post('/api/tasks', this.form)
        this.$router.push('/tasks')
      } catch (error) {
        console.error('Error creating task:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script> 