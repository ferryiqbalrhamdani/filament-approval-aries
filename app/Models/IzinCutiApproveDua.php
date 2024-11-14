<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinCutiApproveDua extends Model
{
    protected $fillable = [
        'izin_cuti_approve_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function izinCutiApprove()
    {
        return $this->belongsTo(IzinCutiApprove::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
