<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;

class AttendanceTime
{
    public static function getStatus()
    {
        $setting = Setting::first();
        $jamMasuk = Carbon::createFromTimeString(
            $setting->jam_masuk
        );
        $batasHadir = $jamMasuk->copy()->addMinutes($setting->toleransi_menit); // 07:00:00 => 07:10:00

        return now()->lte($batasHadir) ? 'hadir' : 'terlambat';
    }
}
