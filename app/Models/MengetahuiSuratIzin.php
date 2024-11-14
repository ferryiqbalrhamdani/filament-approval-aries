<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MengetahuiSuratIzin extends Model
{
    protected $fillable = [
        'user_mengetahui_id',
        'surat_izin_id',
    ];

    public function userMengetahui()
    {
        return $this->belongsTo(User::class, 'user_mengetahui_id');
    }

    public function suratIzin()
    {
        return $this->belongsTo(SuratIzin::class, 'surat_izin_id');
    }
}
