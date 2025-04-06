<template>
    <Head title="Edit Akun" />

    <AuthenticatedLayout :auth="auth" title="Edit Akun">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Akun
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg mb-4" v-if="form.recentlySuccessful">
                            <p class="text-blue-600 dark:text-blue-300 text-sm">
                                Data berhasil disimpan.
                            </p>
                        </div>

                        <div v-if="formError" class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg mb-4">
                            <p class="text-red-600 dark:text-red-300 text-sm">
                                {{ formError }}
                            </p>
                        </div>

                        <div>
                            <InputLabel for="platform_id" value="Platform" />
                            <div class="mt-1 relative">
                                <div v-if="form.platform_id" class="absolute left-3 top-2">
                                    <i :class="[getPlatformIcon(getPlatformNameById(form.platform_id)?.toLowerCase()), 'text-xl']"></i>
                                </div>
                                <select
                                    id="platform_id"
                                    v-model="form.platform_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    :class="{ 'pl-10': form.platform_id }"
                                    required
                                >
                                    <option value="">Pilih Platform</option>
                                    <option v-for="platform in platforms" :key="platform.id" :value="platform.id">
                                        {{ platform.name }}
                                    </option>
                                </select>
                            </div>
                            <InputError :message="form.errors.platform_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Nama Akun/Halaman" />
                            <TextInput
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="username" value="Username" />
                            <TextInput
                                id="username"
                                type="text"
                                v-model="form.username"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.username" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="profile_url" value="URL Profil" />
                            <TextInput
                                id="profile_url"
                                type="url"
                                v-model="form.profile_url"
                                class="mt-1 block w-full"
                                required
                                placeholder="https://instagram.com/username"
                                autocomplete="off"
                                @input="validateUrl"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Format: https://example.com/username
                            </p>
                            <InputError :message="form.errors.profile_url" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Deskripsi (Opsional)" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                            >
                            <InputLabel for="is_active" value="Akun Aktif" class="ml-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('social-accounts.index')"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                            >
                                Batal
                            </Link>

                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg transition-colors duration-200"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { ref, computed, onMounted } from 'vue'

// Variabel untuk menampung pesan error
const formError = ref('');

const props = defineProps({
    account: {
        type: Object,
        required: true
    },
    platforms: {
        type: Array,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
})

// Inisialisasi form setelah props tersedia
const form = useForm({
    platform_id: props.account?.platform_id || '',
    name: props.account?.name || '',
    username: props.account?.username || '',
    profile_url: props.account?.profile_url || '',
    description: props.account?.description || '',
    is_active: props.account?.is_active === undefined ? false : props.account.is_active
});

// Function untuk memvalidasi dan memperbaiki URL
const validateUrl = (event) => {
    console.log('URL changed:', event.target.value);
    const url = event.target.value;
    
    if (url && !url.match(/^https?:\/\/.+/)) {
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            console.log('Adding https:// prefix to URL');
            form.profile_url = 'https://' + url;
        }
    }
};

// Fungsi untuk mendapatkan nama platform dari ID
const getPlatformNameById = (id) => {
    if (!id) return '';
    const platform = props.platforms.find(p => p.id === id);
    return platform ? platform.name : '';
}

// Fungsi untuk mendapatkan ikon platform
const getPlatformIcon = (platform) => {
    const icons = {
        instagram: 'fa-brands fa-instagram',
        facebook: 'fa-brands fa-facebook-f',
        twitter: 'fa-brands fa-twitter',
        tiktok: 'fa-brands fa-tiktok',
        youtube: 'fa-brands fa-youtube',
        linkedin: 'fa-brands fa-linkedin-in',
        pinterest: 'fa-brands fa-pinterest-p',
        website: 'fa-brands fa-chrome',
        threads: 'fa-brands fa-threads',
        // Tambahkan platform lain sesuai kebutuhan
        default: 'fa-brands fa-chrome' // Icon default jika platform tidak ditemukan
    }
    return icons[platform] || icons.default
}

