<template>
    <Head title="Tasks">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
    </Head>

    <AuthenticatedLayout :auth="auth" title="Manajemen Task">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Tasks Board
                </h2>
                <!-- Add Task Button -->
                <Link
                    :href="route('tasks.create')"
                    class="inline-flex items-center px-3 py-2 md:px-4 md:py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden md:inline ml-2">Tambah Task</span>
                    </span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Filter Section -->
                <div class="mb-6 space-y-4">
                    <!-- Row 1: Search, Category, Priority -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Search Input -->
                        <div class="md:col-span-4">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari task..."
                                class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                            >
                        </div>

                        <!-- Category & Priority Filter Container -->
                        <div class="grid grid-cols-2 gap-4 md:col-span-8 md:grid md:grid-cols-2">
                            <!-- Category Filter -->
                            <div>
                                <select
                                    v-model="categoryFilter"
                                    class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Priority Filter -->
                            <div>
                                <select
                                    v-model="priorityFilter"
                                    class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                                >
                                    <option value="">Semua Prioritas</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Date Range & Archive Button -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Date Range -->
                        <div class="md:col-span-8">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input
                                        v-model="dateFilter.start"
                                        type="date"
                                        class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                                    >
                                </div>
                                <div>
                                    <input
                                        v-model="dateFilter.end"
                                        type="date"
                                        class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Archive Button -->
                        <div class="md:col-span-4">
                            <Link
                                :href="route('tasks.archived')"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                                Lihat Arsip
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Kanban Board -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 overflow-x-auto">
                    <div 
                        v-for="status in statuses" 
                        :key="status"
                        class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600"
                        :class="{ 'kanban-column-highlight': isDraggingOver === status }"
                        @dragover.prevent="handleDragOver($event, status)"
                        @dragleave.prevent="handleDragLeave"
                        @drop.prevent="handleDrop($event, status)"
                    >
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center justify-between">
                            <span class="flex items-center">
                                <span :class="{
                                    'text-gray-500': status === 'draft',
                                    'text-blue-500': status === 'in_progress',
                                    'text-green-500': status === 'completed',
                                    'text-red-500': status === 'cancelled'
                                }">
                                    <svg v-if="status === 'draft'" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <svg v-if="status === 'in_progress'" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <svg v-if="status === 'completed'" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <svg v-if="status === 'cancelled'" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                                {{ getStatusLabel(status) }}
                            </span>
                            <span class="bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium px-2.5 py-1 rounded-full">
                                {{ tasksGroupedByStatus[status].length }}
                            </span>
                        </h3>
                        
                        <!-- Task List For This Status -->
                        <div class="min-h-[200px] space-y-3">
                            <div 
                                v-for="task in paginatedTasksByStatus(status)" 
                                :key="`task-${task.id}`"
                                class="task-card bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 dark:border-gray-700"
                                :class="{
                                    'animate-highlight': props.highlight && task.id === parseInt(props.highlight),
                                    'opacity-60 border-blue-400': isDragging && draggingTask?.id === task.id,
                                    'transform hover:-translate-y-1': !isDragging || draggingTask?.id !== task.id,
                                    'opacity-75': task.archived_at
                                }"
                                draggable="true"
                                @dragstart="handleDragStart($event, task)"
                                @dragend="handleDragEnd"
                                @click="viewTaskDetails(task.id)"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <!-- Category Marker -->
                                        <div
                                            v-if="task.category" 
                                            class="h-4 w-4 rounded-full"
                                            :style="{ backgroundColor: task.category.color || '#CBD5E0' }"
                                            :title="task.category.name"
                                        ></div>
                                        
                                        <!-- Priority Tag -->
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': task.priority === 'low',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': task.priority === 'medium',
                                                'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200': task.priority === 'high',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': task.priority === 'urgent'
                                            }"
                                        >
                                            {{ getPriorityLabel(task.priority) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2">
                                        <IconButton 
                                            v-if="hasPermission('edit-task') || hasPermission('manage-task')"
                                            @click.stop="editTask(task.id)"
                                            variant="ghost"
                                            size="sm"
                                            title="Edit Task"
                                            class="text-gray-500 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-400"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </IconButton>
                                        <IconButton
                                            v-if="hasPermission('delete-task') || hasPermission('manage-task')"
                                            @click.stop="confirmTaskDeletion(task)"
                                            variant="ghost"
                                            size="sm"
                                            title="Hapus Task"
                                            class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </IconButton>
                                    </div>
                                </div>
                                
                                <!-- Task Content -->
                                <div class="font-medium text-gray-900 dark:text-gray-100 mb-1 line-clamp-1">{{ task.title }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">{{ task.description }}</div>
                                
                                <!-- Task Footer -->
                                <div class="flex items-center justify-between mt-2">
                                    <!-- Assignees -->
                                    <div class="flex -space-x-2 overflow-hidden">
                                        <template v-for="(assignee, index) in task.assignees.slice(0, 3)" :key="`assignee-${assignee.id}-${task.id}`">
                                            <img
                                                v-if="assignee.avatar_url"
                                                :src="assignee.avatar_url"
                                                :alt="assignee.name"
                                                class="h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-800"
                                                :title="assignee.name"
                                                :style="{ zIndex: 30 - index }"
                                            >
                                            <div
                                                v-else
                                                class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 ring-2 ring-white dark:ring-gray-800 flex items-center justify-center text-xs font-medium text-gray-500 dark:text-gray-400"
                                                :title="assignee.name"
                                                :style="{ zIndex: 30 - index }"
                                            >
                                                {{ assignee.name.charAt(0) }}
                                            </div>
                                        </template>
                                        <div
                                            v-if="task.assignees.length > 3"
                                            class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 ring-2 ring-white dark:ring-gray-800 flex items-center justify-center text-xs font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            +{{ task.assignees.length - 3 }}
                                        </div>
                                    </div>
                                    
                                    <!-- Due Date & Archive Button -->
                                    <div class="flex items-center space-x-2">
                                        <div class="text-xs font-medium" 
                                            :class="{
                                                'text-red-500': isOverdue(task.due_date),
                                                'text-gray-500 dark:text-gray-400': !isOverdue(task.due_date)
                                            }"
                                        >
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ formatDate(task.due_date) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Load More Button -->
                            <div v-if="hasMoreTasks(status)" class="mt-4 text-center">
                                <button
                                    @click="loadMore(status)"
                                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Muat lebih banyak...
                                </button>
                            </div>

                            <!-- Empty State -->
                            <div 
                                v-if="filteredTasksByStatus(status).length === 0" 
                                class="flex items-center justify-center h-24 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg"
                            >
                                <p class="text-sm text-gray-400 dark:text-gray-500">Tidak ada task</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingTaskDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Hapus Task
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus task ini?
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        Batal
                    </SecondaryButton>

                    <PrimaryButton
                        variant="danger"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTask"
                    >
                        {{ form.processing ? 'Menghapus...' : 'Hapus Task' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import IconButton from '@/Components/IconButton.vue';
import PermissionGate from '@/Components/PermissionGate.vue';
import axios from 'axios';
import toast from '@/toast.js';
import { usePermission } from '@/Composables/usePermission';

const props = defineProps({
    auth: {
        type: Object,
        required: true
    },
    tasks: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    highlight: {
        type: String,
        default: null
    },
    statusFilter: String,
    timestamp: Number,
    error: String
});

// Local state
const search = ref('');
const priorityFilter = ref('');
const categoryFilter = ref('');
const dateFilter = ref({
    start: '',
    end: ''
});
const selectedTask = ref(null);
const confirmingTaskDeletion = ref(false);
const form = useForm({});
const debugMode = ref(false);  // Set ke false permanen

// Pagination state
const itemsPerPage = 5;
const currentPage = ref({
    draft: 1,
    in_progress: 1,
    completed: 1,
    cancelled: 1
});

// Array status tetap
const statuses = [
    'draft',
    'in_progress',
    'completed',
    'cancelled'
];

// Notification handler
onMounted(() => {
    // Tampilkan error jika ada
    if (props.error) {
        toast.error(props.error);
    }
    
    // Tampilkan notifikasi dari flash message
    const page = usePage();
    if (page.props.flash?.success) {
        toast.success(page.props.flash.success);
    }
    
    if (page.props.flash?.error) {
        toast.error(page.props.flash.error);
    }
    
    if (page.props.flash?.warning) {
        toast.warning(page.props.flash.warning);
    }
});

// Filter tasks berdasarkan input pencarian dan tanggal
const filteredTasks = computed(() => {
    return props.tasks.filter(task => {
        // Filter berdasarkan pencarian teks
        if (search.value) {
            const searchTerm = search.value.toLowerCase();
            if (!task.title?.toLowerCase().includes(searchTerm) && 
                !task.description?.toLowerCase().includes(searchTerm)) {
                return false;
            }
        }

        // Filter berdasarkan tanggal
        if (dateFilter.value.start || dateFilter.value.end) {
            const taskDate = new Date(task.created_at);
            
            if (dateFilter.value.start) {
                const startDate = new Date(dateFilter.value.start);
                if (taskDate < startDate) return false;
            }
            
            if (dateFilter.value.end) {
                const endDate = new Date(dateFilter.value.end);
                endDate.setHours(23, 59, 59, 999);
                if (taskDate > endDate) return false;
            }
        }

        // Filter berdasarkan prioritas
        if (priorityFilter.value && task.priority !== priorityFilter.value) {
            return false;
        }

        // Filter berdasarkan kategori
        if (categoryFilter.value && task.category?.id !== parseInt(categoryFilter.value)) {
            return false;
        }

        // Filter task yang sudah diarsip
        if (task.archived_at) {
            return false;
        }

        return true;
    });
});

// Computed untuk group tasks berdasarkan status
const tasksGroupedByStatus = computed(() => {
    // Buat struktur kosong untuk semua status
    const result = {
        draft: [],
        in_progress: [],
        completed: [],
        cancelled: []
    };
    
    // Proses semua task yang sudah difilter
    filteredTasks.value.forEach(task => {
        // Tentukan status (default ke 'draft' jika tidak valid)
        const status = task.status && statuses.includes(task.status) ? task.status : 'draft';
        // Tambahkan task ke kolom status yang sesuai
        result[status].push(task);
    });
    
    return result;
});

// Filter tasks berdasarkan status dengan pagination
const filteredTasksByStatus = (status) => {
    return tasksGroupedByStatus.value[status] || [];
};

// Pagination untuk tasks berdasarkan status
const paginatedTasksByStatus = (status) => {
    const tasksInStatus = filteredTasksByStatus(status);
    const page = currentPage.value[status] || 1;
    return tasksInStatus.slice(0, page * itemsPerPage);
};

// Check if has more tasks
const hasMoreTasks = (status) => {
    const tasksInStatus = filteredTasksByStatus(status);
    const page = currentPage.value[status] || 1;
    return tasksInStatus.length > page * itemsPerPage;
};

// Load more tasks
const loadMore = (status) => {
    if (!currentPage.value[status]) {
        currentPage.value[status] = 1;
    }
    currentPage.value[status]++;
};

// Reset pagination when filters change
watch([search, dateFilter, priorityFilter, categoryFilter], () => {
    statuses.forEach(status => {
        currentPage.value[status] = 1;
    });
});

// Helper functions
const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Draft',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
    };
    return labels[status] || status;
};

const getPriorityLabel = (priority) => {
    const labels = {
        'low': 'Low',
        'medium': 'Medium',
        'high': 'High',
        'urgent': 'Urgent'
    };
    return labels[priority] || priority;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

// Check permissions
const hasPermission = (permission) => {
    // Konversi ke format permission yang benar
    const formattedPermission = permission.replace(' ', '-');
    return props.auth.permissions && props.auth.permissions.includes(formattedPermission);
};

// Task deletion
const confirmTaskDeletion = (task) => {
    selectedTask.value = task;
    confirmingTaskDeletion.value = true;
};

const closeModal = () => {
    confirmingTaskDeletion.value = false;
    selectedTask.value = null;
};

const deleteTask = () => {
    if (!selectedTask.value) return;
    
    form.delete(route('tasks.destroy', selectedTask.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            // Hard refresh untuk mendapatkan data terbaru
            setTimeout(() => window.location.reload(), 500);
        }
    });
};

// Edit task
const editTask = (taskId) => {
    router.visit(route('tasks.edit', taskId));
};

const isDraggingOver = ref(null);
const isDragging = ref(false);
const draggingTask = ref(null);

// Cek apakah task sudah melewati deadline
const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    const now = new Date();
    const due = new Date(dueDate);
    return due < now;
};

// Fungsi untuk drag & drop
const handleDragStart = (event, task) => {
    isDragging.value = true;
    draggingTask.value = task;
    event.dataTransfer.setData('text/plain', task.id.toString());
    event.dataTransfer.effectAllowed = 'move';
    // Tambahkan class untuk visual feedback
    event.target.classList.add('opacity-50');
};

const handleDragOver = (event, status) => {
    event.preventDefault();
    isDraggingOver.value = status;
    event.dataTransfer.dropEffect = 'move';
    // Tambahkan visual feedback untuk drop zone
    event.currentTarget.classList.add('bg-blue-50', 'dark:bg-blue-900');
};

const handleDragLeave = (event) => {
    event.preventDefault();
    isDraggingOver.value = null;
    // Hapus visual feedback
    event.currentTarget.classList.remove('bg-blue-50', 'dark:bg-blue-900');
};

const handleDragEnd = (event) => {
    isDragging.value = false;
    draggingTask.value = null;
    isDraggingOver.value = null;
    // Hapus visual feedback
    if (event.target) {
        event.target.classList.remove('opacity-50');
    }
};

const handleDrop = async (event, newStatus) => {
    event.preventDefault();
    
    // Hapus visual feedback
    event.currentTarget.classList.remove('bg-blue-50', 'dark:bg-blue-900');
    
    const taskId = event.dataTransfer.getData('text/plain');
    console.log('Dropping task:', taskId, 'to status:', newStatus);
    
    if (!draggingTask.value || draggingTask.value.status === newStatus) {
        handleDragEnd(event);
        return;
    }
    
    try {
        const originalStatus = draggingTask.value.status;
        draggingTask.value.status = newStatus;
        
        const response = await axios.patch(route('tasks.update-status', taskId), {
            status: newStatus
        }, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.data.success) {
            const updatedTask = response.data.task;
            const taskIndex = props.tasks.findIndex(t => t.id === parseInt(updatedTask.id));
            
            if (taskIndex !== -1) {
                const updatedTaskCopy = { ...updatedTask };
                props.tasks[taskIndex] = updatedTaskCopy;
                console.log('Task updated successfully:', updatedTaskCopy);
            }
            
            toast.success(`Status task berhasil diubah menjadi ${getStatusLabel(newStatus)}`);
        }
    } catch (error) {
        console.error('Error updating task status:', error);
        if (draggingTask.value) {
            draggingTask.value.status = originalStatus;
        }
        
        const errorMessage = error.response?.data?.message || 'Gagal memperbarui status task';
        toast.error(errorMessage);
    } finally {
        handleDragEnd(event);
    }
};

// Fungsi untuk melihat detail task
const viewTaskDetails = (taskId) => {
    router.visit(route('tasks.show', taskId));
};

// Fungsi route yang aman
const route = (...args) => {
    if (typeof window.route === 'undefined') {
        console.error('Route function not found');
        return '#';
    }
    return window.route(...args);
};
</script>

<style scoped>
/* Task cards */
.task-card {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform, opacity, box-shadow;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Drag handle */
.drag-handle {
    touch-action: none;
    -webkit-user-select: none;
    user-select: none;
}

/* Custom styles for Kanban board */
.kanban-column-highlight {
    border: 2px dashed #93c5fd !important;
    background-color: #eef2ff !important;
}

.dark .kanban-column-highlight {
    border: 2px dashed #3b82f6 !important;
    background-color: #1e293b !important;
}

@keyframes highlight {
    0% { background-color: rgba(59, 130, 246, 0); }
    50% { background-color: rgba(59, 130, 246, 0.2); }
    100% { background-color: rgba(59, 130, 246, 0); }
}

.animate-highlight {
    animation: highlight 2s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid-cols-1 > * {
        margin-bottom: 0.5rem;
    }
    
    .grid-cols-1 > *:last-child {
        margin-bottom: 0;
    }
}
</style> 