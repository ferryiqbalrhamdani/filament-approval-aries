<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{
                mode: 'month', // default bulanan
                currentDate: new Date(),
                
                init() {
                    // Inisialisasi tanggal saat komponen dimuat
                    this.currentDate = new Date();
                },
                
                // Fungsi untuk mendapatkan hari dalam bulan
                getDaysInMonth(year, month) {
                    return new Date(year, month + 1, 0).getDate();
                },
                
                // Fungsi untuk mendapatkan hari pertama dalam bulan
                getFirstDayOfMonth(year, month) {
                    return new Date(year, month, 1).getDay();
                },
                
                // Fungsi untuk navigasi
                previous() {
                    if (this.mode === 'month') {
                        this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                    } else if (this.mode === 'week') {
                        this.currentDate = new Date(this.currentDate.getTime() - 7 * 24 * 60 * 60 * 1000);
                    }
                },
                
                next() {
                    if (this.mode === 'month') {
                        this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                    } else if (this.mode === 'week') {
                        this.currentDate = new Date(this.currentDate.getTime() + 7 * 24 * 60 * 60 * 1000);
                    }
                },
                
                // Fungsi untuk kembali ke hari ini
                goToToday() {
                    this.currentDate = new Date();
                },
                
                // Format tanggal untuk tampilan
                formatDate(date, format) {
                    const options = { 
                        year: 'numeric', 
                        month: 'long'
                    };
                    return date.toLocaleDateString('id-ID', options);
                },
                
                // Format tanggal pendek
                formatShortDate(date) {
                    const options = { 
                        day: 'numeric',
                        month: 'long', 
                        year: 'numeric'
                    };
                    return date.toLocaleDateString('id-ID', options);
                },
                
                // Cek apakah tanggal adalah hari ini
                isToday(date) {
                    if (!date) return false;
                    const today = new Date();
                    const compareDate = new Date(date);
                    return compareDate.getDate() === today.getDate() && 
                           compareDate.getMonth() === today.getMonth() && 
                           compareDate.getFullYear() === today.getFullYear();
                },
                
                // Mendapatkan daftar hari dalam minggu
                getWeekDays() {
                    const startOfWeek = new Date(this.currentDate);
                    // Mulai dari Senin (1), jika Minggu (0) maka mundur 6 hari
                    const dayOfWeek = startOfWeek.getDay();
                    const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
                    startOfWeek.setDate(startOfWeek.getDate() + diff);
                    
                    const weekDays = [];
                    for (let i = 0; i < 7; i++) {
                        const day = new Date(startOfWeek);
                        day.setDate(startOfWeek.getDate() + i);
                        weekDays.push(day);
                    }
                    
                    return weekDays;
                },
                
                // Mendapatkan array hari untuk bulan view
                getMonthDays() {
                    const year = this.currentDate.getFullYear();
                    const month = this.currentDate.getMonth();
                    const firstDay = this.getFirstDayOfMonth(year, month);
                    const daysInMonth = this.getDaysInMonth(year, month);
                    
                    const days = [];
                    
                    // Tambahkan hari kosong di awal
                    for (let i = 0; i < firstDay; i++) {
                        days.push(null);
                    }
                    
                    // Tambahkan hari dalam bulan
                    for (let i = 1; i <= daysInMonth; i++) {
                        days.push(new Date(year, month, i));
                    }
                    
                    return days;
                }
            }" class="space-y-4">

            <!-- HEADER SELECTOR -->
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold dark:text-white">Kalender</h2>

                <div class="flex items-center gap-2">
                    <button class="px-3 py-1 text-sm rounded-lg border dark:border-gray-600 transition-colors"
                        :class="mode === 'month' 
                            ? 'bg-primary-600 text-white dark:bg-primary-500' 
                            : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'" @click="mode = 'month'">Bulanan</button>

                    <button class="px-3 py-1 text-sm rounded-lg border dark:border-gray-600 transition-colors"
                        :class="mode === 'week' 
                            ? 'bg-primary-600 text-white dark:bg-primary-500' 
                            : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'" @click="mode = 'week'">Mingguan</button>
                </div>
            </div>

            <!-- NAVIGATION CONTROLS -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button @click="previous()"
                        class="px-3 py-1 text-sm rounded-lg border bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Sebelumnya
                    </button>

                    <button @click="goToToday()" class="px-3 py-1 text-sm rounded-lg border 
                                bg-primary-600 border-primary-700 text-white 
                                hover:bg-primary-700
                                dark:bg-primary-500 dark:border-primary-600 dark:hover:bg-primary-600
                                transition-colors font-medium">
                        Hari Ini
                    </button>
                </div>

                <div class="font-semibold dark:text-white text-center">
                    <span x-show="mode === 'month'" x-text="formatDate(currentDate)"></span>
                    <span x-show="mode === 'week'"
                        x-text="'Minggu ' + formatShortDate(getWeekDays()[0]) + ' - ' + formatShortDate(getWeekDays()[6])"></span>
                </div>

                <button @click="next()"
                    class="px-3 py-1 text-sm rounded-lg border bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-1">
                    Selanjutnya
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- MONTH VIEW -->
            <div x-show="mode === 'month'" x-cloak>
                <div class="p-6 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-sm">
                    <!-- Header hari -->
                    <div class="grid grid-cols-7 text-center text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">
                        <div>Min</div>
                        <div>Sen</div>
                        <div>Sel</div>
                        <div>Rab</div>
                        <div>Kam</div>
                        <div>Jum</div>
                        <div>Sab</div>
                    </div>

                    <!-- Grid hari -->
                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="(day, index) in getMonthDays()" :key="index">
                            <div class="aspect-square flex items-center justify-center">
                                <template x-if="day">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 transition-all cursor-pointer"
                                        :class="isToday(day) 
                                            ? 'bg-yellow-500 border-yellow-600  font-bold shadow-md' 
                                            : 'border-transparent text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                        <span x-text="day.getDate()" class="text-sm"></span>
                                    </div>
                                </template>
                                <template x-if="!day">
                                    <div class="w-10 h-10"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- WEEK VIEW -->
            <div x-show="mode === 'week'" x-cloak>
                <div class="p-6 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-sm">
                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="(day, index) in getWeekDays()" :key="index">
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"
                                    x-text="['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][day.getDay()]"></div>
                                <div class="w-12 h-12 mx-auto flex items-center justify-center rounded-full border-2 transition-all cursor-pointer"
                                    :class="isToday(day) 
                                        ? 'bg-yellow-500 border-yellow-600 text-white font-bold shadow-md' 
                                        : 'border-transparent text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'">
                                    <span x-text="day.getDate()" class="text-lg font-semibold"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- INDIKATOR HARI INI -->
            <div class="flex items-center justify-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span>Hari libur</span>
                </div>
            </div>

        </div>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    </x-filament::section>
</x-filament-widgets::widget>