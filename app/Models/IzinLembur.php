<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IzinLembur extends Model
{
    protected $table = 'tb_lembur';

    protected $fillable = [
        'tarif_lembur_id',
        'tanggal_lembur',
        'start_time',
        'end_time',
        'keterangan_lembur',
        'total',
        'user_id',
        'is_draft',
        'lama_lembur',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($izinLembur) {
            $izinLembur->izinLemburApprove()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tarifLembur()
    {
        return $this->belongsTo(TarifLembur::class);
    }

    public function izinLemburApprove()
    {
        return $this->hasOne(IzinLemburApprove::class);
    }
    public function izinLemburApproveDua()
    {
        return $this->hasOne(IzinLemburApproveDua::class);
    }

    public function mengetahui()
    {
        return $this->hasOne(MengetahuiIzinLembur::class);
    }
}
