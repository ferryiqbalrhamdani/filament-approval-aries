<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinCutiApprove extends Model
{
    protected $fillable = [
        'cuti_khusus_id',
        'cuti_pribadi_id',
        'user_id',
        'keterangan_cuti',
        'status',
        'keterangan',
        'user_cuti_id',
        'company_id',
        'pilihan_cuti',
        'lama_cuti',
        'mulai_cuti',
        'sampai_cuti',
        'pesan_cuti',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userCuti()
    {
        return $this->belongsTo(User::class, 'user_cuti_id');
    }

    public function cutiKhusus()
    {
        return $this->belongsTo(CutiKhusus::class);
    }

    public function cutiPribadi()
    {
        return $this->belongsTo(CutiPribadi::class);
    }

    public function izinCutiApproveDua()
    {
        return $this->hasOne(IzinCutiApproveDua::class); // or `hasMany`, depending on your structure
    }
}
