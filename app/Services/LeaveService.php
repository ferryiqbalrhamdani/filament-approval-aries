<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CutiPribadi;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public const JATAH_TAHUNAN = 6;
    public const MAKS_PER_PERIODE = 6;

    /**
     * Aturan:
     * - 1 Januari  : jatah 6 hari terbit. Sisa tahun lalu menjadi "sebelumnya" (maks 6).
     *                Maksimal total 12 hari (Jan - Jun).
     * - 1 Juli     : sisa tahun lalu (1 tahun 6 bulan sejak terbit) HANGUS.
     * Aman dijalankan berulang kali di hari yang sama (idempotent).
     */
    public function updateLeaveBalance(User $user, Carbon $today)
    {
        if ($today->isSameDay(Carbon::create($today->year, 1, 1))
            && (int) $user->cuti_terbit_tahun !== $today->year) {
            $user->sisa_cuti_sebelumnya = min(self::MAKS_PER_PERIODE, max(0, (int) $user->sisa_cuti));
            $user->sisa_cuti = self::JATAH_TAHUNAN;
            $user->cuti_terbit_tahun = $today->year;
        }

        if ($today->isSameDay(Carbon::create($today->year, 7, 1))
            && (int) $user->cuti_hangus_tahun !== $today->year) {
            $user->sisa_cuti_sebelumnya = 0; // hangus
            $user->cuti_hangus_tahun = $today->year;
        }

        $user->save();
    }

    /**
     * Kembalikan potongan cuti sebuah pengajuan, maksimal satu kali.
     */
    public function refund(CutiPribadi $cuti): bool
    {
        return DB::transaction(function () use ($cuti) {
            $temp = $cuti->tempCuti()->lockForUpdate()->first();

            if (! $temp || $temp->dikembalikan_at) {
                return false;
            }

            $user = User::whereKey($cuti->user_id)->lockForUpdate()->first();
            if (! $user) {
                return false;
            }

            $now = now();
            $batasHangus = Carbon::create($now->year, 7, 1)->startOfDay();

            // Saldo tahun lalu yang sudah hangus tidak dikembalikan
            $kembaliSebelumnya = $now->lt($batasHangus) ? (int) $temp->sisa_cuti_sebelumnya : 0;

            $user->sisa_cuti = min(self::MAKS_PER_PERIODE, (int) $user->sisa_cuti + (int) $temp->sisa_cuti);
            $user->sisa_cuti_sebelumnya = min(self::MAKS_PER_PERIODE, (int) $user->sisa_cuti_sebelumnya + $kembaliSebelumnya);
            $user->save();

            $temp->update(['dikembalikan_at' => $now]);

            return true;
        });
    }
}
