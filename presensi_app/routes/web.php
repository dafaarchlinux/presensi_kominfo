<?php

use App\Http\Controllers\AuthPnsController;
use App\Models\PNS;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect Root ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi PNS
Route::get('/login', [AuthPnsController::class, 'showLogin'])->name('login');
Route::get('/login-pns', [AuthPnsController::class, 'showLogin'])->name('pns.login');
Route::post('/login-pns', [AuthPnsController::class, 'login'])->name('pns.login.submit');
Route::post('/logout-pns', [AuthPnsController::class, 'logout'])->name('pns.logout');

// Halaman Presensi PNS (Wajib Login PNS)
Route::middleware('auth:pns')->group(function () {
    Route::get('/pns/presensi-wajah', function () {
        $pns = Auth::guard('pns')->user();
        $absensiHariIni = Presensi::where('pns_id', $pns->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();
        $riwayatPresensi = Presensi::where('pns_id', $pns->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->take(15)
            ->get();

        return view('pns.presensi-wajah', compact('pns', 'absensiHariIni', 'riwayatPresensi'));
    })->name('pns.presensi.wajah');
});

// API Presensi & Biometrik Wajah
Route::prefix('api')->group(function () {
    Route::get('/get-pns-faces', function () {
        $pnsList = PNS::whereNotNull('face_embedding')->select('id', 'nama', 'nip', 'face_embedding')->get();
        return response()->json($pnsList);
    });

    Route::post('/simpan-wajah-pns', function (Request $request) {
        $pns = PNS::findOrFail($request->pns_id);
        $pns->update(['face_embedding' => $request->face_descriptors]);
        return response()->json(['success' => true, 'message' => 'Data biometrik wajah berhasil didaftarkan!']);
    });

    Route::post('/simpan-presensi', function (Request $request) {
        $pns = PNS::findOrFail($request->pns_id);
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $mode = $request->input('mode', 'masuk');

        $presensi = Presensi::firstOrNew([
            'pns_id' => $pns->id,
            'tanggal' => $today,
        ]);

        if ($mode === 'masuk') {
            if ($presensi->jam_masuk) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melakukan presensi masuk hari ini pada pukul ' . $presensi->jam_masuk]);
            }
            $presensi->jam_masuk = $currentTime;
            $presensi->status = ($currentTime > '08:00:00') ? 'Terlambat' : 'Tepat Waktu';
            $presensi->metode = 'Face Recognition';
            $presensi->save();

            return response()->json([
                'success' => true,
                'message' => 'Presensi Masuk Berhasil! Status: ' . $presensi->status,
                'waktu' => $currentTime,
            ]);
        } else {
            if (!$presensi->exists || !$presensi->jam_masuk) {
                return response()->json(['success' => false, 'message' => 'Anda belum melakukan presensi masuk hari ini.']);
            }
            if ($presensi->jam_pulang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melakukan presensi pulang hari ini pada pukul ' . $presensi->jam_pulang]);
            }

            $presensi->jam_pulang = $currentTime;
            $presensi->status_pulang = ($currentTime < '15:00:00') ? 'Pulang Cepat' : 'Tepat Waktu';
            $presensi->save();

            return response()->json([
                'success' => true,
                'message' => 'Presensi Pulang Berhasil! Status: ' . $presensi->status_pulang,
                'waktu' => $currentTime,
            ]);
        }
    });
});

// ROUTE CETAK PDF & EKSPOR CSV DI ADMIN
Route::prefix('admin/laporan')->name('admin.laporan.')->group(function () {
    Route::get('/cetak-pdf', function (Request $request) {
        $query = Presensi::with(['pns.unitKerja'])->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc');

        if ($request->dari_tanggal) {
            $query->whereDate('tanggal', '>=', $request->dari_tanggal);
        }
        if ($request->sampai_tanggal) {
            $query->whereDate('tanggal', '<=', $request->sampai_tanggal);
        }
        if ($request->unit_kerja_id) {
            $query->whereHas('pns', fn ($q) => $q->where('unit_kerja_id', $request->unit_kerja_id));
        }

        $data = $query->get();
        $periode = ($request->dari_tanggal && $request->sampai_tanggal)
            ? Carbon::parse($request->dari_tanggal)->format('d/m/Y') . ' s.d ' . Carbon::parse($request->sampai_tanggal)->format('d/m/Y')
            : 'Seluruh Riwayat Presensi';

        return view('filament.pages.cetak-presensi', compact('data', 'periode'));
    })->name('cetak.pdf');

    Route::get('/ekspor-excel', function (Request $request) {
        $query = Presensi::with(['pns.unitKerja'])->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc');

        if ($request->dari_tanggal) {
            $query->whereDate('tanggal', '>=', $request->dari_tanggal);
        }
        if ($request->sampai_tanggal) {
            $query->whereDate('tanggal', '<=', $request->sampai_tanggal);
        }

        $data = $query->get();
        $fileName = 'Rekap_Presensi_PNS_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'NIP', 'Nama Pegawai', 'Unit Kerja', 'Jam Masuk', 'Status Masuk', 'Jam Pulang', 'Status Pulang', 'Metode']);

            foreach ($data as $i => $row) {
                fputcsv($file, [
                    $i + 1,
                    $row->tanggal,
                    $row->pns->nip ?? '-',
                    $row->pns->nama ?? '-',
                    $row->pns->unitKerja->nama_unit_kerja ?? '-',
                    $row->jam_masuk ?? '-',
                    $row->status ?? '-',
                    $row->jam_pulang ?? '-',
                    $row->status_pulang ?? '-',
                    $row->metode ?? 'Face Recognition',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('ekspor.excel');
});
