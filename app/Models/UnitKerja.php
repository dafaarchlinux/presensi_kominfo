<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    protected $table = 'unit_kerjas';

    protected $fillable = ['nama_unit_kerja'];

    public function pns()
    {
        return $this->hasMany(PNS::class, 'unit_kerja_id');
    }
}
