<x-filament-widgets::widget>
    <x-filament::section>

        <style>
            .filament-calendar .day {
                color: inherit;
                transition: background-color .15s ease, color .15s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .filament-calendar .day.today {
                border: 2px solid #2563eb !important;
                border-radius: 8px;
            }

            .dark .filament-calendar .day.today {
                border-color: #3b82f6 !important;
            }

            .filament-calendar .day.holiday {
                color: #dc2626 !important;
            }

            .dark .filament-calendar .day.holiday {
                color: #fca5a5 !important;
            }

            .filament-calendar .day.weekend {
                color: #b91c1c !important;
            }

            .dark .filament-calendar .day.weekend {
                color: #f87171 !important;
            }

            .filament-calendar .dot {
                width: .4rem;
                height: .4rem;
                border-radius: 9999px;
                margin-top: .15rem;
                display: inline-block;
            }
        </style>


        <div class="p-6 space-y-4">


            {{-- Navigasi Bulan --}}
            <div class="flex flex-col items-center gap-3
            sm:flex-row sm:items-center sm:justify-between">

                <button wire:click="previousMonth" class="px-3 py-1 text-xs sm:text-sm rounded bg-gray-200 dark:bg-gray-700 
               hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                    ← Prev
                </button>

                <h2 class="text-gray-900 dark:text-white text-center">
                    <p class="text-base sm:text-xl font-bold">
                        {{ $this->currentDate->translatedFormat('F Y') }}
                    </p>

                    <div class="flex justify-center mt-1">
                        <button wire:click="goToToday" class="px-3 py-1 text-xs sm:text-sm rounded bg-gray-200 dark:bg-gray-700 
                       hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                            Hari Ini
                        </button>
                    </div>
                </h2>

                <button wire:click="nextMonth" class="px-3 py-1 text-xs sm:text-sm rounded bg-gray-200 dark:bg-gray-700 
               hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                    Next →
                </button>
            </div>


            <x-filament::section>
                {{-- Hari --}}
                <div
                    class="grid grid-cols-7 text-center font-semibold text-[0.60rem] sm:text-xs md:text-sm lg:text-base">
                    @foreach(['Ming','Sen','Sel','Rab','Kam','Jum','Sab'] as $day)
                    <div>{{ $day }}</div>
                    @endforeach
                </div>



                {{-- Tanggal --}}
                @php
                $holidays = collect($this->getHolidays())->pluck('name', 'date')->toArray();
                @endphp

                <div class="mt-6 filament-calendar 
                    grid grid-cols-7 gap-1 
                    sm:gap-2 
                    text-xs sm:text-sm md:text-base">
                    @foreach($this->getMonthData() as $date)
                    @php
                    $dateString = $date->toDateString();
                    $isToday = $date->isToday();
                    $isCurrentMonth = $date->month === $this->currentDate->month;
                    $isWeekend = ($date->dayOfWeek === 0 || $date->dayOfWeek === 6);
                    $isHoliday = array_key_exists($dateString, $holidays);
                    @endphp

                    <div class="day 
                        h-10 sm:h-12 md:h-14
                        rounded cursor-pointer select-none font-medium
                        flex items-center justify-center flex-col
                        hover:bg-blue-100 dark:hover:bg-gray-600
                        {{ $isToday ? 'today' : '' }}
                        {{ $isHoliday ? 'holiday' : '' }}
                        {{ $isWeekend ? 'weekend' : '' }}
                        {{ !$isCurrentMonth ? 'opacity-40' : '' }}
                        hover:bg-blue-100 dark:hover:bg-gray-600"
                        title="{{ $isHoliday ? $holidays[$dateString] : '' }}">
                        <span
                            class="{{ !$isCurrentMonth ? 'text-[0.65rem] text-gray-400 dark:text-gray-500' : 'text-base' }}">
                            {{ $date->day }}
                        </span>



                        <span class="dot {{ $isHoliday ? 'bg-red-600' : '' }}"
                            style="{{ !$isHoliday ? 'visibility: hidden;' : '' }}">
                        </span>
                    </div>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- Daftar Hari Libur --}}
            @if(count($this->getHolidays()) > 0)
            <p class="font-semibold">Hari Libur:</p>
            <ul class="space-y-1">
                @foreach($this->getHolidays() as $h)
                <li class="text-sm text-red-500 dark:text-red-300">
                    {{ \Carbon\Carbon::parse($h['date'])->translatedFormat('d F Y') }} — {{ $h['name'] }}
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400 italic">Tidak ada libur bulan ini</p>
            @endif

        </div>
    </x-filament::section>
</x-filament-widgets::widget>