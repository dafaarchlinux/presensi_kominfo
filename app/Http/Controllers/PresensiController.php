<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\PNS;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    // 📍 Lokasi Kantor Terpadu Pemda Sragen
    private const OFFICE_LAT = -7.423633899458331;
    private const OFFICE_LNG = 111.00744679061334;
    private const OFFICE_RADIUS = 4000; // meter

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PRESENSI PNS (RENDER VIEW)
    |--------------------------------------------------------------------------
    */
    public function halamanPresensiPns()
    {
        $pns = Auth::guard('pns')->user();
        $today = now('Asia/Jakarta')->toDateString();

        // Absensi hari ini
        $absensiHariIni = Absensi::where('pns_id', $pns->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Status presensi hari ini
        if (!$absensiHariIni) {
            $statusHariIni = 'Belum presensi';
        } elseif ($absensiHariIni->jam_masuk && !$absensiHariIni->jam_pulang) {
            $statusHariIni = 'Sudah absen masuk';
        } elseif ($absensiHariIni->jam_masuk && $absensiHariIni->jam_pulang) {
            $statusHariIni = 'Sudah absen pulang';
        } else {
            $statusHariIni = 'Belum presensi';
        }

        // Riwayat presensi (10 terakhir)
        $riwayatPresensi = Absensi::where('pns_id', $pns->id)
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        return view('pns.presensi-wajah', [
            'pns'             => $pns,
            'statusHariIni'   => $statusHariIni,
            'absensiHariIni'  => $absensiHariIni,
            'riwayatPresensi' => $riwayatPresensi,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ENDPOINT PRESENSI (FACE + GPS)
    |--------------------------------------------------------------------------
    */
    public function presensi(Request $request)
    {
        Log::info('PRESENSI HIT', $request->all());

        $validated = $request->validate([
            'pns_id'    => 'required|integer|exists:p_n_s,id',
            'type'      => 'required|in:masuk,pulang',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'nullable|numeric',
        ]);

        try {
            $pns = PNS::findOrFail($validated['pns_id']);

            // ================= VALIDASI LOKASI =================
            $distance = $this->hitungJarak(
                self::OFFICE_LAT,
                self::OFFICE_LNG,
                $validated['latitude'],
                $validated['longitude']
            );

            if ($distance > self::OFFICE_RADIUS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi hanya dapat dilakukan di area Kantor Terpadu Pemda Sragen.',
                ], 422);
            }

            $now   = Carbon::now('Asia/Jakarta');
            $today = $now->toDateString();

            if (!Setting::first()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting jam kerja belum diatur.',
                ], 500);
            }

            $result = DB::transaction(function () use ($validated, $pns, $now, $today) {

                $absen = Absensi::where('pns_id', $pns->id)
                    ->whereDate('tanggal', $today)
                    ->lockForUpdate()
                    ->first();

                // ================= ABSEN MASUK =================
                if ($validated['type'] === 'masuk') {

                    if ($absen) {
                        return [
                            'success' => false,
                            'message' => "{$pns->nama} telah melakukan presensi masuk hari ini.",
                            'code'    => 422,
                        ];
                    }

                    $jamMulai   = Carbon::parse("$today 07:30:00", 'Asia/Jakarta');
                    $jamSelesai = Carbon::parse("$today 08:30:00", 'Asia/Jakarta');

                    if ($now->lessThan($jamMulai)) {
                        return [
                            'success' => false,
                            'message' => "{$pns->nama} belum dapat melakukan presensi masuk karena belum waktunya.",
                            'code'    => 422,
                        ];
                    }

                    $status = $now->greaterThan($jamSelesai) ? 'terlambat' : 'hadir';

                    Absensi::create([
                        'pns_id'    => $pns->id,
                        'tanggal'   => $today,
                        'jam_masuk' => $now->format('H:i:s'),
                        'status'    => $status,
                        'metode'    => 'face',
                        'latitude'  => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                    ]);

                    return [
                        'success' => true,
                        'message' => "Presensi masuk berhasil. Status: " . strtoupper($status),
                        'code'    => 200,
                    ];
                }

                // ================= ABSEN PULANG =================
                if ($validated['type'] === 'pulang') {

                    if (!$absen) {
                        return [
                            'success' => false,
                            'message' => "{$pns->nama} belum melakukan presensi masuk.",
                            'code'    => 422,
                        ];
                    }

                    if ($absen->jam_pulang) {
                        return [
                            'success' => false,
                            'message' => "{$pns->nama} telah melakukan presensi pulang.",
                            'code'    => 422,
                        ];
                    }

                    $jamMulai = Carbon::parse("$today 16:00:00", 'Asia/Jakarta');

                    if ($now->lessThan($jamMulai)) {
                        return [
                            'success' => false,
                            'message' => "{$pns->nama} belum dapat melakukan presensi pulang karena belum waktunya.",
                            'code'    => 422,
                        ];
                    }

                    $absen->update([
                        'jam_pulang' => $now->format('H:i:s'),
                    ]);

                    return [
                        'success' => true,
                        'message' => "Presensi pulang berhasil.",
                        'code'    => 200,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Tipe presensi tidak valid.',
                    'code'    => 422,
                ];
            });

            return response()->json(
                collect($result)->except('code'),
                $result['code']
            );

        } catch (\Throwable $e) {

            Log::error('PRESENSI ERROR', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem presensi.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HITUNG JARAK (HAVERSINE)
    |--------------------------------------------------------------------------
    */
    private function hitungJarak($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
