<template>
    <Head title="Redirecting" />
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-center">
                    <h2 class="text-lg font-semibold mb-2">
                        Mengalihkan ke Dashboard Baru...
                    </h2>
                    <p class="text-gray-600 mb-4">
                        Mohon tunggu sebentar, Anda akan dialihkan ke halaman dashboard baru.
                    </p>
                    <div class="flex justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';

export default {
    components: {
        Head
    },
    mounted() {
        // Alihkan ke route dashboard setelah beberapa detik
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }
};
</script> 