// Fungsi untuk mendapatkan class warna platform
const getPlatformClass = (platform) => {
    const classes = {
        instagram: 'bg-gradient-to-r from-purple-500 to-pink-500 text-white',
        facebook: 'bg-blue-600 text-white',
        twitter: 'bg-blue-400 text-white',
        tiktok: 'bg-black text-white',
        youtube: 'bg-red-600 text-white',
        linkedin: 'bg-blue-700 text-white',
        pinterest: 'bg-red-700 text-white',
        website: 'bg-blue-500 text-white',
        threads: 'bg-black text-white',
        // Tambahkan platform lain sesuai kebutuhan
        default: 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
    }
    return classes[platform] || classes.default
}

// Dideklarasikan setelah semua fungsi utility
onMounted(() => {
    // Debug informasi akun
    console.log('Account data:', props.account);
    console.log('Account profile_url:', props.account.profile_url);
    console.log('Platforms:', props.platforms);
    
    // Cek apakah form sudah terinisialisasi dengan benar
    if (!form) {
        formError.value = 'Terjadi kesalahan saat memuat data. Silakan refresh halaman.';
        console.error('Form not initialized properly');
        return;
    }
    
    // Cek apakah id account valid
    if (!props.account || !props.account.id) {
        formError.value = 'ID akun tidak valid. Silakan kembali ke halaman daftar akun.';
        console.error('Invalid account ID:', props.account);
        return;
    }
    
    // Memastikan semua field memiliki nilai yang benar
    if (!form.profile_url && props.account.profile_url) {
        form.profile_url = props.account.profile_url;
    }
    
    // Memastikan URL dimulai dengan http:// atau https://
    if (form.profile_url && !form.profile_url.match(/^https?:\/\//)) {
        if (!form.profile_url.startsWith('http://') && !form.profile_url.startsWith('https://')) {
            form.profile_url = 'https://' + form.profile_url;
        }
    }
    
    // Log form data setelah inisialisasi dan perbaikan
    console.log('Form data after initialization:', form.data());
});

const submit = () => {
    // Debugging
    console.log('Form data being submitted:', form.data());
    console.log('Account ID:', props.account.id);
    
    // Validasi data sebelum submit
    if (!form.platform_id) {
        form.setError('platform_id', 'Platform harus dipilih');
        return;
    }
    
    if (!form.name) {
        form.setError('name', 'Nama akun harus diisi');
        return;
    }
    
    if (!form.username) {
        form.setError('username', 'Username harus diisi');
        return;
    }
    
    if (!form.profile_url) {
        form.setError('profile_url', 'URL profil harus diisi');
        return;
    }

    // Validasi format URL
    if (!form.profile_url.match(/^https?:\/\/.+/)) {
        if (!form.profile_url.startsWith('http://') && !form.profile_url.startsWith('https://')) {
            form.profile_url = 'https://' + form.profile_url;
        } else {
            form.setError('profile_url', 'Format URL tidak valid. Gunakan format: https://contoh.com');
            return;
        }
    }
    
    form.clearErrors();
    
    // Log additional debugging info
    console.log('Route information:', {
        route: 'social-accounts.update',
        accountId: props.account.id,
        url: route('social-accounts.update', props.account.id)
    });
    
    form.put(route('social-accounts.update', props.account.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            console.log('Form submitted successfully!', page);
            // Force reload halaman setelah sukses update
            window.location.href = route('social-accounts.index');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            formError.value = 'Terjadi kesalahan saat menyimpan data. Silakan periksa form dan coba lagi.';
            // Tambahkan scrolling ke elemen pertama dengan error
            const firstErrorElement = document.querySelector('.text-red-600');
            if (firstErrorElement) {
                firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        onFinish: () => {
            console.log('Form submission finished');
        }
    });
}
</script> 