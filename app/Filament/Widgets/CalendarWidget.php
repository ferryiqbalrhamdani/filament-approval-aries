<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use App\Models\PublicHoliday;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class CalendarWidget extends Widget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected static string $view = 'filament.widgets.calendar-widget';

    public Carbon $currentDate;

    public function mount(): void
    {
        $this->currentDate = Carbon::parse(now());
    }

    public function goToToday()
    {
        $this->currentDate = now();
    }


    public function getHolidays(): array
    {
        return PublicHoliday::whereMonth('date', $this->currentDate->month)
            ->whereYear('date', $this->currentDate->year)
            ->orderBy('date')
            ->get()
            ->toArray();
    }

  public function previousMonth()
    {
        $this->currentDate->subMonth();
    }

    public function nextMonth()
    {
        $this->currentDate->addMonth();
    }


    public function getMonthData()
    {
        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();

        // Ubah start hari → Senin
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $dates = [];

        $current = $startOfCalendar->copy();
        while ($current <= $endOfCalendar) {
            $dates[] = $current->copy();
            $current->addDay();
        }

        return $dates;
    }

}
