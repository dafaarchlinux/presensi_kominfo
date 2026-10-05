<x-filament::page>
    <link rel="stylesheet" href="{{ asset('css/daftar-wajah.css') }}">

    <div
        id="face-container"
        data-pns-id="{{ $record->id }}"
        wire:ignore
        class="face-wrapper"
    >
        <!-- KIRI: KAMERA & STATUS -->
        <div class="face-left">
            <div>
                <h2 class="title">Registrasi Biometrik Wajah Pegawai</h2>
                <p class="subtitle">
                    Sistem otomatis merekam 3 orientasi wajah untuk akurasi verifikasi kehadiran
                </p>

                <div id="status" class="status-box">
                    Menyiapkan modul kamera dan kecerdasan buatan…
                </div>

                <div id="alert" class="alert-box" style="display:none"></div>
            </div>

            <div class="camera-box" style="position: relative; overflow: hidden; border-radius: 12px;">
                <video id="video" autoplay muted playsinline style="transform: scaleX(-1); width: 100%; height: auto;"></video>
                <canvas id="overlay" style="position: absolute; top: 0; left: 0; transform: scaleX(-1); width: 100%; height: 100%;"></canvas>
            </div>
        </div>

        <!-- KANAN: INDIKATOR PROGRES SUDUT -->
        <div class="face-right">
            <h3 class="title-small">Tahapan Perekaman Wajah</h3>
            
            <div class="preview-grid" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                <div id="step-0" class="step-indicator active">
                    1. Posisi Tampak Depan
                </div>
                <div id="step-1" class="step-indicator">
                    2. Posisi Miring Kiri
                </div>
                <div id="step-2" class="step-indicator">
                    3. Posisi Miring Kanan
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('face-api/face-api.min.js') }}"></script>
        <script src="{{ asset('js/ambilWajah.js') }}"></script>
    @endpush
</x-filament::page>
