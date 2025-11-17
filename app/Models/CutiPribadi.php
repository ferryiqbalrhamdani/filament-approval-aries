<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CutiPribadi extends Model
{
    protected $table = 'tb_cuti_pribadi';

    protected $fillable = [
        'user_id',
        'company_id',
        'lama_cuti',
        'mulai_cuti',
        'sampai_cuti',
        'keterangan_cuti',
        'is_draft',
    ];

//    protected static function boot()
//     {
//         parent::boot();

//         static::creating(function ($cuti) {
//             $jatahAktif = $cuti->user->cutis()
//                 ->whereDate('tanggal_mulai', '<=', now())
//                 ->whereDate('tanggal_hangus', '>', now())
//                 ->orderBy('tahun', 'asc')
//                 ->first();

//             if (!$jatahAktif || $jatahAktif->sisa_cuti < $cuti->lama_cuti) {
//                 Notification::make()
//                     ->title('Kesalahan')
//                     ->danger()
//                     ->body("Anda tidak memiliki cukup jatah cuti untuk mengajukan cuti pribadi ini.")
//                     ->duration(15000)
//                     ->send();
//             }
//         });

//         static::created(function ($cuti) {
//             $cuti->kurangiSisaCuti();
//         });

//         static::deleted(function ($cuti) {
//             $cuti->kembalikanSisaCuti();
//         });
//     }


    // public function kurangiSisaCuti()
    // {
    //     $lama = $this->lama_cuti;

    //     // Cari jatah cuti aktif milik user (yang belum hangus & masih ada sisa)
    //     $jatahAktif = $this->user->cutis()
    //         ->whereDate('tanggal_mulai', '<=', now())
    //         ->whereDate('tanggal_hangus', '>', now())
    //         ->orderBy('tahun', 'asc')
    //         ->first();

    //     if ($jatahAktif) {
    //         $jatahAktif->decrement('sisa_cuti', $lama);
    //     }
    // }

    // public function kembalikanSisaCuti()
    // {
    //     $lama = $this->lama_cuti;

    //     // Jika cuti dihapus, kembalikan sisa cutinya
    //     $jatahAktif = $this->user->cutis()
    //         ->whereDate('tanggal_mulai', '<=', now())
    //         ->whereDate('tanggal_hangus', '>', now())
    //         ->orderBy('tahun', 'asc')
    //         ->first();

    //     if ($jatahAktif) {
    //         $jatahAktif->increment('sisa_cuti', $lama);
    //     }
    // }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function izinCutiApprove()
    {
        return $this->hasOne(IzinCutiApprove::class, 'cuti_pribadi_id');
    }

    public function tempCuti()
    {
        return $this->hasOne(TempCuti::class, 'cuti_pribadi_id');
    }
}
