<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;

class LeaveService
{
    public function updateLeaveBalance(User $user, Carbon $today)
    {
        if ($today->isSameDay(Carbon::create($today->year, 1, 1))) {
            $user->sisa_cuti_sebelumnya = $user->sisa_cuti;
            $user->sisa_cuti = 6;
        }

        if ($today->isSameDay(Carbon::create($today->year, 6, 1))) {
            $totalCuti = $user->sisa_cuti_sebelumnya + $user->sisa_cuti;
            $user->sisa_cuti = min(12, $totalCuti);
            $user->sisa_cuti_sebelumnya = 0;
        }

        $user->save();
    }
}

