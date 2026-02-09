<?php

namespace App\Filament\Pages;

use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Pages\Page;

class FaceAttendance extends Page
{
    /**
     * =========================
     * FILAMENT v4 CONFIG
     * =========================
     */
    protected string $view = 'filament.pages.face-attendance';

    protected static ?string $navigationLabel = 'Presensi Wajah';
    protected static ?string $title = 'Presensi Wajah';

    public static function getNavigationGroup(): ?string
    {
        return 'Presensi';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    /**
     * =========================
     * STATE
     * =========================
     */
    public array $todayAttendances = [];

    /**
     * =========================
     * LIFECYCLE
     * =========================
     */
    public function mount(): void
    {
        $this->loadTodayAttendances();
    }

    /**
     * =========================
     * DATA LOADER
     * =========================
     */
    protected function loadTodayAttendances(): void
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $this->todayAttendances = Absensi::with('pns')
            ->whereDate('tanggal', $today)
            ->orderBy('created_at')
            ->get()
            ->map(function ($a) {
                return [
                    'nama'       => optional($a->pns)->nama ?? '-',
                    'nip'        => optional($a->pns)->nip ?? '-',
                    'jam_masuk'  => $a->jam_masuk ?? '-',
                    'jam_pulang' => $a->jam_pulang ?? '-',
                    'status'     => $a->status ? ucfirst($a->status) : '-',
                    'raw_status' => $a->status,
                ];
            })
            ->toArray();
    }

    /**
     * =========================
     * OPTIONAL: REFRESH MANUAL
     * =========================
     */
    public function refreshAttendances(): void
    {
        $this->loadTodayAttendances();
    }
}
