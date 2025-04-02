<template>
    <Head :title="'Event: ' + event.title" />

    <AuthenticatedLayout :auth="auth" title="Detail Event">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Detail Event
                </h2>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('calendar.edit', event.id)"
                        class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-white text-sm font-medium transition-colors"
                    >
                        <PencilIcon class="h-5 w-5" />
                        <span class="ml-1.5 hidden sm:inline">Edit</span>
                    </Link>
                    
                    <button
                        @click="confirmDeletion"
                        class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md text-white text-sm font-medium transition-colors"
                    >
                        <TrashIcon class="h-5 w-5" />
                        <span class="ml-1.5 hidden sm:inline">Hapus</span>
                    </button>
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
                            <div 
                                class="w-4 h-4 rounded-full" 
                                :style="{ backgroundColor: event.category.color }"
                            ></div>
                            <span class="text-white font-medium">{{ event.category.name }}</span>
                        </div>
                        <div :class="getStatusBadgeClass()">
                            {{ getStatusText() }}
                        </div>
                    </div>
                    
                    <!-- Content Section -->
                    <div class="p-6">
                        <!-- Title & Description -->
                        <div class="mb-6">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ event.title }}</h1>
                            <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ event.description || 'Tidak ada deskripsi' }}</p>
                        </div>
                        
                        <!-- Info Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Platform Card -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center border border-gray-200 dark:border-gray-700">
                                <div class="mr-4 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <i :class="['text-xl fa-brands text-blue-600 dark:text-blue-400', `fa-${event.platform.icon}`]"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Platform</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ event.platform.name }}</p>
                                </div>
                            </div>
                            
                            <!-- Publish Date Card -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center border border-gray-200 dark:border-gray-700">
                                <div class="mr-4 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <CalendarIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Publish</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ formatDate(event.publish_date) }}</p>
                                </div>
                            </div>
                            
                            <!-- Deadline Card -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 flex items-center border border-gray-200 dark:border-gray-700">
                                <div class="mr-4 w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                    <ClockIcon class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Deadline</h3>
                                    <p class="text-gray-900 dark:text-white font-medium mt-0.5">{{ formatDate(event.deadline) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Assignees Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Penanggung Jawab</h3>
                            <div v-if="event.assignees && event.assignees.length > 0" class="flex flex-wrap gap-2">
                                <div 
                                    v-for="assignee in event.assignees" 
                                    :key="assignee.id"
                                    class="flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full"
                                >
                                    <div class="w-7 h-7 rounded-full bg-indigo-200 dark:bg-indigo-800 flex items-center justify-center text-xs font-semibold mr-2">
                                        {{ assignee.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm">{{ assignee.name }}</span>
                                </div>
                            </div>
                            <div v-else class="text-gray-500 dark:text-gray-400 text-sm bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center">
                                <UserIcon class="h-5 w-5 mr-2 text-gray-400" />
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
                        <!-- Comment Form -->
                        <div class="mb-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Tambah Komentar</h4>
                            <form @submit.prevent="addComment">
                                <textarea
                                    v-model="newComment" 
                                    rows="3"
                                    class="w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Tulis komentar Anda di sini..."
                                ></textarea>
                                
                                <!-- Attachment Options -->
                                <div class="flex flex-wrap items-center gap-4 mt-3">
                                    <!-- File Upload -->
                                    <div>
                                        <input 
                                            type="file" 
                                            ref="fileInput" 
                                            class="hidden" 
                                            @change="handleFileSelected"
                                        />
                                        <button
                                            type="button"
                                            @click="$refs.fileInput.click()"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <PaperClipIcon class="h-4 w-4 mr-1" />
                                            Lampirkan File
                                        </button>
                                        <div v-if="uploadedFile" class="mt-2 text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                            <DocumentIcon class="h-4 w-4 mr-1" />
                                            {{ uploadedFile.name }}
                                            <button @click="removeFile" class="ml-2 text-red-500 hover:text-red-700">
                                                <XMarkIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                
                                    <!-- Link Input -->
                                    <div>
                                        <button 
                                            type="button" 
                                            @click="showLinkInput = !showLinkInput"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <LinkIcon class="h-4 w-4 mr-1" />
                                            Tambah Link
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Link Input Form -->
                                <div v-if="showLinkInput" class="mt-3 p-3 border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800">
                                    <div class="flex flex-col space-y-2">
                                        <input 
                                            v-model="linkUrl" 
                                            type="url" 
                                            placeholder="URL (https://...)" 
                                            class="w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-200"
                                        />
                                        <input 
                                            v-model="linkTitle" 
                                            type="text" 
                                            placeholder="Judul Link (opsional)" 
                                            class="w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-200"
                                        />
                                        <div class="flex justify-end space-x-2">
                                            <button 
                                                type="button" 
                                                @click="cancelLink" 
                                                class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                                            >
                                                Batal
                                            </button>
                                            <button
                                                type="button"
                                                @click="addLink" 
                                                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                                            >
                                                Tambah Link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Selected Link Preview -->
                                <div v-if="selectedLink" class="mt-2 text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <LinkIcon class="h-4 w-4 mr-1" />
                                    {{ selectedLink.title || selectedLink.url }}
                                    <button @click="removeLink" class="ml-2 text-red-500 hover:text-red-700">
                                        <XMarkIcon class="h-4 w-4" />
                                    </button>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="mt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
                                        :disabled="isSubmitting"
                                    >
                                        <span v-if="isSubmitting">
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
                        <div v-if="event.comments && event.comments.length > 0" class="space-y-4">
                            <div
                                v-for="comment in event.comments"
                                :key="comment.id"
                                class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start">
                                        <div class="mr-3">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                                {{ comment.user.name.charAt(0).toUpperCase() }}
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ comment.user.name }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatCommentDate(comment.created_at) }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Comment Actions -->
                                    <div class="flex space-x-2" v-if="comment.user_id === auth.user.id">
                                        <button 
                                            @click="editComment(comment)" 
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="confirmDeleteComment(comment.id)" 
                                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Comment Content -->
                                <div v-if="editingCommentId !== comment.id" class="mt-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ comment.content }}</p>
                                    
                                    <!-- Attachments -->
                                    <template v-if="comment.attachments && comment.attachments.length > 0">
                                        <div class="mt-3 space-y-2">
                                            <div v-for="attachment in comment.attachments" :key="attachment.id" class="flex items-center p-2 rounded bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                                <div v-if="attachment.type === 'file'" class="flex items-center">
                                                    <DocumentIcon class="w-5 h-5 mr-2 text-blue-500" />
                                                    <a :href="attachment.url" target="_blank" class="text-sm text-blue-500 hover:underline">
                                                        {{ attachment.filename || 'File' }}
                                                        <span v-if="attachment.file_size" class="text-xs text-gray-500">
                                                            ({{ formatFileSize(attachment.file_size) }})
                                                        </span>
                                                    </a>
                                                </div>
                                                <div v-else-if="attachment.type === 'link'" class="flex items-center">
                                                    <LinkIcon class="w-5 h-5 mr-2 text-blue-500" />
                                                    <a :href="attachment.url" target="_blank" class="text-sm text-blue-500 hover:underline">
                                                        {{ attachment.title || attachment.url }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Edit Comment Form -->
                                <div v-else class="mt-3">
                                    <form @submit.prevent="updateComment">
                                        <textarea 
                                            v-model="editedComment" 
                                            rows="3" 
                                            class="w-full border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-gray-200"
                                        ></textarea>
                                        <div class="mt-3 flex justify-end space-x-2">
                                            <button 
                                                type="button" 
                                                @click="cancelEdit" 
                                                class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                                            >
                                                Batal
                                            </button>
                                            <button 
                                                type="submit" 
                                                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                                                :disabled="isSubmitting"
                                            >
                                                Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- No Comments Message -->
                        <div v-else class="py-10 text-center">
                            <ChatBubbleLeftRightIcon class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-600" />
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Belum ada komentar pada event ini.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500">Jadilah yang pertama memberikan komentar!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deletion Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Hapus Event
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus event ini? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Batal</SecondaryButton>
                    <DangerButton @click="deleteEvent" :class="{ 'opacity-25': isProcessing }" :disabled="isProcessing">
                        Hapus Event
                    </DangerButton>
                </div>
            </div>
        </Modal>
        
        <!-- Comment Deletion Confirmation Modal -->
        <Modal :show="showDeleteCommentModal" @close="showDeleteCommentModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Hapus Komentar
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus komentar ini? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteCommentModal = false">Batal</SecondaryButton>
                    <DangerButton @click="deleteComment" :class="{ 'opacity-25': isSubmitting }" :disabled="isSubmitting">
                        Hapus Komentar
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { 
    PencilIcon, 
    TrashIcon,
    CalendarIcon,
    ClockIcon,
    PaperClipIcon,
    LinkIcon,
    DocumentIcon,
    XMarkIcon,
    ChatBubbleLeftRightIcon,
    ArrowTopRightOnSquareIcon,
    ArrowDownTrayIcon,
    UserIcon
} from '@heroicons/vue/24/outline';
import { format, parseISO, formatDistance } from 'date-fns';
import { id } from 'date-fns/locale';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    event: Object
});

const showDeleteModal = ref(false);
const isProcessing = ref(false);

// Comments and attachments
const newComment = ref('');
const editingCommentId = ref(null);
const editedComment = ref('');
const isSubmitting = ref(false);
const commentIdToDelete = ref(null);
const showDeleteCommentModal = ref(false);

// File upload
const fileInput = ref(null);
const uploadedFile = ref(null);

// Link attachment
const showLinkInput = ref(false);
const linkUrl = ref('');
const linkTitle = ref('');
const selectedLink = ref(null);

const formatDate = (date) => {
    if (!date) return '-';
    try {
        return format(parseISO(date), 'dd MMMM yyyy, HH:mm', { locale: id });
    } catch (error) {
        return date;
    }
};

const getStatusText = () => {
    const statusMap = {
        'draft': 'Draft',
        'planned': 'Direncanakan',
        'in_progress': 'Sedang Dibuat',
        'reviewing': 'Dalam Review',
        'completed': 'Selesai',
        'published': 'Dipublikasikan',
        'cancelled': 'Dibatalkan'
    };
    return statusMap[props.event.status] || props.event.status;
};

const getStatusBadgeClass = () => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-medium';
    
    const statusClasses = {
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'planned': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
        'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'reviewing': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'completed': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'published': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    };
    
    return `${baseClasses} ${statusClasses[props.event.status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'}`;
};

const confirmDeletion = () => {
    showDeleteModal.value = true;
};

const closeModal = () => {
    showDeleteModal.value = false;
};

const deleteEvent = () => {
    isProcessing.value = true;
    router.delete(route('calendar.destroy', props.event.id), {
        onSuccess: () => {
            // Redirect will happen automatically by Inertia
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

// Format comment date
const formatCommentDate = (date) => {
    if (!date) return '-';
    try {
        return formatDistance(parseISO(date), new Date(), { addSuffix: true, locale: id });
    } catch (error) {
        return date;
    }
};

// Handle file selection
const handleFileSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadedFile.value = file;
    }
};

// Remove selected file
const removeFile = () => {
    uploadedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = ''; // Clear file input
    }
};

// Add link to comment
const addLink = () => {
    if (linkUrl.value) {
        selectedLink.value = {
            url: linkUrl.value,
            title: linkTitle.value || linkUrl.value
        };
        showLinkInput.value = false;
        linkUrl.value = '';
        linkTitle.value = '';
    }
};

// Cancel link addition
const cancelLink = () => {
    showLinkInput.value = false;
    linkUrl.value = '';
    linkTitle.value = '';
};

// Remove selected link
const removeLink = () => {
    selectedLink.value = null;
};

// Add comment
const addComment = async () => {
    if (!newComment.value && !uploadedFile.value && !selectedLink.value) return;
    
    isSubmitting.value = true;
    
    try {
        const formData = new FormData();
        formData.append('content', newComment.value);
        formData.append('calendar_id', props.event.id);
        
        // Add file if selected
        if (uploadedFile.value) {
            formData.append('attachment', uploadedFile.value);
            formData.append('attachment_type', 'file');
            formData.append('attachment_filename', uploadedFile.value.name);
            formData.append('attachment_file_size', uploadedFile.value.size);
        }
        
        // Add link if selected
        if (selectedLink.value) {
            formData.append('link_url', selectedLink.value.url);
            formData.append('link_title', selectedLink.value.title);
            formData.append('attachment_type', 'link');
        }
        
        console.log('Submitting comment to:', route('calendar.comments.store', props.event.id));
        console.log('Form data:', Object.fromEntries(formData));
        
        // Send comment to server
        const response = await axios.post(route('calendar.comments.store', props.event.id), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        console.log('Comment response:', response.data);
        
        if (response.data.success) {
            // Clear form
            newComment.value = '';
            uploadedFile.value = null;
            selectedLink.value = null;
            
            // Refresh page to show new comment
            window.location.href = route('calendar.show', props.event.id) + '?refresh=' + Date.now();
        } else {
            throw new Error(response.data.message || 'Gagal mengirim komentar');
        }
    } catch (error) {
        console.error('Error posting comment:', error);
        alert('Gagal mengirim komentar. Silakan coba lagi. Detail: ' + (error.response?.data?.message || error.message));
    } finally {
        isSubmitting.value = false;
    }
};

// Edit comment
const editComment = (comment) => {
    editingCommentId.value = comment.id;
    editedComment.value = comment.content;
};

// Cancel edit
const cancelEdit = () => {
    editingCommentId.value = null;
    editedComment.value = '';
};

// Update comment
const updateComment = async () => {
    if (!editedComment.value) return;
    
    isSubmitting.value = true;
    
    try {
        console.log('Updating comment:', editingCommentId.value);
        
        const response = await axios.put(route('calendar.comments.update', editingCommentId.value), {
            content: editedComment.value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        });
        
        console.log('Update response:', response.data);
        
        if (response.data.success) {
            // Reset edit state
            editingCommentId.value = null;
            editedComment.value = '';
            
            // Refresh page to show updated comment
            window.location.href = route('calendar.show', props.event.id) + '?refresh=' + Date.now();
        } else {
            throw new Error(response.data.message || 'Gagal memperbarui komentar');
        }
    } catch (error) {
        console.error('Error updating comment:', error);
        alert('Gagal memperbarui komentar. Silakan coba lagi. Detail: ' + (error.response?.data?.message || error.message));
    } finally {
        isSubmitting.value = false;
    }
};

// Confirm comment deletion
const confirmDeleteComment = (commentId) => {
    commentIdToDelete.value = commentId;
    showDeleteCommentModal.value = true;
};

// Delete comment
const deleteComment = async () => {
    isSubmitting.value = true;
    
    try {
        console.log('Deleting comment:', commentIdToDelete.value);
        
        const response = await axios.delete(route('calendar.comments.destroy', commentIdToDelete.value), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: {
                event_id: props.event.id
            }
        });
        
        console.log('Delete response:', response.data);
        
        if (response.data.success) {
            // Reset state
            commentIdToDelete.value = null;
            showDeleteCommentModal.value = false;
            
            // Refresh page to remove deleted comment
            window.location.href = route('calendar.show', props.event.id) + '?refresh=' + Date.now();
        } else {
            throw new Error(response.data.message || 'Gagal menghapus komentar');
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
        alert('Gagal menghapus komentar. Silakan coba lagi. Detail: ' + (error.response?.data?.message || error.message));
    } finally {
        isSubmitting.value = false;
        showDeleteCommentModal.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script> 