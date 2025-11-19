<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class CalendarWidget extends Widget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected static string $view = 'filament.widgets.calendar-widget';
}
