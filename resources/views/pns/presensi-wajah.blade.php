<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Presensi Wajah PNS</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- IDENTITAS LOGIN (UNTUK JS) -->
    <meta name="pns-id-login" content="{{ auth('pns')->id() }}">
    <meta name="pns-nama-login" content="{{ auth('pns')->user()->nama }}">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/pns-presensi.css') }}">

    <!-- LEAFLET MAP -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<div class="app-container mobile-mode">

    <!-- ================= HEADER ================= -->
    <header class="mobile-header">
        <div class="user-info">
            <strong>{{ $pns->nama }}</strong>
            <small>NIP: {{ $pns->nip }}</small>
        </div>

        <div class="clock-box">
            <span id="clock">--:--:--</span>
            <small id="dateToday">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </small>
        </div>
    </header>

    <!-- ================= STATUS HARI INI ================= -->
    <section class="today-status card">
        <h4>Status Presensi Hari Ini</h4>

        @if(!$absensiHariIni)
            <p class="badge badge-gray">Belum presensi</p>
        @else
            <div class="status-row">
                <span>Masuk</span>
                <strong>{{ $absensiHariIni->jam_masuk ?? '-' }}</strong>
            </div>

            <div class="status-row">
                <span>Pulang</span>
                <strong>{{ $absensiHariIni->jam_pulang ?? '-' }}</strong>
            </div>

            <div class="status-row">
                <span>Status</span>
                <span class="badge 
                    {{ $absensiHariIni->status === 'terlambat' ? 'badge-warning' : 'badge-success' }}">
                    {{ ucfirst($absensiHariIni->status) }}
                </span>
            </div>
        @endif
    </section>

    <!-- ================= KAMERA ================= -->
    <section class="camera-section card">
        <h4>Verifikasi Wajah</h4>

        <div class="camera-wrapper">
            <video id="video" autoplay muted playsinline></video>
            <div class="camera-overlay">
                <span>Arahkan wajah ke kamera</span>
            </div>
        </div>

        <p id="status" class="status-text">
            Menunggu deteksi wajah...
        </p>
    </section>

    <!-- ================= STATUS LOKASI ================= -->
    <section class="location-status card">
        <h4>Status Lokasi</h4>
        <p id="locationText">Mengambil lokasi GPS...</p>
    </section>

    <!-- ================= MAP ================= -->
    <section class="map-section card">
        <h4>Peta Lokasi Presensi</h4>
        <div id="map"></div>
    </section>

    <!-- ================= BUTTON PRESENSI ================= -->
    <section class="action-section">
        <button id="btnMasuk" class="btn btn-primary">
            Absen Masuk
        </button>

        <button id="btnPulang" class="btn btn-success">
            Absen Pulang
        </button>
    </section>

    <!-- ================= ALERT ================= -->
    <section class="alert-section">
        <div id="alert" class="alert-box"></div>
    </section>

    <!-- ================= RIWAYAT PRESENSI ================= -->
    <section class="history-section card">
        <h4>Riwayat Presensi Saya</h4>

        <div class="history-list">
            @forelse($riwayatPresensi as $item)
                <div class="history-item">
                    <div class="date">
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                    </div>

                    <div class="time">
                        Masuk: {{ $item->jam_masuk ?? '-' }}<br>
                        Pulang: {{ $item->jam_pulang ?? '-' }}
                    </div>

                    <div class="status">
                        <span class="badge 
                            {{ $item->status === 'terlambat' ? 'badge-warning' : 'badge-success' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="empty">Belum ada riwayat presensi</p>
            @endforelse
        </div>
    </section>

    <!-- ================= INFO SISTEM ================= -->
    <section class="system-info">
        <small>
            Sistem Presensi Wajah PNS<br>
            Lokasi: Kantor Terpadu Pemda Sragen
        </small>
    </section>

    <!-- ================= LOGOUT ================= -->
    <section class="logout-section">
        <form method="POST" action="{{ route('pns.logout') }}">
            @csrf
            <button type="submit" class="btn btn-logout">
                Logout
            </button>
        </form>
    </section>

</div>

<!-- FACE API -->
<script src="{{ asset('face-api/face-api.min.js') }}"></script>

<!-- LEAFLET JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- JS PRESENSI -->
<script src="{{ asset('js/attendance-user.js') }}"></script>

<!-- JAM REALTIME -->
<script>
    setInterval(() => {
        const clock = document.getElementById('clock');
        if (clock) {
            clock.innerText = new Date().toLocaleTimeString('id-ID');
        }
    }, 1000);
</script>

</body>
</html>
