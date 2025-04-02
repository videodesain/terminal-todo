<template>
  <div class="tasks-list">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Daftar Task</h1>
      <router-link 
        to="/tasks/create" 
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
      >
        Buat Task Baru
      </router-link>
    </div>

    <div class="grid gap-6">
      <div v-for="task in tasks" :key="task.id" 
           class="bg-white shadow rounded-lg p-6 hover:shadow-md transition-shadow">
        <router-link :to="{ name: 'tasks.show', params: { id: task.id }}">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ task.title }}</h2>
              <p class="text-gray-600 line-clamp-2">{{ task.description }}</p>
            </div>
            <div class="flex items-center space-x-2">
              <span :class="getStatusBadgeClass(task.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ task.status }}
              </span>
              <span :class="getPriorityBadgeClass(task.priority)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ task.priority }}
              </span>
            </div>
          </div>

          <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
            <div class="flex items-center space-x-4">
              <div class="flex items-center">
                <span class="font-medium">Kategori:</span>
                <span class="ml-1">{{ task.category?.name || 'Tidak ada' }}</span>
              </div>
              <div class="flex items-center">
                <span class="font-medium">Platform:</span>
                <span class="ml-1">{{ task.platform?.name || 'Tidak ada' }}</span>
              </div>
            </div>
            <div class="flex items-center">
              <span class="font-medium">Dibuat oleh:</span>
              <span class="ml-1">{{ task.creator?.name || 'Tidak ada' }}</span>
            </div>
          </div>

          <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div v-for="assignee in task.assignees" :key="assignee.id"
                   class="bg-gray-100 px-3 py-1 rounded-full text-sm text-gray-700">
                {{ assignee.name }}
              </div>
            </div>
            <div class="text-sm text-gray-500">
              {{ formatDate(task.created_at) }}
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'TaskList',
  data() {
    return {
      tasks: []
    }
  },
  async created() {
    try {
      const response = await axios.get('/api/tasks')
      this.tasks = response.data
    } catch (error) {
      console.error('Error fetching tasks:', error)
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return 'Tidak ada';
      return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    getStatusBadgeClass(status) {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },
    getPriorityBadgeClass(priority) {
      const classes = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-red-100 text-red-800'
      };
      return classes[priority] || 'bg-gray-100 text-gray-800';
    }
  }
}
</script> 