<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserCuti;
use Illuminate\Console\Command;

class UpdateCutiTahunan extends Command
{
    protected $signature = 'cuti:update-tahunan';
    protected $description = 'Menambah jatah cuti baru setiap 1 Januari dan memperbarui status cuti setiap 1 Juni';

    public function handle()
    {
        $this->info('🚀 Memulai proses update cuti pribadi...');
        $today = now()->startOfDay();

        // ======= 1 JANUARI: TAMBAH CUTI BARU =======
        if ($today->isSameDay(Carbon::create($today->year, 1, 1))) {
            $users = User::whereHas('roles', fn($q) => $q->where('name', 'cuti_pribadi'))->get();

            foreach ($users as $user) {
                // Cegah duplikasi cuti di tahun yang sama
                $existing = UserCuti::where('user_id', $user->id)
                    ->where('tahun', $today->year)
                    ->exists();

                if (! $existing) {
                    UserCuti::create([
                        'user_id' => $user->id,
                        'tahun' => $today->year,
                        'tanggal_mulai' => $today,
                        'tanggal_hangus' => Carbon::create($today->year + 1, 6, 1),
                        'jatah_cuti' => 6,
                        'sisa_cuti' => 6,
                        'status' => 'tersedia', // status default
                    ]);
                }
            }

            $this->info('✅ Jatah cuti tahun ' . $today->year . ' berhasil ditambahkan.');
        } else {
            $this->info('📅 Hari ini bukan tanggal 1 Januari, tidak ada jatah cuti yang ditambahkan.');
        }

        // ======= 1 JUNI: UPDATE STATUS CUTI YANG HANGUS =======
        if ($today->isSameDay(Carbon::create($today->year, 6, 1))) {
            $cutis = UserCuti::all();

            foreach ($cutis as $cuti) {
                if ($today->greaterThanOrEqualTo(Carbon::parse($cuti->tanggal_hangus))) {
                    $cuti->update([
                        'sisa_cuti' => 0,
                        'status' => 'hangus',
                    ]);
                } else {
                    $cuti->update([
                        'status' => 'tersedia',
                    ]);
                }
            }

            $this->info('🗓️ Status cuti diperbarui. Cuti yang hangus telah diatur menjadi 0 dan status = hangus.');
        } else {
            $this->info('📅 Hari ini bukan tanggal 1 Juni, tidak ada cuti yang diperbarui.');
        }

        return Command::SUCCESS;
    }
}
