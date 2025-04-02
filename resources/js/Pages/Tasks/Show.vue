<template>
    <Head title="Tasks" />

    <AuthenticatedLayout :auth="auth" title="Detail Task">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Detail Task
                </h2>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('tasks.index')"
                        class="inline-flex items-center px-3 py-2 md:px-4 md:py-2 bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span>Kembali</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Main Content Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                    <!-- Header with Category & Status Badge -->
                    <div class="relative h-16 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between px-6">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: task.category.color }"></div>
                            <span class="text-white font-medium">{{ task.category.name }}</span>
                        </div>
                        <div class="flex space-x-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': task.status === 'draft',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': task.status === 'in_review',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': task.status === 'approved',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': task.status === 'in_progress',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': task.status === 'completed',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': task.status === 'cancelled'
                                }"
                            >
                                {{ getStatusLabel(task.status) }}
                            </span>
                            
                            <span class="px-2 py-1 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': task.priority === 'low',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': task.priority === 'medium',
                                    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200': task.priority === 'high',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': task.priority === 'urgent'
                                }"
                            >
                                {{ getPriorityLabel(task.priority) }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Content Section -->
                    <div class="p-6">
                        <!-- Title & Description with Action Buttons -->
                        <div class="mb-6">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 lg:gap-6">
                                <!-- Title and Description Container -->
                                <div class="flex-1">
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ task.title }}</h1>
                                    <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ task.description || 'Tidak ada deskripsi' }}</p>
                                </div>

                                <!-- Action Buttons Container -->
                                <div class="flex flex-wrap gap-2 lg:flex-shrink-0">
                                    <!-- Baris Pertama Mobile & Tablet: Edit & Hapus -->
                                    <div class="flex items-center gap-2 w-full sm:w-auto">
                                        <Link
                                            v-if="hasPermission('edit-task') || hasPermission('manage-task') || task.created_by === auth.user?.id || isAssignee"
                                            :href="route('tasks.edit', task.id)"
                                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                        >
                                            <i class="fas fa-edit mr-2"></i> Edit
                                        </Link>
                                        <button
                                            v-if="hasPermission('delete-task') || hasPermission('manage-task') || task.created_by === auth.user?.id"
                                            @click="confirmDeleteTask"
                                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                        >
                                            <i class="fas fa-trash mr-2"></i> Hapus
                                        </button>
                                    </div>
                                    
                                    <!-- Baris Kedua Mobile & Tablet: Arsipkan -->
                                    <div class="w-full sm:w-auto">
                                        <button
                                            @click="archiveTask"
                                            class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                        >
                                            <i class="fas fa-archive mr-2"></i> Arsipkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Platform Card -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center border border-gray-200 dark:border-gray-700">
                                <div class="mr-4 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <i :class="['text-xl fa-brands text-blue-600 dark:text-blue-400', `fa-${task.platform.icon}`]"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Platform</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ task.platform.name }}</p>
                                </div>
                            </div>
                            
                            <!-- Start Date Card -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center border border-gray-200 dark:border-gray-700">
                                <div class="mr-4 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <i class="fas fa-calendar-day text-lg text-green-600 dark:text-green-400"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Mulai</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ formatDate(task.start_date) }}</p>
                                </div>
                            </div>
                            
                            <!-- Due Date Card -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center border border-gray-200 dark:border-gray-700">
                                <div class="mr-4 w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-lg text-orange-600 dark:text-orange-400"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenggat Waktu</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ formatDate(task.due_date) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Team Info -->
                        <div v-if="task.team" class="mb-6 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="mr-4 w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                    <i class="fas fa-users text-lg text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tim</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ task.team.name }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Assignees Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Penanggung Jawab</h3>
                            <div v-if="task.assignees && task.assignees.length > 0" class="flex flex-wrap gap-2">
                                <div 
                                    v-for="assignee in task.assignees" 
                                    :key="assignee.id"
                                    class="flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full"
                                >
                                    <div v-if="assignee.avatar_url" class="w-7 h-7 rounded-full mr-2 overflow-hidden">
                                        <img :src="assignee.avatar_url" :alt="assignee.name" class="w-full h-full object-cover">
                                    </div>
                                    <div v-else class="w-7 h-7 rounded-full bg-indigo-200 dark:bg-indigo-800 flex items-center justify-center text-xs font-semibold mr-2">
                                        {{ getInitials(assignee.name) }}
                                    </div>
                                    <div>
                                        <span class="text-sm">{{ assignee.name }}</span>
                                        <span v-if="assignee.role" class="text-xs text-indigo-600 dark:text-indigo-400 block">{{ assignee.role }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center">
                                <i class="fas fa-user-slash mr-2 text-gray-400"></i>
                                Tidak ada penanggung jawab yang ditugaskan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Section Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Komentar dan Aktivitas</h3>
                    </div>
                    
                    <div class="p-6">
                        <!-- Add Comment Form -->
                        <div v-if="hasPermission('edit-task') || hasPermission('manage-task') || task.created_by === auth.user.id || isAssignee" class="mb-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Tambah Komentar</h4>
                            <form @submit.prevent="submitComment">
                                <TextArea
                                    v-model="commentForm.content"
                                    placeholder="Tambahkan komentar..."
                                    class="w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-200"
                                    rows="3"
                                    required
                                />
                                <InputError :message="commentForm.errors.content" class="mt-2" />

                                <!-- Submit Button -->
                                <div class="mt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
                                        :disabled="commentForm.processing"
                                    >
                                        <span v-if="commentForm.processing">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Mengirim...
                                        </span>
                                        <span v-else>Kirim Komentar</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Comments List -->
                        <div v-if="task.comments && task.comments.length > 0" class="space-y-4">
                            <div
                                v-for="comment in task.comments"
                                :key="comment.id"
                                class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start">
                                        <div class="mr-3">
                                            <div v-if="comment.user.avatar_url" class="h-10 w-10 rounded-full overflow-hidden">
                                                <img :src="comment.user.avatar_url" :alt="comment.user.name" class="h-full w-full object-cover">
                                            </div>
                                            <div v-else class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                                {{ getInitials(comment.user.name) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ comment.user.name }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(comment.created_at) }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Comment Actions -->
                                    <div class="flex space-x-2" v-if="comment.user.id === auth.user.id || hasPermission('manage-task')">
                                        <button 
                                            v-if="editingCommentId !== comment.id"
                                            @click="startEditComment(comment)" 
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <button 
                                            @click="deleteComment(comment.id)" 
                                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                        >
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Comment Content -->
                                <div v-if="editingCommentId !== comment.id" class="mt-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ comment.content }}</p>
                                </div>
                                
                                <!-- Edit Comment Form -->
                                <div v-else class="mt-3">
                                    <form @submit.prevent="updateComment(comment.id)">
                                        <TextArea
                                            v-model="editCommentForm.content"
                                            class="w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-200"
                                            rows="3"
                                        ></TextArea>
                                        <div class="mt-3 flex justify-end space-x-2">
                                            <button 
                                                type="button" 
                                                @click="cancelEditComment" 
                                                class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                                            >
                                                Batal
                                            </button>
                                            <button 
                                                type="submit" 
                                                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                                                :disabled="editCommentForm.processing"
                                            >
                                                {{ editCommentForm.processing ? 'Memperbarui...' : 'Simpan' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No Comments Message -->
                        <div v-else class="py-10 text-center">
                            <i class="fas fa-comments text-5xl text-gray-400 dark:text-gray-600"></i>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Belum ada komentar pada task ini.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500">Jadilah yang pertama memberikan komentar!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal Konfirmasi Hapus Komentar -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Hapus Komentar
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Apakah Anda yakin ingin menghapus komentar ini? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex justify-end space-x-3">
                <SecondaryButton @click="showDeleteModal = false">Batal</SecondaryButton>
                <DangerButton @click="confirmDeleteComment" :class="{ 'opacity-25': isDeleting }" :disabled="isDeleting">
                    {{ isDeleting ? 'Menghapus...' : 'Hapus Komentar' }}
                </DangerButton>
            </div>
        </div>
    </Modal>

    <!-- Modal Konfirmasi Hapus Task -->
    <Modal :show="deletingTask" @close="closeDeleteTaskModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Hapus Task
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Apakah Anda yakin ingin menghapus task ini? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex justify-end space-x-3">
                <SecondaryButton @click="closeDeleteTaskModal">Batal</SecondaryButton>
                <DangerButton @click="deleteTask" :class="{ 'opacity-25': taskDeleteForm.processing }" :disabled="taskDeleteForm.processing">
                    {{ taskDeleteForm.processing ? 'Menghapus...' : 'Hapus Task' }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PermissionGate from '@/Components/PermissionGate.vue';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import { computed, ref } from 'vue';
import toast from '@/toast.js';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    task: {
        type: Object,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
});

const hasPermission = (permission) => {
    return props.auth?.permissions?.includes(permission);
};

const isAssignee = computed(() => {
    if (!props.task.assignees || !props.auth.user) return false;
    return props.task.assignees.some(assignee => assignee.id === props.auth.user.id);
});

const commentForm = ref({
    content: '',
    errors: {},
    processing: false
});

// Function untuk reset form
const resetCommentForm = () => {
    commentForm.value.content = '';
    commentForm.value.errors = {};
};

const editingCommentId = ref(null);
const editCommentForm = ref({
    content: '',
    errors: {},
    processing: false
});

// State untuk modal konfirmasi hapus
const showDeleteModal = ref(false);
const commentIdToDelete = ref(null);
const isDeleting = ref(false);

const deletingTask = ref(false);
const taskDeleteForm = useForm({});

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Draft',
        in_review: 'In Review',
        approved: 'Approved',
        in_progress: 'In Progress',
        completed: 'Completed',
        cancelled: 'Cancelled'
    };
    return labels[status] || status;
};

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        urgent: 'Urgent'
    };
    return labels[priority] || priority;
};

