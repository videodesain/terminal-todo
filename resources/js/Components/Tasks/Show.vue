<template>
  <div class="task-detail">
    <div v-if="task" class="bg-white shadow rounded-lg p-6">
      <div class="flex justify-between items-start mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ task.title }}</h2>
        <div class="flex items-center space-x-2">
          <span :class="getStatusBadgeClass(task.status)" class="px-3 py-1 rounded-full text-sm font-medium">
            {{ task.status }}
          </span>
          <span :class="getPriorityBadgeClass(task.priority)" class="px-3 py-1 rounded-full text-sm font-medium">
            {{ task.priority }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Informasi Task</h3>
          <div class="space-y-2">
            <p class="text-gray-600">
              <span class="font-medium">Kategori:</span> {{ task.category?.name || 'Tidak ada' }}
            </p>
            <p class="text-gray-600">
              <span class="font-medium">Platform:</span> {{ task.platform?.name || 'Tidak ada' }}
            </p>
            <p class="text-gray-600">
              <span class="font-medium">Tim:</span> {{ task.team?.name || 'Tidak ada' }}
            </p>
            <p class="text-gray-600">
              <span class="font-medium">Dibuat oleh:</span> {{ task.creator?.name || 'Tidak ada' }}
            </p>
            <p class="text-gray-600">
              <span class="font-medium">Tanggal Dibuat:</span> {{ formatDate(task.created_at) }}
            </p>
          </div>
        </div>

        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Jadwal</h3>
          <div class="space-y-2">
            <p class="text-gray-600">
              <span class="font-medium">Tanggal Mulai:</span> {{ formatDate(task.start_date) }}
            </p>
            <p class="text-gray-600">
              <span class="font-medium">Tanggal Selesai:</span> {{ formatDate(task.due_date) }}
            </p>
            <p v-if="task.completed_at" class="text-gray-600">
              <span class="font-medium">Selesai pada:</span> {{ formatDate(task.completed_at) }}
            </p>
          </div>
        </div>
      </div>

      <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Deskripsi</h3>
        <p class="text-gray-600 whitespace-pre-wrap">{{ task.description || 'Tidak ada deskripsi' }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Assignee</h3>
        <div class="flex flex-wrap gap-2">
          <div v-for="assignee in task.assignees" :key="assignee.id" 
               class="flex items-center space-x-2 bg-gray-100 px-3 py-1 rounded-full">
            <span class="text-sm text-gray-700">{{ assignee.name }}</span>
          </div>
          <div v-if="!task.assignees?.length" class="text-gray-500">
            Belum ada assignee
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskShow',
  props: {
    task: {
      type: Object,
      required: true
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