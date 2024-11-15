<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MengetahuiIzinCuti extends Model
{
    protected $fillable = [
        'user_mengetahui_id',
        'izin_cuti_approve_id',
    ];

    public function userMengetahui()
    {
        return $this->belongsTo(User::class, 'user_mengetahui_id');
    }

    public function izinCutiApprove()
    {
        return $this->belongsTo(IzinCutiApprove::class, 'izin_cuti_approve_id');
    }
}