const formatDate = (date) => {
    if (!date) return '-';
    try {
        return format(new Date(date), 'dd MMM yyyy HH:mm', { locale: id });
    } catch (error) {
        return '-';
    }
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const submitComment = () => {
    if (!commentForm.value.content) {
        alert('Silakan masukkan konten komentar');
        return;
    }

    const content = commentForm.value.content;
    commentForm.value.processing = true;
    resetCommentForm();
    
    // Log pengiriman komentar
    console.log('[COMMENT] Mengirim komentar untuk task ID:', props.task.id);
    
    // Dapatkan CSRF token dari meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Gunakan axios untuk mengirim komentar (menghindari Inertia)
    axios.post(route('tasks.comments.store', props.task.id), {
        content: content
    }, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('[COMMENT] Komentar berhasil dikirim:', response.data);
        commentForm.value.processing = false;
        
        // Tampilkan notifikasi sukses
        if (typeof toast !== 'undefined' && toast.success) {
            toast.success('Komentar berhasil ditambahkan');
        } else {
            alert('Komentar berhasil ditambahkan');
        }
        
        // Reload halaman untuk menampilkan komentar baru
        setTimeout(() => {
            window.location.href = route('tasks.show', props.task.id) + '?refresh=' + Date.now();
        }, 1000);
    })
    .catch(error => {
        console.error('[COMMENT] Error mengirim komentar:', error);
        commentForm.value.processing = false;
        
        // Kembalikan nilai ke form jika gagal
        commentForm.value.content = content;
        
        // Set error jika ada
        if (error.response?.data?.errors) {
            commentForm.value.errors = error.response.data.errors;
        }
        
        // Tampilkan notifikasi error
        let errorMessage = 'Terjadi kesalahan saat mengirim komentar';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors?.content) {
            errorMessage = error.response.data.errors.content[0];
        }
        
        if (typeof toast !== 'undefined' && toast.error) {
            toast.error(errorMessage);
        } else {
            alert(errorMessage);
        }
    });
};

