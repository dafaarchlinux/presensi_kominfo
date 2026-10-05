<?php

namespace App\Filament\Widgets;

use App\Models\Presensi;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Statistik Hari Ini
        $totalHariIni = Presensi::whereDate('tanggal', $today)->count();
        $tepatWaktuHariIni = Presensi::whereDate('tanggal', $today)->where('status', 'Tepat Waktu')->count();
        $terlambatHariIni = Presensi::whereDate('tanggal', $today)->where('status', 'Terlambat')->count();

        // 2. Statistik Minggu Ini
        $totalMingguIni = Presensi::whereBetween('tanggal', [$startOfWeek, $endOfWeek])->count();

        // 3. Statistik Bulan Ini
        $totalBulanIni = Presensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();
        $tepatWaktuBulanIni = Presensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->where('status', 'Tepat Waktu')->count();
        $rasioDisiplin = $totalBulanIni > 0 ? round(($tepatWaktuBulanIni / $totalBulanIni) * 100) : 100;

        return [
            Stat::make('Kehadiran Hari Ini', $totalHariIni . ' Pegawai')
                ->description("Tepat Waktu: {$tepatWaktuHariIni} | Terlambat: {$terlambatHariIni}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($terlambatHariIni > 0 ? 'warning' : 'success')
                ->chart([$tepatWaktuHariIni, $totalHariIni]),

            Stat::make('Kehadiran Minggu Ini', $totalMingguIni . ' Catatan')
                ->description('Akumulasi kehadiran pekan aktif')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Kehadiran Bulan Ini', $totalBulanIni . ' Catatan')
                ->description("Tingkat Ketepatan: {$rasioDisiplin}%")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($rasioDisiplin >= 80 ? 'success' : 'danger')
                ->chart([50, 70, 85, $rasioDisiplin]),
        ];
    }
}
