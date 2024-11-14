<?php

use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('app:update-leave-balance')
            ->evenInMaintenanceMode()
            ->dailyAt('01:00');
        $schedule->command('app:update-izin')
            ->evenInMaintenanceMode()
            ->monthlyOn(25, '08:00');
        $schedule->command('app:update-izin')
            ->evenInMaintenanceMode()
            ->monthlyOn(25, '13:00');
        $schedule->command('app:update-izin')
            ->evenInMaintenanceMode()
            ->monthlyOn(25, '15:00');
    })->create();
