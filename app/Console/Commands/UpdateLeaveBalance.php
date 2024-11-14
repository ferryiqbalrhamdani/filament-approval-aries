<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-leave-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cuti berhasil di update';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'cuti_pribadi');
        })->get();

        $today = Carbon::today();

        foreach ($users as $user) {
            // 1. Tambah 6 hari cuti pada awal tahun (1 Januari) dengan maksimum 12 hari
            if ($today->isSameDay(Carbon::create($today->year, 1, 1))) {
                $user->sisa_cuti_sebelumnya = $user->sisa_cuti;
                $user->sisa_cuti = 6;
            }

            // 2. Reset cuti pada tanggal 1 Juli, 7, 1
            if ($today->isSameDay(Carbon::create($today->year, 7, 1))) {
                $totalCuti = $user->sisa_cuti_sebelumnya + $user->sisa_cuti;

                // Menghitung cuti berdasarkan penggunaan dari Januari hingga Juli
                if ($totalCuti <= 6) {
                    $user->sisa_cuti_sebelumnya = 0;
                    $user->sisa_cuti = max($totalCuti, 0);
                } else {
                    $usedCuti = $totalCuti - $user->sisa_cuti; // Misalnya cuti yang sudah dipakai
                    $user->sisa_cuti = max($totalCuti - $usedCuti, 0); // Update `sisa_cuti`
                    $user->sisa_cuti_sebelumnya = 0;
                }
            }

            // Simpan perubahan
            $user->save();
        }
    }
}
