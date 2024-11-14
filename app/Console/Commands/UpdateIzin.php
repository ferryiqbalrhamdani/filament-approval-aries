<?php

namespace App\Console\Commands;

use App\Models\SuratIzin;
use App\Models\CutiKhusus;
use App\Models\IzinLembur;
use App\Models\CutiPribadi;
use Illuminate\Console\Command;

class UpdateIzin extends Command
{
    protected $signature = 'app:update-izin';
    protected $description = 'Update izin status and approvals';

    public function handle()
    {
        // Mengambil data is_draft true
        $suratIzinList = SuratIzin::where('is_draft', true)->get();
        $izinLemburList = IzinLembur::where('is_draft', true)->get();
        $cutiKhususList = CutiKhusus::where('is_draft', true)->get();
        $cutiPribadiList = CutiPribadi::where('is_draft', true)->get();

        // Loop surat izin
        foreach ($suratIzinList as $suratIzin) {
            $suratIzin->update(['is_draft' => false]);

            $userApproveId = $suratIzin->user->user_approve_id ?? null;
            $suratIzin->suratIzinApprove()->create([
                'surat_izin_id' => $suratIzin->id,
                'user_id' => $userApproveId,
                'status' => $userApproveId ? 0 : 1,
            ]);

            $suratIzin->suratIzinApproveDua()->create(['surat_izin_id' => $suratIzin->id]);
        }

        // Loop izin lembur
        foreach ($izinLemburList as $izinLembur) {
            $izinLembur->update(['is_draft' => false]);

            $userApproveId = $izinLembur->user->user_approve_id ?? null;
            $izinLembur->izinLemburApprove()->create([
                'surat_izin_id' => $izinLembur->id,
                'user_id' => $userApproveId,
                'status' => $userApproveId ? 0 : 1,
            ]);

            $izinLembur->izinLemburApproveDua()->create(['surat_izin_id' => $izinLembur->id]);
        }

        // Loop cuti khusus
        foreach ($cutiKhususList as $cutiKhusus) {
            $cutiKhusus->update(['is_draft' => false]);

            $userApproveId = $cutiKhusus->user->user_approve_id ?? null;
            $izinCuti = $cutiKhusus->izinCutiApprove()->create([
                'cuti_khusus_id' => $cutiKhusus->id,
                'keterangan_cuti' => 'Cuti Khusus',
                'user_cuti_id' => $cutiKhusus->user->id,
                'company_id' => $cutiKhusus->company_id,
                'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
                'lama_cuti' => $cutiKhusus->lama_cuti,
                'mulai_cuti' => $cutiKhusus->mulai_cuti,
                'sampai_cuti' => $cutiKhusus->sampai_cuti,
                'pesan_cuti' => $cutiKhusus->keterangan_cuti,
                'user_id' => $userApproveId,
                'status' => $userApproveId ? 0 : 1,
            ]);

            $izinCuti->izinCutiApproveDua()->create(['surat_izin_id' => $cutiKhusus->id]);
        }

        // Loop cuti pribadi (jika diperlukan)
        foreach ($cutiPribadiList as $cutiPribadi) {
            $cutiPribadi->update(['is_draft' => false]);

            $userApproveId = $cutiPribadi->user->user_approve_id ?? null;
            $cutiPribadiApprove = $cutiPribadi->izinCutiApprove()->create([
                'cuti_pribadi_id' => $cutiPribadi->id,
                'keterangan_cuti' => 'Cuti Pribadi',
                'user_cuti_id' => $cutiPribadi->user->id,
                'company_id' => $cutiPribadi->company_id,
                'lama_cuti' => $cutiPribadi->lama_cuti,
                'mulai_cuti' => $cutiPribadi->mulai_cuti,
                'sampai_cuti' => $cutiPribadi->sampai_cuti,
                'pesan_cuti' => $cutiPribadi->keterangan_cuti,
                'user_id' => $userApproveId,
                'status' => $userApproveId ? 0 : 1,
            ]);

            $cutiPribadiApprove->izinCutiApproveDua()->create(['cuti_pribadi_approve_id' => $cutiPribadiApprove->id]);
        }
    }
}
