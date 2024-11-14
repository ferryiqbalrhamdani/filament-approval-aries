<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratIzin extends Model
{
    use HasFactory;
    protected $table = 'tb_izin';

    protected $fillable = [
        'user_id',
        'keperluan_izin',
        'photo',
        'lama_izin',
        'tanggal_izin',
        'sampai_tanggal',
        'durasi_izin',
        'jam_izin',
        'sampai_jam',
        'keterangan_izin',
        'company_id',
        'status_izin',
        'is_draft',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($suratIzin) {
            if ($suratIzin->photo) {
                // Hapus file dari storage jika ada photo
                Storage::disk('public')->delete($suratIzin->photo);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function suratIzinApprove()
    {
        return $this->hasOne(SuratIzinApprove::class);
    }
    public function suratIzinApproveDua()
    {
        return $this->hasOne(SuratIzinApproveDua::class);
    }
}
