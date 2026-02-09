<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PNS extends Authenticatable
{
    use Notifiable;

    /**
     * =========================
     * TABLE (WAJIB - FIX ERROR)
     * =========================
     */
    protected $table = 'p_n_s';

    /**
     * =========================
     * PRIMARY KEY
     * =========================
     * (default id, jadi tidak perlu diubah
     * tapi ditulis agar eksplisit & aman)
     */
    protected $primaryKey = 'id';

    /**
     * =========================
     * MASS ASSIGNMENT
     * =========================
     */
    protected $fillable = [
        'nip',
        'password',
        'nama',
        'unit_kerja_id',
        'no_telp',
    ];

    /**
     * =========================
     * HIDDEN ATTRIBUTE
     * =========================
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * =========================
     * CASTING
     * =========================
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * =========================
     * RELATION: UNIT KERJA
     * =========================
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    /**
     * =========================
     * RELATION: ABSENSI
     * =========================
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'pns_id');
    }
}