const startEditComment = (comment) => {
    editingCommentId.value = comment.id;
    editCommentForm.value.content = comment.content;
    editCommentForm.value.errors = {};
};

const cancelEditComment = () => {
    editingCommentId.value = null;
    editCommentForm.value.content = '';
    editCommentForm.value.errors = {};
};

const updateComment = (commentId) => {
    if (!editCommentForm.value.content.trim()) {
        alert('Konten komentar tidak boleh kosong');
        return;
    }

    editCommentForm.value.processing = true;
    
    // Log pengiriman komentar
    console.log('[COMMENT] Memperbarui komentar ID:', commentId);
    
    // Dapatkan CSRF token dari meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Gunakan axios untuk update komentar
    axios.put(route('tasks.comments.update', commentId), {
        content: editCommentForm.value.content
    }, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('[COMMENT] Komentar berhasil diperbarui:', response.data);
        editCommentForm.value.processing = false;
        editingCommentId.value = null;
        
        // Tampilkan notifikasi sukses
        if (typeof toast !== 'undefined' && toast.success) {
            toast.success('Komentar berhasil diperbarui');
        } else {
            alert('Komentar berhasil diperbarui');
        }
        
        // Reload halaman untuk menampilkan komentar baru
        setTimeout(() => {
            window.location.href = route('tasks.show', props.task.id) + '?refresh=' + Date.now();
        }, 1000);
    })
    .catch(error => {
        console.error('[COMMENT] Error memperbarui komentar:', error);
        editCommentForm.value.processing = false;
        
        // Set error jika ada
        if (error.response?.data?.errors) {
            editCommentForm.value.errors = error.response.data.errors;
        }
        
        // Tampilkan notifikasi error
        let errorMessage = 'Terjadi kesalahan saat memperbarui komentar';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors?.content) {
            errorMessage = error.response.data.errors.content[0];
        }
        
        if (typeof toast !== 'undefined' && toast.error) {
            toast.error(errorMessage);
        } else {
            alert(errorMessage);
        }
    });
};

