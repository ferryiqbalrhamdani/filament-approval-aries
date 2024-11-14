<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MengetahuiIzinLembur extends Model
{
    protected $fillable = [
        'user_mengetahui_id',
        'izin_lembur_id',
    ];

    public function userMengetahui()
    {
        return $this->belongsTo(User::class, 'user_mengetahui_id');
    }

    public function izinLembur()
    {
        return $this->belongsTo(IzinLembur::class, 'izin_lembur_id');
    }
}
