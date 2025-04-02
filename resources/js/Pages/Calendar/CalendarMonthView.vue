<template>
    <div class="calendar-container bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-sm">
        <!-- Header Kalender dengan Navigasi -->
        <div class="calendar-header bg-gray-100 dark:bg-gray-700 p-4">
            <div class="flex items-center justify-between">
                <button 
                    @click="prevMonth" 
                    class="btn-nav flex items-center justify-center h-10 w-10 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-300"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ currentMonthName }} {{ currentYear }}</h2>
                
                <button 
                    @click="nextMonth" 
                    class="btn-nav flex items-center justify-center h-10 w-10 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-gray-300"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
            
            <button 
                @click="goToday" 
                class="mt-3 w-full py-2 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-md text-white font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-800"
            >
                Hari Ini
            </button>
        </div>
        
        <!-- Hari-hari dalam Seminggu -->
        <div class="calendar-days-header grid grid-cols-7 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <div v-for="day in daysOfWeek" :key="day" class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ day }}
            </div>
        </div>
        
        <!-- Grid Kalender -->
        <div class="calendar-grid">
            <div class="grid grid-cols-7">
                <div 
                    v-for="(day, index) in calendarDays" 
                    :key="index" 
                    @click="day.isCurrentMonth && selectDay(day)"
                    :class="getDayClass(day)"
                    class="calendar-day relative p-1 border border-gray-100 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                >
                    <div class="absolute top-1 right-1 text-xs" :class="{'font-bold': isToday(day)}">
                        {{ day.day }}
                    </div>
                    
                    <!-- Indikator Event -->
                    <div 
                        v-if="hasEvents(day)" 
                        class="absolute bottom-1 left-0 right-0 flex justify-center"
                    >
                        <div 
                            v-for="i in Math.min(getEventCount(day), 3)" 
                            :key="i" 
                            class="h-1.5 w-1.5 mx-0.5 rounded-full"
                            :style="getEventDotStyle(day, i-1)"
                        ></div>
                        <div v-if="getEventCount(day) > 3" class="text-xs text-gray-500 dark:text-gray-400 ml-0.5">
                            +{{ getEventCount(day) - 3 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tampilan Day Detail untuk hari yang dipilih -->
    <div 
        v-if="selectedDay" 
        class="fixed inset-0 z-50 flex items-end justify-center bg-black bg-opacity-50"
        @click.self="closeDetail"
    >
        <div class="bg-white dark:bg-gray-800 w-full max-h-[70vh] rounded-t-xl p-4 overflow-y-auto animate-slide-up">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ formatDetailDate(selectedDay) }}</h3>
                <button @click="closeDetail" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div v-if="getDayEvents(selectedDay).length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8">
                Tidak ada event pada hari ini
            </div>
            
            <div v-else class="space-y-3">
                <div 
                    v-for="event in getDayEvents(selectedDay)" 
                    :key="event.id" 
                    @click="viewEvent(event.id)"
                    class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700 border-l-4 cursor-pointer hover:shadow-md transition-shadow"
                    :style="{ borderLeftColor: event.categoryColor || '#9333ea' }"
                >
                    <div class="font-medium text-gray-900 dark:text-white">{{ event.title }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ event.timeRange }}</div>
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span v-if="event.category" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" 
                            :style="{ 
                                backgroundColor: (event.categoryColor || '#9333ea') + '20',
                                color: event.categoryColor || '#9333ea'
                            }"
                        >
                            {{ event.category }}
                        </span>
                        <span v-if="event.platform" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                            <i v-if="event.platformIcon" :class="['mr-1 fa-brands', `fa-${event.platformIcon}`]"></i>
                            {{ event.platform }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button 
                    @click="createEvent"
                    class="w-full py-2 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-md text-white font-medium flex items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buat Event Baru
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';

export default {
    props: {
        events: {
            type: Array,
            default: () => []
        }
    },
    emits: ['create-event', 'view-event'],
    data() {
        return {
            currentDate: new Date(),
            selectedDay: null,
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
        };
    },
    computed: {
        currentYear() {
            return this.currentDate.getFullYear();
        },
        currentMonthName() {
            return new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(this.currentDate);
        },
        calendarDays() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            
            const firstDayOfMonth = new Date(year, month, 1);
            const lastDayOfMonth = new Date(year, month + 1, 0);
            
            const daysInMonth = lastDayOfMonth.getDate();
            const firstDayOfWeek = firstDayOfMonth.getDay(); // 0 = Minggu, 1 = Senin, dst
            
            // Isi array dengan hari dari bulan sebelumnya untuk mengisi slot minggu pertama
            const days = [];
            
            // Hari dari bulan sebelumnya
            const prevMonthLastDay = new Date(year, month, 0).getDate();
            for (let i = firstDayOfWeek - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                const date = new Date(year, month - 1, day);
                days.push({
                    day,
                    date: this.formatDate(date),
                    isCurrentMonth: false,
                    isPrevMonth: true
                });
            }
            
            // Hari dari bulan sekarang
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                days.push({
                    day,
                    date: this.formatDate(date),
                    isCurrentMonth: true,
                    isToday: this.isDateToday(date)
                });
            }
            
            // Hari dari bulan berikutnya
            const remainingDays = 42 - days.length; // 6 baris x 7 hari = 42 sel
            for (let day = 1; day <= remainingDays; day++) {
                const date = new Date(year, month + 1, day);
                days.push({
                    day,
                    date: this.formatDate(date),
                    isCurrentMonth: false,
                    isNextMonth: true
                });
            }
            
            return days;
        }
    },
    methods: {
        prevMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
        },
        nextMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
        },
        goToday() {
            this.currentDate = new Date();
            // Cari dan pilih hari ini
            const today = this.calendarDays.find(day => day.isToday);
            if (today) {
                this.selectDay(today);
            }
        },
        selectDay(day) {
            if (!day.isCurrentMonth) return;
            this.selectedDay = day;
        },
        closeDetail() {
            this.selectedDay = null;
        },
        isToday(day) {
            return day.isToday;
        },
        isDateToday(date) {
            const today = new Date();
            return date.getDate() === today.getDate() && 
                   date.getMonth() === today.getMonth() && 
                   date.getFullYear() === today.getFullYear();
        },
        formatDate(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        },
        formatDetailDate(day) {
            if (!day) return '';
            const date = new Date(day.date);
            return new Intl.DateTimeFormat('id-ID', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long',
                year: 'numeric'
            }).format(date);
        },
        getDayClass(day) {
            return {
                'opacity-50': !day.isCurrentMonth,
                'bg-indigo-50 dark:bg-indigo-900/20': day.isToday,
                'hover:bg-gray-100 dark:hover:bg-gray-700': day.isCurrentMonth,
                'cursor-pointer': day.isCurrentMonth,
                'cursor-default': !day.isCurrentMonth
            };
        },
        hasEvents(day) {
            return this.getEventCount(day) > 0;
        },
        getEventCount(day) {
            return this.getDayEvents(day).length;
        },
        getDayEvents(day) {
            if (!day) return [];
            return this.events.filter(event => {
                try {
                    // Handle either ISO string or already formatted date string
                    const eventDate = event.date.includes('T') 
                        ? format(parseISO(event.date), 'yyyy-MM-dd')
                        : event.date;
                    return eventDate === day.date;
                } catch (e) {
                    console.error('Error parsing date:', e);
                    return false;
                }
            });
        },
        getEventDotStyle(day, index) {
            const events = this.getDayEvents(day);
            if (events.length > index) {
                const event = events[index];
                return {
                    backgroundColor: event.categoryColor || '#9333ea'
                };
            }
            return { backgroundColor: '#9333ea' };
        },
        createEvent() {
            if (this.selectedDay) {
                this.$emit('create-event', this.selectedDay.date);
                this.closeDetail();
            }
        },
        viewEvent(eventId) {
            this.$emit('view-event', eventId);
        }
    }
};
</script>

<style scoped>
.calendar-day {
    cursor: pointer;
    transition: all 0.2s;
    aspect-ratio: 1/1;
}

.animate-slide-up {
    animation: slideUp 0.3s ease-out forwards;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
</style> 