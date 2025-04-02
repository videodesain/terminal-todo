<template>
    <Head title="Calendar" />

    <AuthenticatedLayout :auth="auth" title="Edit Event">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-[var(--text-primary)]">
                    Edit Event
                </h2>
                <div class="flex items-center space-x-3">
                    <SecondaryButton
                        @click="() => router.get(route('calendar.index'))"
                        variant="outline"
                        class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                        <span class="ml-1.5 hidden sm:inline">Kembali</span>
                    </SecondaryButton>
                    <DangerButton
                        @click="confirmDelete"
                        :disabled="form.processing"
                        class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium"
                    >
                        <TrashIcon class="w-5 h-5" />
                        <span class="ml-1.5 hidden sm:inline">Hapus</span>
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <EventForm
                                :form="form"
                                :platforms="platforms"
                                :categories="categories"
                                :teams="teams"
                                :users="users"
                            />

                            <div class="flex items-center justify-end gap-4">
                                <Link
                                    :href="route('calendar.index')"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                >
                                    <ArrowLeftIcon class="h-5 w-5 sm:mr-2 sm:block hidden" />
                                    <span>Batal</span>
                                </Link>

                                <PrimaryButton
                                    type="submit"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:mr-2 sm:block hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin sm:mr-2 sm:block hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Event' }}</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Hapus Event
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus event ini? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        Batal
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteEvent"
                    >
                        <span v-if="form.processing">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </span>
                        {{ form.processing ? 'Menghapus...' : 'Hapus Event' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import EventForm from '@/Components/EventForm.vue';
import { ArrowLeftIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    event: {
        type: Object,
        required: true
    },
    platforms: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    teams: {
        type: Array,
        required: true
    },
    users: {
        type: Array,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
});

// Debug initial data
console.log('Initial Event Data:', props.event);

const form = useForm({
    title: props.event.title,
    description: props.event.description,
    publish_date: props.event.publish_date,
    deadline: props.event.deadline,
    status: props.event.status,
    platform_id: props.event.platform_id,
    category_id: props.event.category_id,
    team_id: props.event.team_id,
    assignees: Array.isArray(props.event.assignees) 
        ? props.event.assignees.map(assignee => assignee.id.toString())
        : []
});

// Debug form initialization
console.log('Form Data after initialization:', form.data());

const confirmingDeletion = ref(false);

const confirmDelete = () => {
    confirmingDeletion.value = true;
};

const closeModal = () => {
    confirmingDeletion.value = false;
};

const deleteEvent = () => {
    form.delete(route('calendar.destroy', props.event.id), {
        onSuccess: () => closeModal(),
    });
};

const submit = () => {
    // Debug before submit
    console.log('=== DEBUG SUBMIT START ===');
    console.log('Event ID:', props.event.id);
    console.log('Form Data before submit:', {
        title: form.title,
        description: form.description,
        publish_date: form.publish_date,
        deadline: form.deadline,
        status: form.status,
        platform_id: form.platform_id,
        category_id: form.category_id,
        team_id: form.team_id,
        assignees: form.assignees
    });
    console.log('Route:', route('calendar.update', props.event.id));
    
    // Pastikan assignees adalah array valid
    const formData = form.data();
    formData.assignees = formData.assignees.filter(id => id !== undefined && id !== null);
    
    form.put(route('calendar.update', props.event.id), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Success Response:', page);
            router.visit(route('calendar.index'));
        },
        onError: (errors) => {
            console.error('=== VALIDATION ERRORS ===');
            console.error(errors);
            console.log('Current Form State:', form.data());
            console.log('Processing State:', form.processing);
        },
        onFinish: () => {
            console.log('=== REQUEST FINISHED ===');
            console.log('Final Form State:', form.data());
        }
    });
};
</script> 