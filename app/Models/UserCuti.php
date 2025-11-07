<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCuti extends Model
{
     protected $fillable = [
        'user_id',
        'tahun',
        'tanggal_mulai',
        'tanggal_hangus',
        'jatah_cuti',
        'sisa_cuti',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