const deleteComment = (commentId) => {
    commentIdToDelete.value = commentId;
    showDeleteModal.value = true;
};

const confirmDeleteComment = () => {
    if (!commentIdToDelete.value) return;
    
    isDeleting.value = true;
    
    // Log pengiriman komentar
    console.log('[COMMENT] Menghapus komentar ID:', commentIdToDelete.value);
    
    // Dapatkan CSRF token dari meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Gunakan axios untuk hapus komentar
    axios.delete(route('tasks.comments.destroy', commentIdToDelete.value), {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('[COMMENT] Komentar berhasil dihapus:', response.data);
        
        // Tampilkan notifikasi sukses
        if (typeof toast !== 'undefined' && toast.success) {
            toast.success('Komentar berhasil dihapus');
        } else {
            alert('Komentar berhasil dihapus');
        }
        
        // Tutup modal
        showDeleteModal.value = false;
        isDeleting.value = false;
        commentIdToDelete.value = null;
        
        // Reload halaman untuk menyembunyikan komentar yang dihapus
        setTimeout(() => {
            window.location.href = route('tasks.show', props.task.id) + '?refresh=' + Date.now();
        }, 1000);
    })
    .catch(error => {
        console.error('[COMMENT] Error menghapus komentar:', error);
        isDeleting.value = false;
        
        // Tampilkan notifikasi error
        let errorMessage = 'Terjadi kesalahan saat menghapus komentar';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        
        if (typeof toast !== 'undefined' && toast.error) {
            toast.error(errorMessage);
        } else {
            alert(errorMessage);
        }
        
        // Tutup modal
        showDeleteModal.value = false;
    });
};

const confirmDeleteTask = () => {
    deletingTask.value = true;
};

const closeDeleteTaskModal = () => {
    deletingTask.value = false;
};

const deleteTask = () => {
    taskDeleteForm.delete(route('tasks.destroy', props.task.id), {
        onSuccess: () => {
            closeDeleteTaskModal();
            // Redirect ke halaman index
            router.visit(route('tasks.index'));
        }
    });
};

// Tambahkan computed property untuk debug tombol arsip
const showArchiveButton = computed(() => {
    console.log('Debug Archive Button:', {
        hasEditPermission: hasPermission('edit-task'),
        hasManagePermission: hasPermission('manage-task'),
        taskStatus: props.task.status,
        isArchived: !!props.task.archived_at
    });
    
    return (hasPermission('edit-task') || hasPermission('manage-task')) && 
           !props.task.archived_at && 
           (props.task.status === 'completed' || props.task.status === 'cancelled');
});

// Update method archiveTask
const archiveTask = () => {
    router.post(route('tasks.archive', props.task.id), {}, {
        onSuccess: () => {
            router.visit(route('tasks.index'));
        },
    });
};

</script> 