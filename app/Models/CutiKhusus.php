<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CutiKhusus extends Model
{
    protected $table = 'tb_cuti_khusus';

    protected $fillable = [
        'user_id',
        'company_id',
        'lama_cuti',
        'pilihan_cuti',
        'mulai_cuti',
        'sampai_cuti',
        'keterangan_cuti',
        'is_draft',
    ];


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
        return $this->hasOne(IzinCutiApprove::class, 'cuti_khusus_id');
    }
}
