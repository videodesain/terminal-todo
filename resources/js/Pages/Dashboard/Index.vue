<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout :auth="auth" title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
                <div class="flex items-center space-x-2">
                    <button 
                        @click="refreshData" 
                        class="inline-flex items-center justify-center p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                        :class="{ 'animate-spin': isRefreshing }"
                        title="Muat ulang data"
                        :disabled="isRefreshing"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="isDataFresh ? 'text-gray-600 dark:text-gray-400' : 'text-amber-600 dark:text-amber-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Error Alert -->
                <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 p-4 mb-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400 dark:text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-400">
                                {{ error }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Data Sync Warning -->
                <div v-if="!isDataFresh && !error" class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 dark:border-amber-600 p-3 mb-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400 dark:text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex justify-between items-center w-full">
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                Data dashboard mungkin tidak terupdate. Data terakhir dari {{ formatRefreshTime }}.
                            </p>
                            <button @click="refreshData" class="ml-4 text-sm font-medium text-amber-700 dark:text-amber-400 underline" :disabled="isRefreshing">
                                {{ isRefreshing ? 'Memperbarui...' : 'Perbarui sekarang' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Task Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md border-t-4 border-indigo-500 dark:border-indigo-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total Tugas</h3>
                                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ stats.tasks.total }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Semua tugas dalam sistem</p>
                            </div>
                            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-3">
                                <div class="flex rounded-full h-2.5">
                                    <div class="bg-yellow-400 h-2.5 rounded-l-full" :style="getProgressBarStyle(stats.tasks.draft, stats.tasks.total)"></div>
                                    <div class="bg-blue-500 h-2.5" :style="getProgressBarStyle(stats.tasks.in_progress, stats.tasks.total)"></div>
                                    <div class="bg-green-500 h-2.5" :style="getProgressBarStyle(stats.tasks.completed, stats.tasks.total)"></div>
                                    <div class="bg-red-500 h-2.5 rounded-r-full" :style="getProgressBarStyle(stats.tasks.cancelled, stats.tasks.total)"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm p-2 rounded bg-gray-50 dark:bg-gray-900/20 flex flex-col">
                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                        <span class="w-2 h-2 rounded-full bg-yellow-400 mr-1.5"></span>
                                        Draft
                                    </span>
                                    <span class="font-medium dark:text-gray-300 text-lg">{{ stats.tasks.draft }}</span>
                                </div>
                                <div class="text-sm p-2 rounded bg-gray-50 dark:bg-gray-900/20 flex flex-col">
                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-1.5"></span>
                                        Dalam Proses
                                    </span>
                                    <span class="font-medium dark:text-gray-300 text-lg">{{ stats.tasks.in_progress }}</span>
                                </div>
                                <div class="text-sm p-2 rounded bg-gray-50 dark:bg-gray-900/20 flex flex-col">
                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-1.5"></span>
                                        Selesai
                                    </span>
                                    <span class="font-medium dark:text-gray-300 text-lg">{{ stats.tasks.completed }}</span>
                                </div>
                                <div class="text-sm p-2 rounded bg-gray-50 dark:bg-gray-900/20 flex flex-col">
                                    <span class="flex items-center text-gray-500 dark:text-gray-400">
                                        <span class="w-2 h-2 rounded-full bg-red-500 mr-1.5"></span>
                                        Dibatalkan
                                    </span>
                                    <span class="font-medium dark:text-gray-300 text-lg">{{ stats.tasks.cancelled }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md border-t-4 border-emerald-500 dark:border-emerald-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Event Kalender</h3>
                                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.calendar.total }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total event terjadwal</p>
                            </div>
                            <div class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center p-2 mb-3 rounded bg-emerald-50 dark:bg-emerald-900/20">
                                <div class="p-2 mr-3 rounded-full bg-emerald-100 dark:bg-emerald-800/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="block text-emerald-800 dark:text-emerald-300 font-medium">Event Mendatang</span>
                                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ stats.calendar.upcoming }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div v-for="month in stats.calendar.monthly" :key="month.name" 
                                    class="text-sm flex justify-between items-center p-2 rounded"
                                    :class="{'bg-emerald-100 dark:bg-emerald-900/30': month.status === 'current', 'bg-gray-50 dark:bg-gray-900/20': month.status !== 'current'}">
                                    <span class="text-gray-700 dark:text-gray-300" :class="{'font-semibold': month.status === 'current'}">{{ month.name }}</span>
                                    <div class="flex items-center">
                                        <div class="w-16 h-2 bg-gray-200 dark:bg-gray-700 rounded-full mr-2">
                                            <div class="h-2 bg-emerald-500 rounded-full" :style="getMonthProgressStyle(month.count)"></div>
                                        </div>
                                        <span class="font-medium" :class="{'text-emerald-600 dark:text-emerald-400': month.status === 'current', 'dark:text-gray-300': month.status !== 'current'}">{{ month.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Tasks Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md border-t-4 border-amber-500 dark:border-amber-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tugas Saya</h3>
                                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ myTasks.total }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total tugas terkait dengan Anda</p>
                            </div>
                            <div class="p-3 rounded-full bg-amber-100 dark:bg-amber-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex space-x-2 mb-3">
                                <div class="w-1/2 h-24 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="text-amber-800 dark:text-amber-300 font-medium text-sm">Dibuat Oleh Saya</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ myTasks.created }}</span>
                                </div>
                                <div class="w-1/2 h-24 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 flex flex-col justify-between">
                                    <div class="flex justify-between items-center">
                                        <span class="text-amber-800 dark:text-amber-300 font-medium text-sm">Ditugaskan Ke Saya</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ myTasks.assigned }}</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <Link 
                                    :href="route('tasks.index')" 
                                    class="w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 rounded-md text-amber-700 dark:text-amber-300 font-medium flex items-center justify-center text-sm transition-colors"
                                >
                                    Lihat semua tugas saya
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Teams Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md border-t-4 border-blue-500 dark:border-blue-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tim</h3>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.teams.total }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total tim dalam sistem</p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 mb-3">
                                <div class="text-sm mb-2">
                                    <span class="block text-blue-800 dark:text-blue-300 font-medium">Tim Saya</span>
                                </div>
                                <div v-if="stats.teams.userTeams.length === 0" class="text-sm text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Anda belum tergabung dalam tim manapun
                                </div>
                                <div v-for="team in stats.teams.userTeams" :key="team.id" class="text-sm flex items-center py-1 px-2 bg-white dark:bg-gray-700 rounded mb-1 last:mb-0">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                                    <span class="font-medium dark:text-gray-300">{{ team.name }}</span>
                                </div>
                            </div>
                            <div class="mt-2" v-if="hasPermission('view-team')">
                                <Link 
                                    :href="route('teams.index')" 
                                    class="w-full py-2 px-3 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-md text-blue-700 dark:text-blue-300 font-medium flex items-center justify-center text-sm transition-colors"
                                >
                                    Kelola tim
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Upcoming Events -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recent Tasks -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <ClipboardDocumentListIcon class="h-5 w-5 mr-2 text-indigo-500 dark:text-indigo-400" />
                                Tugas Terbaru
                            </h3>
                            <button @click="refreshRecentData" 
                                    :disabled="loadingRecentTasks"
                                    class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-300">
                                <ArrowPathIcon v-if="loadingRecentTasks" class="h-4 w-4 mr-1 animate-spin" />
                                <ArrowPathIcon v-else class="h-4 w-4 mr-1" />
                                <span>Refresh</span>
                            </button>
                        </div>
                        
                        <div v-if="recentTasks.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-8 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 mt-4">
                            <ClipboardDocumentListIcon class="h-10 w-10 mb-2 text-gray-400 dark:text-gray-500" />
                            <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Tidak ada tugas terbaru</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Buat tugas baru untuk mulai mengatur pekerjaan Anda</p>
                            <Link :href="route('tasks.create')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                Buat Tugas Baru
                            </Link>
                        </div>
                        <div v-else class="mt-3">
                            <!-- Task list -->
                            <div v-if="recentTasks.length > 0" class="mt-4 space-y-3">
                                <div v-for="task in recentTasks" :key="task.id" 
                                     class="p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-lg shadow-sm hover:shadow transition-all duration-300 hover:border-indigo-300 dark:hover:border-indigo-600 transform hover:-translate-y-0.5">
                                    <div class="flex justify-between items-center">
                                        <Link :href="route('tasks.edit', task.id)" class="text-gray-900 dark:text-gray-100 font-medium hover:text-indigo-500 dark:hover:text-indigo-400">
                                            {{ task.title }}
                                        </Link>
                                        <span class="px-2 py-1 text-xs rounded-full" 
                                            :class="{
                                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200': task.status === 'draft',
                                                'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200': task.status === 'in_progress',
                                                'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200': task.status === 'completed',
                                                'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200': task.status === 'cancelled'
                                            }">
                                            {{ task.status === 'draft' ? 'Draft' : 
                                                task.status === 'in_progress' ? 'In Progress' : 
                                                task.status === 'completed' ? 'Completed' : 'Cancelled' 
                                            }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        <span>{{ formatTime(task.due_date) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4" v-if="recentTasks.length > 0">
                            <Link 
                                :href="route('tasks.index')" 
                                class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium flex items-center"
                            >
                                Lihat semua tugas
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <CalendarIcon class="h-5 w-5 mr-2 text-emerald-500 dark:text-emerald-400" />
                                Event Kalender Mendatang
                            </h3>
                            <button @click="refreshEventData" 
                                    :disabled="loadingEvents"
                                    class="inline-flex items-center rounded-md border border-gray-200 dark:border-gray-700 px-2.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-300">
                                <ArrowPathIcon v-if="loadingEvents" class="h-4 w-4 mr-1 animate-spin" />
                                <ArrowPathIcon v-else class="h-4 w-4 mr-1" />
                                <span>Refresh</span>
                            </button>
                        </div>
                        
                        <div v-if="upcomingEvents.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-8 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 mt-4">
                            <CalendarIcon class="h-10 w-10 mb-2 text-gray-400 dark:text-gray-500" />
                            <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Tidak ada event mendatang</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Buat event baru untuk mengatur jadwal kalender Anda</p>
                            <Link :href="route('calendar.create')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
                                Buat Event Baru
                            </Link>
                        </div>
                        <div v-else class="mt-3">
                            <!-- Events list -->
                            <div v-if="upcomingEvents.length > 0" class="mt-4 space-y-3">
                                <div v-for="event in upcomingEvents" :key="event.id" 
                                     class="p-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-lg shadow-sm hover:shadow transition-all duration-300 hover:border-emerald-300 dark:hover:border-emerald-600 transform hover:-translate-y-0.5">
                                    <div class="flex justify-between items-center">
                                        <Link :href="route('calendar.show', event.id)" class="text-gray-900 dark:text-gray-100 font-medium hover:text-emerald-500 dark:hover:text-emerald-400">
                                            {{ event.title }}
                                        </Link>
                                        <span v-if="event.category" class="px-2 py-1 text-xs rounded-full" :style="{ 
                                            backgroundColor: event.category.color + '20',
                                            color: event.category.color
                                        }">
                                            {{ event.category.name }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-col space-y-1">
                                        <div class="flex items-center text-xs">
                                            <span class="flex items-center mr-1 text-emerald-600 dark:text-emerald-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Publish:
                                            </span>
                                            <span class="text-gray-600 dark:text-gray-400">{{ formatDateTime(event.publish_date) }}</span>
                                        </div>
                                        <div class="flex items-center text-xs">
                                            <span class="flex items-center mr-1 text-amber-600 dark:text-amber-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Deadline:
                                            </span>
                                            <span class="text-gray-600 dark:text-gray-400">{{ formatDateTime(event.deadline) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-1">
                                            <div class="flex items-center text-xs">
                                                <span v-if="event.platform && event.platform.icon" class="mr-1">
                                                    <i :class="['fa-brands text-sm', `fa-${event.platform.icon}`]"></i>
                                                </span>
                                                <span class="text-gray-600 dark:text-gray-400">{{ event.platform ? event.platform.name : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4" v-if="upcomingEvents.length > 0">
                            <Link 
                                :href="route('calendar.index')" 
                                class="text-sm text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 font-medium flex items-center"
                            >
                                Lihat semua event
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { format, formatDistance } from 'date-fns';
import { id } from 'date-fns/locale';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { ClipboardDocumentListIcon, CalendarIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

export default {
    components: {
        Head,
        Link,
        AuthenticatedLayout,
        ClipboardDocumentListIcon,
        CalendarIcon,
        ArrowPathIcon
    },
    props: {
        auth: Object,
        stats: Object,
        recentTasks: Array,
        upcomingEvents: Array,
        myTasks: Object,
        lastUpdated: String,
        error: {
            type: String,
            default: null
        }
    },
    setup(props) {
        const isRefreshing = ref(false);
        const isRefreshingRecent = ref(false);
        const isRefreshingEvents = ref(false);
        const lastRefreshTime = ref(new Date());
        const error = ref(props.error || null);
        let refreshInterval = null;
        const AUTO_REFRESH_INTERVAL = 30000; // 30 seconds untuk lebih realtime
        const DATA_FRESHNESS_THRESHOLD = 5 * 60 * 1000; // 5 minutes
        
        // Dynamic ref for recent tasks and upcoming events
        const recentTasks = ref(props.recentTasks || []);
        const upcomingEvents = ref(props.upcomingEvents || []);
        
        // Add missing reactive variables
        const loadingRecentTasks = ref(false);
        const loadingEvents = ref(false);
        
        // Check if data is fresh
        const isDataFresh = computed(() => {
            const now = new Date();
            return (now - lastRefreshTime.value) < DATA_FRESHNESS_THRESHOLD;
        });

        // Validate stats to ensure they're not corrupted
        const validateData = () => {
            // Check if stats object is valid
            if (!props.stats || typeof props.stats !== 'object') {
                error.value = 'Data statistik tidak valid. Silakan refresh halaman.';
                return false;
            }
            
            // Check if tasks stats exist
            if (!props.stats.tasks || !props.myTasks) {
                error.value = 'Data tugas tidak tersedia. Silakan refresh halaman.';
                return false;
            }
            
            // Check if calendar stats exist
            if (!props.stats.calendar) {
                error.value = 'Data kalender tidak tersedia. Silakan refresh halaman.';
                return false;
            }
            
            // Clear error if data is valid
            error.value = null;
            return true;
        };

        const refreshData = () => {
            if (isRefreshing.value) return;
            
            isRefreshing.value = true;
            
            // Use Inertia's visit function to reload the data
            router.reload({
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    // Update local data from response
                    if (page.props.recentTasks) {
                        recentTasks.value = page.props.recentTasks;
                    }
                    
                    if (page.props.upcomingEvents) {
                        upcomingEvents.value = page.props.upcomingEvents;
                    }
                    
                    // Validate the new data
                    validateData();
                },
                onFinish: () => {
                    isRefreshing.value = false;
                    lastRefreshTime.value = new Date();
                },
                onError: (errors) => {
                    console.error('Error refreshing dashboard data:', errors);
                    error.value = 'Gagal memperbarui data. Silakan coba lagi.';
                    isRefreshing.value = false;
                }
            });
        };

        // Function to refresh recent tasks data
        const refreshRecentData = () => {
            if (loadingRecentTasks.value) return;
            
            loadingRecentTasks.value = true;
            isRefreshingRecent.value = true;
            
            // Use Inertia router to load only recent tasks data
            router.visit(route('dashboard'), {
                only: ['recentTasks'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    if (page.props.recentTasks) {
                        recentTasks.value = page.props.recentTasks;
                    }
                    lastRefreshTime.value = new Date();
                },
                onFinish: () => {
                    loadingRecentTasks.value = false;
                    isRefreshingRecent.value = false;
                },
                onError: () => {
                    error.value = 'Failed to refresh recent tasks data.';
                }
            });
        };
        
        // Function to refresh upcoming events data
        const refreshEventData = () => {
            if (loadingEvents.value) return;
            
            loadingEvents.value = true;
            isRefreshingEvents.value = true;
            
            // Use Inertia router to load only upcoming events data
            router.visit(route('dashboard'), {
                only: ['upcomingEvents'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    if (page.props.upcomingEvents) {
                        upcomingEvents.value = page.props.upcomingEvents;
                    }
                    lastRefreshTime.value = new Date();
                },
                onFinish: () => {
                    loadingEvents.value = false;
                    isRefreshingEvents.value = false;
                },
                onError: () => {
                    error.value = 'Failed to refresh upcoming events data.';
                }
            });
        };

        const startAutoRefresh = () => {
            // Clear any existing intervals
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            
            // Set a new interval
            refreshInterval = setInterval(() => {
                refreshData();
            }, AUTO_REFRESH_INTERVAL);
        };

        const stopAutoRefresh = () => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        };

        // Listen for visibility changes to pause/resume auto-refresh when tab is inactive
        const handleVisibilityChange = () => {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
                // Refresh immediately when tab becomes visible again if it's been a while
                const now = new Date();
                if ((now - lastRefreshTime.value) > AUTO_REFRESH_INTERVAL) {
                    refreshData();
                }
            }
        };

        onMounted(() => {
            // Set initial values
            recentTasks.value = props.recentTasks || [];
            upcomingEvents.value = props.upcomingEvents || [];
            
            // Set initial refresh time
            lastRefreshTime.value = new Date();
            
            // Validate initial data
            validateData();
            
            // Start auto-refresh
            startAutoRefresh();
            
            // Add visibility change listener
            document.addEventListener('visibilitychange', handleVisibilityChange);
        });

        onBeforeUnmount(() => {
            // Clean up
            stopAutoRefresh();
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        });

        const formatRefreshTime = computed(() => {
            return formatDistance(lastRefreshTime.value, new Date(), { 
                addSuffix: true, 
                locale: id 
            });
        });
        
        return {
            isRefreshing,
            isRefreshingRecent,
            isRefreshingEvents,
            refreshData,
            refreshRecentData,
            refreshEventData,
            lastRefreshTime,
            formatRefreshTime,
            isDataFresh,
            error,
            recentTasks,
            upcomingEvents,
            loadingRecentTasks,
            loadingEvents,
            ClipboardDocumentListIcon,
            CalendarIcon,
            ArrowPathIcon,
            formatTime: (dateString) => {
                if (!dateString) return 'Tidak ada tanggal';
                try {
                    const date = new Date(dateString);
                    return format(date, 'd MMM yyyy', { locale: id });
                } catch (e) {
                    return dateString;
                }
            },
            formatEventDate: (dateString) => {
                if (!dateString) return 'Jadwal belum ditentukan';
                try {
                    const date = new Date(dateString);
                    return format(date, 'd MMM', { locale: id });
                } catch (e) {
                    return 'Format tanggal tidak valid';
                }
            },
            formatDateTime: (dateString) => {
                if (!dateString) return 'Belum ditentukan';
                try {
                    const date = new Date(dateString);
                    return format(date, 'd MMM yyyy • HH:mm', { locale: id });
                } catch (e) {
                    return 'Belum ditentukan';
                }
            }
        };
    },
    computed: {
        hasPermission() {
            return (permission) => {
                return this.$page.props.auth && 
                      this.$page.props.auth.permissions && 
                      this.$page.props.auth.permissions.includes(permission);
            }
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                const date = new Date(dateString);
                return formatDistance(date, new Date(), { addSuffix: true, locale: id });
            } catch (e) {
                return dateString;
            }
        },
        formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'N/A';
            try {
                const date = new Date(dateTimeString);
                return format(date, 'dd MMM yyyy HH:mm', { locale: id });
            } catch (e) {
                return dateTimeString;
            }
        },
        getProgressBarStyle(value, total) {
            // Safeguard against invalid values
            if (!value || !total || total <= 0) {
                return { width: '0%' };
            }
            
            // Calculate percentage (capped at 100%)
            const percentage = Math.min(Math.round((value / total) * 100), 100);
            return { width: `${percentage}%` };
        },
        getMonthProgressStyle(count) {
            // Calculate a reasonable percentage for the progress bar
            // Max out at 10 events = 100%
            const maxEvents = 10;
            const percentage = Math.min(Math.round((count / maxEvents) * 100), 100);
            return { width: `${percentage}%` };
        }
    }
};
</script> 