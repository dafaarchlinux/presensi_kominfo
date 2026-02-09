<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\PNS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPresensiController extends Controller
{
    public function presensi(Request $request)
    {
        Log::info('ADMIN PRESENSI HIT', $request->all());

        try {

            /*
            =========================================================
            VALIDASI IDENTITAS (WAJIB DARI WAJAH)
            =========================================================
            */
            $request->validate([
                'pns_id' => 'required',
                'type'   => 'required|in:masuk,pulang',
            ]);

            $pns = PNS::find((int) $request->pns_id);

            if (!$pns) {
                return response()->json([
                    'success' => false,
                    'message' => 'PNS tidak ditemukan.',
                ], 422);
            }

            $now   = Carbon::now('Asia/Jakarta');
            $today = $now->toDateString();

            return DB::transaction(function () use (
                $request,
                $now,
                $today,
                $pns
            ) {

                $absen = Absensi::where('pns_id', $pns->id)
                    ->whereDate('tanggal', $today)
                    ->lockForUpdate()
                    ->first();

                /*
                =================================================
                ABSEN MASUK
                =================================================
                */
                if ($request->type === 'masuk') {

                    if ($absen) {
                        return response()->json([
                            'success' => false,
                            'nama'    => $pns->nama,
                            'message' => "{$pns->nama} sudah melakukan presensi masuk hari ini.",
                        ], 422);
                    }

                    // ===== ATURAN JAM MASUK =====
                    $jamMasukMulai = Carbon::createFromTimeString(
                        '07:30',
                        'Asia/Jakarta'
                    )->setDateFrom($now);

                    $jamMasukSelesai = Carbon::createFromTimeString(
                        '08:30',
                        'Asia/Jakarta'
                    )->setDateFrom($now);

                    // BELUM BOLEH ABSEN
                    if ($now->lessThan($jamMasukMulai)) {
                        return response()->json([
                            'success' => false,
                            'nama'    => $pns->nama,
                            'message' => 'Belum saatnya absen masuk.',
                        ], 422);
                    }

                    // STATUS MASUK
                    $statusMasuk = $now->greaterThan($jamMasukSelesai)
                        ? 'terlambat'
                        : 'hadir';

                    Absensi::create([
                        'pns_id'    => $pns->id,
                        'tanggal'   => $today,
                        'jam_masuk' => $now->format('H:i:s'),
                        'status'    => $statusMasuk,
                        'metode'    => 'admin-face',
                    ]);

                    return response()->json([
                        'success' => true,
                        'nama'    => $pns->nama,
                        'message' =>
                            "Presensi masuk berhasil. Status: " .
                            strtoupper($statusMasuk),
                    ]);
                }

                /*
                =================================================
                ABSEN PULANG
                =================================================
                */
                if ($request->type === 'pulang') {

                    if (!$absen) {
                        return response()->json([
                            'success' => false,
                            'nama'    => $pns->nama,
                            'message' => 'Belum melakukan presensi masuk.',
                        ], 422);
                    }

                    if ($absen->jam_pulang) {
                        return response()->json([
                            'success' => false,
                            'nama'    => $pns->nama,
                            'message' => 'Sudah melakukan presensi pulang.',
                        ], 422);
                    }

                    // ===== ATURAN JAM PULANG =====
                    $jamPulangMulai = Carbon::createFromTimeString(
                        '16:00',
                        'Asia/Jakarta'
                    )->setDateFrom($now);

                    $jamPulangTelat = Carbon::createFromTimeString(
                        '17:00',
                        'Asia/Jakarta'
                    )->setDateFrom($now);

                    // BELUM BOLEH ABSEN PULANG
                    if ($now->lessThan($jamPulangMulai)) {
                        return response()->json([
                            'success' => false,
                            'nama'    => $pns->nama,
                            'message' => 'Belum saatnya absen pulang.',
                        ], 422);
                    }

                    // STATUS PULANG
                    $statusPulang = $now->greaterThan($jamPulangTelat)
                        ? 'terlambat'
                        : 'tepat_waktu';

                    $absen->update([
                        'jam_pulang'    => $now->format('H:i:s'),
                        'status_pulang' => $statusPulang,
                    ]);

                    return response()->json([
                        'success' => true,
                        'nama'    => $pns->nama,
                        'message' =>
                            "Presensi pulang berhasil. Status: " .
                            strtoupper($statusPulang),
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Tipe presensi tidak valid.',
                ], 422);
            });

        } catch (\Throwable $e) {

            Log::error('ADMIN PRESENSI ERROR', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada sistem presensi admin.',
            ], 500);
        }
    }
}
