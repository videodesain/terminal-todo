<template>
    <Head title="Archived Tasks">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
    </Head>

    <AuthenticatedLayout :auth="auth" title="Task Arsip">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Task Arsip
                </h2>
                <Link
                    :href="route('tasks.index')"
                    class="inline-flex items-center px-3 py-2 md:px-4 md:py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden md:inline">Kembalikan</span>
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
                                placeholder="Cari task arsip..."
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

                    <!-- Row 2: Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Date Range -->
                        <div class="md:col-span-12">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input
                                        v-model="dateFilter.start"
                                        type="date"
                                        placeholder="Dari tanggal"
                                        class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                                    >
                                </div>
                                <div>
                                    <input
                                        v-model="dateFilter.end"
                                        type="date"
                                        placeholder="Sampai tanggal"
                                        class="w-full px-4 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500 dark:focus:ring-primary-400"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archived Tasks Grid with Pagination -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="task in paginatedTasks" 
                        :key="task.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-300"
                    >
                        <!-- Task Content -->
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
                            
                            <!-- Archived Date -->
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                    {{ formatDate(task.archived_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Restore Button -->
                        <div class="mt-4 flex justify-end">
                            <button 
                                v-if="canManageTask"
                                @click.stop="confirmUnarchiveTask(task)"
                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Kembalikan
                            </button>
                        </div>
                    </div>

                    <!-- Load More Button -->
                    <div v-if="hasMoreTasks" class="col-span-full flex justify-center mt-6">
                        <button
                            @click="loadMore"
                            class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Muat lebih banyak...
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div 
                        v-if="filteredTasks.length === 0" 
                        class="col-span-full flex flex-col items-center justify-center py-12 text-center"
                    >
                        <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                            Tidak Ada Task Arsip
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Belum ada task yang diarsipkan. Task yang diarsipkan akan muncul di sini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal Konfirmasi Unarchive -->
    <Modal :show="showUnarchiveModal" @close="closeUnarchiveModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Kembalikan Task dari Arsip
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Apakah Anda yakin ingin mengembalikan task ini ke Kanban Board?
            </p>
            <div class="mt-6 flex justify-end space-x-4">
                <SecondaryButton 
                    @click="closeUnarchiveModal"
                    variant="outline"
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                >
                    Batal
                </SecondaryButton>

                <button
                    @click="unarchiveSelectedTask"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-25"
                >
                    {{ processing ? 'Memproses...' : 'Ya, Kembalikan' }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { usePermission } from '@/Composables/usePermission';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IconButton from '@/Components/IconButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';
import toast from '@/toast.js';

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
    error: String
});

// Get page props dan permissions
const page = usePage();
const { hasPermission } = usePermission();

// Local state
const search = ref('');
const priorityFilter = ref('');
const categoryFilter = ref('');
const dateFilter = ref({
    start: '',
    end: ''
});

// State untuk modal unarchive
const showUnarchiveModal = ref(false);
const selectedTask = ref(null);
const processing = ref(false);

// Pagination state
const itemsPerPage = 12;
const currentPage = ref(1);

onMounted(() => {
    // Debug logs dihapus
});

// Filter tasks
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
            const taskDate = new Date(task.archived_at);
            
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

        return true;
    });
});

// Paginated tasks
const paginatedTasks = computed(() => {
    return filteredTasks.value.slice(0, currentPage.value * itemsPerPage);
});

// Check if has more tasks
const hasMoreTasks = computed(() => {
    return filteredTasks.value.length > currentPage.value * itemsPerPage;
});

// Load more tasks
const loadMore = () => {
    currentPage.value++;
};

// Reset pagination when filters change
watch([search, dateFilter, priorityFilter, categoryFilter], () => {
    currentPage.value = 1;
});

// Helper functions
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
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Perbaikan fungsi hasPermission untuk menggunakan composable
const canManageTask = computed(() => {
    return hasPermission('edit-task') || hasPermission('manage-task');
});

// Fungsi untuk membuka modal konfirmasi
const confirmUnarchiveTask = (task) => {
    selectedTask.value = task;
    showUnarchiveModal.value = true;
};

// Fungsi untuk menutup modal
const closeUnarchiveModal = () => {
    showUnarchiveModal.value = false;
    selectedTask.value = null;
    processing.value = false;
};

// Modifikasi fungsi unarchiveSelectedTask
const unarchiveSelectedTask = async () => {
    if (!selectedTask.value) return;
    
    processing.value = true;
    
    try {
        const response = await axios.post(route('tasks.unarchive', selectedTask.value.id));
        
        if (response.data.success) {
            toast.success('Task berhasil dikembalikan ke Kanban Board');
            closeUnarchiveModal();
            
            router.reload({ only: ['tasks'] });
        }
    } catch (error) {
        toast.error('Gagal mengembalikan task dari arsip');
    } finally {
        processing.value = false;
    }
};
</script> 