<x-filament-panels::page>

    {{-- ================= CSRF ================= --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ================= CSS ================= --}}
    <link rel="stylesheet" href="{{ asset('css/face-attendance.css') }}">

    <div class="face-attendance-page">
        <div class="face-container">

            {{-- ================= LEFT : CAMERA ================= --}}
            <div class="card camera-card">
                <h2>Presensi Berbasis Wajah</h2>

                <p class="subtitle" id="status">
                    Pilih Absen Masuk atau Absen Pulang
                </p>

                <div id="alert" class="alert" style="display:none"></div>

                <div class="camera-box">
                    <video id="video" muted playsinline></video>
                    <div class="scan-border"></div>
                </div>

                <div class="attendance-actions">
                    <button id="btnMasuk" type="button" class="btn btn-primary">
                        Absen Masuk
                    </button>

                    <button id="btnPulang" type="button" class="btn btn-secondary">
                        Absen Pulang
                    </button>
                </div>

                <p class="subtitle center-text">
                    Kamera akan aktif setelah memilih jenis presensi
                </p>
            </div>

            {{-- ================= RIGHT : TABLE ================= --}}
            <div class="card">
                <h2>Presensi Hari Ini</h2>

                @if (empty($todayAttendances))
                    <p class="subtitle empty-text">
                        Belum ada presensi hari ini
                    </p>
                @else
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NIP</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todayAttendances as $absen)
                                    @php
                                        $statusMasuk  = $absen['raw_status'] ?? null;
                                        $statusPulang = $absen['status_pulang'] ?? null;
                                    @endphp

                                    <tr>
                                        <td>{{ $absen['nama'] }}</td>
                                        <td>{{ $absen['nip'] }}</td>
                                        <td>{{ $absen['jam_masuk'] ?? '-' }}</td>
                                        <td>{{ $absen['jam_pulang'] ?? '-' }}</td>
                                        <td>
                                            @if ($statusMasuk)
                                                <span class="badge {{ $statusMasuk === 'terlambat' ? 'badge-warning' : 'badge-success' }}">
                                                    {{ ucfirst($statusMasuk) }}
                                                </span>
                                            @endif

                                            @if ($statusPulang)
                                                <span class="badge badge-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $statusPulang)) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ================= JS ================= --}}
    <script src="{{ asset('face-api/face-api.min.js') }}" defer></script>
    <script src="{{ asset('attendance.js') }}" defer></script>

</x-filament-panels::page>
