<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // IZINKAN SEMUA FIELD YANG KITA PAKAI
    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
    ];

    protected $casts = [
        'jam_masuk'  => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
    ];
}
