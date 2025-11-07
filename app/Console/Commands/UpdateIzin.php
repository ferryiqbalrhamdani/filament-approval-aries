<?php

namespace App\Console\Commands;

use App\Models\SuratIzin;
use App\Models\CutiKhusus;
use App\Models\IzinLembur;
use App\Models\CutiPribadi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateIzin extends Command
{
    protected $signature = 'app:update-izin';
    protected $description = 'Update izin status and approvals';

    public function handle()
    {
        DB::transaction(function () {
            $this->info('Memulai proses update draft...');

            $suratIzinList = SuratIzin::with('user')->where('is_draft', true)->get();
            $izinLemburList = IzinLembur::with('user')->where('is_draft', true)->get();
            $cutiKhususList = CutiKhusus::with('user')->where('is_draft', true)->get();
            $cutiPribadiList = CutiPribadi::with('user')->where('is_draft', true)->get();

            foreach ($suratIzinList as $suratIzin) {
                $userApproveId = optional($suratIzin->user)->user_approve_id;

                $suratIzin->update(['is_draft' => false]);
                $suratIzin->suratIzinApprove()->create([
                    'surat_izin_id' => $suratIzin->id,
                    'user_id' => $userApproveId,
                    'status' => $userApproveId ? 0 : 1,
                ]);
                $suratIzin->suratIzinApproveDua()->create(['surat_izin_id' => $suratIzin->id]);
            }

            foreach ($izinLemburList as $izinLembur) {
                $userApproveId = optional($izinLembur->user)->user_approve_id;

                $izinLembur->update(['is_draft' => false]);
                $izinLembur->izinLemburApprove()->create([
                    'surat_izin_id' => $izinLembur->id,
                    'user_id' => $userApproveId,
                    'status' => $userApproveId ? 0 : 1,
                ]);
                $izinLembur->izinLemburApproveDua()->create(['surat_izin_id' => $izinLembur->id]);
            }

            foreach ($cutiKhususList as $cutiKhusus) {
                $userApproveId = optional($cutiKhusus->user)->user_approve_id;

                $cutiKhusus->update(['is_draft' => false]);
                $izinCuti = $cutiKhusus->izinCutiApprove()->create([
                    'cuti_khusus_id' => $cutiKhusus->id,
                    'keterangan_cuti' => 'Cuti Khusus',
                    'user_cuti_id' => $cutiKhusus->user->id ?? null,
                    'company_id' => $cutiKhusus->company_id,
                    'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                    'lama_cuti' => $cutiKhusus->lama_cuti,
                    'mulai_cuti' => $cutiKhusus->mulai_cuti,
                    'sampai_cuti' => $cutiKhusus->sampai_cuti,
                    'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                    'user_id' => $userApproveId,
                    'status' => $userApproveId ? 0 : 1,
                ]);

                $izinCuti->izinCutiApproveDua()->create([
                    'izin_cuti_approve_id' => $izinCuti->id,
                ]);
            }

            foreach ($cutiPribadiList as $cutiPribadi) {
                $userApproveId = optional($cutiPribadi->user)->user_approve_id;

                $cutiPribadi->update(['is_draft' => false]);
                $cutiPribadiApprove = $cutiPribadi->izinCutiApprove()->create([
                    'cuti_pribadi_id' => $cutiPribadi->id,
                    'keterangan_cuti' => 'Cuti Pribadi',
                    'user_cuti_id' => $cutiPribadi->user->id ?? null,
                    'company_id' => $cutiPribadi->company_id,
                    'lama_cuti' => $cutiPribadi->lama_cuti,
                    'mulai_cuti' => $cutiPribadi->mulai_cuti,
                    'sampai_cuti' => $cutiPribadi->sampai_cuti,
                    'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                    'user_id' => $userApproveId,
                    'status' => $userApproveId ? 0 : 1,
                ]);

                $cutiPribadiApprove->izinCutiApproveDua()->create([
                    'izin_cuti_approve_id' => $cutiPribadiApprove->id,
                ]);
            }

            $this->info('Update draft selesai dijalankan!');
        });
    }
}
