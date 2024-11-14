<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratIzinApproveDua extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_izin_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function suratIzin()
    {
        return $this->belongsTo(SuratIzin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
