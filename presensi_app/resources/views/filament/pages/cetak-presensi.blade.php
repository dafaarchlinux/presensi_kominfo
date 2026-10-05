<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kehadiran Pegawai - Pemkab Sragen</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: 'Times New Roman', Times, serif; color: #111; line-height: 1.3; font-size: 11pt; margin: 0; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 16px; }
        .kop h3 { margin: 0; font-size: 13pt; text-transform: uppercase; font-weight: bold; }
        .kop h2 { margin: 2px 0; font-size: 15pt; text-transform: uppercase; font-weight: bold; }
        .kop p { margin: 0; font-size: 9.5pt; font-style: italic; }
        .title { text-align: center; margin-bottom: 16px; }
        .title h4 { margin: 0; text-transform: uppercase; font-size: 12pt; text-decoration: underline; }
        .title span { font-size: 10pt; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10pt; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        .text-center { text-align: center; }
        .ttd-wrapper { margin-top: 40px; width: 100%; display: flex; justify-content: flex-end; }
        .ttd-box { width: 250px; text-align: center; float: right; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-family: sans-serif; font-size: 13px; color: #1e40af; font-weight: 600;">Pratinjau Dokumen Rekapitulasi Presensi Siap Cetak</span>
        <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 6px 16px; border-radius: 4px; font-weight: bold; cursor: pointer;">Cetak / Simpan PDF</button>
    </div>

    <!-- KOP RESMI PEMKAB SRAGEN -->
    <div class="kop">
        <h3>Pemerintah Kabupaten Sragen</h3>
        <h2>Dinas Komunikasi dan Informatika</h2>
        <p>Jl. Raya Sukowati No. 255 Sragen, Jawa Tengah | Telp: (0271) 891080 | Website: kominfo.sragenkab.go.id</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title">
        <h4>Laporan Rekapitulasi Presensi Biometrik Pegawai</h4>
        <span>Periode: {{ $periode }}</span>
    </div>

    <!-- TABEL REKAP -->
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th>Nama Pegawai & NIP</th>
                <th>Unit Kerja</th>
                <th>Jam Masuk</th>
                <th>Status Masuk</th>
                <th>Jam Pulang</th>
                <th>Status Pulang</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('DD/MM/Y') }}</td>
                    <td>
                        <b>{{ $item->pns->nama ?? '-' }}</b><br>
                        <small style="color: #444;">NIP: {{ $item->pns->nip ?? '-' }}</small>
                    </td>
                    <td>{{ $item->pns->unitKerja->nama_unit_kerja ?? '-' }}</td>
                    <td class="text-center">{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i:s') : '-' }}</td>
                    <td class="text-center">{{ $item->status ?? '-' }}</td>
                    <td class="text-center">{{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i:s') : '-' }}</td>
                    <td class="text-center">{{ $item->status_pulang ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data presensi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN PENGESAHAN -->
    <div class="ttd-wrapper" style="margin-top: 30px;">
        <div class="ttd-box">
            <p style="margin-bottom: 60px;">
                Sragen, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Kepala Dinas Kominfo Kab. Sragen
            </p>
            <p style="font-weight: bold; text-decoration: underline; margin-bottom: 0;">(....................................................)</p>
            <p style="margin-top: 0; font-size: 10pt;">NIP. ........................................</p>
        </div>
    </div>

</body>
</html>
