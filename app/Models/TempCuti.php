<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempCuti extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'cuti_pribadi_id',
        'sisa_cuti',
        'sisa_cuti_sebelumnya',
    ];

    public function cutiPribadi()
    {
        return $this->belongsTo(CutiPribadi::class);
    }
}
