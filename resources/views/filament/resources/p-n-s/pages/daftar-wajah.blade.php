<x-filament::page>
    <link rel="stylesheet" href="{{ asset('css/daftar-wajah.css') }}">

    <div
        id="face-container"
        data-pns-id="{{ $record->id }}"
        wire:ignore
        class="face-wrapper"
    >

        <!-- KIRI -->
        <div class="face-left">

            <h2 class="title">Pendaftaran Wajah</h2>
            <p class="subtitle">
                Ambil maksimal 3 wajah untuk menjaga akurasi
            </p>

            <div id="status" class="status-box">
                Menunggu kamera…
            </div>

            <div id="alert" class="alert-box" style="display:none"></div>

            <div class="camera-box">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="overlay"></canvas>
            </div>

            <button
                type="button"
                class="btn-ambil"
                onclick="ambilWajah()"
            >
                📷 Ambil Wajah
            </button>
        </div>

        <!-- KANAN -->
        <div class="face-right">
            <h3 class="title-small">Wajah Terdeteksi (Max 3)</h3>

            <div id="preview" class="preview-grid"></div>
        </div>

    </div>

    @push('scripts')
        <script src="{{ asset('face-api/face-api.min.js') }}"></script>
        <script src="{{ asset('ambilWajah.js') }}"></script>
    @endpush
</x-filament::page>
