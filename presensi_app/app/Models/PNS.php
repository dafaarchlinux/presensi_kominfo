<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PNS extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'p_n_s';

    protected $fillable = [
        'nip',
        'nama',
        'password',
        'face_embedding',
        'unit_kerja_id',
        'no_telp',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'face_embedding' => 'array',
        ];
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'pns_id');
    }
}
