<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'pns_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'status_pulang',
        'metode',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pns(): BelongsTo
    {
        return $this->belongsTo(PNS::class, 'pns_id');
    }
}
