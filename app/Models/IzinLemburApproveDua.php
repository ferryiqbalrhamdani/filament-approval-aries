<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinLemburApproveDua extends Model
{
    protected $fillable = [
        'izin_lembur_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function izinLembur()
    {
        return $this->belongsTo(IzinLembur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
