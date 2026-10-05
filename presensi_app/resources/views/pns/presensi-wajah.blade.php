<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Presensi Biometrik PNS | Pemerintah Kabupaten Sragen</title>

    <!-- Tailwind CSS & Plus Jakarta Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Face API JS Lokal (Instan) -->
    <script src="{{ asset('face-api/face-api.min.js') }}"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        .camera-mirror {
            transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
        }
    </style>
</head>
<body class="bg-slate-100 font-sans min-h-screen text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- TOPBAR RESMI INSTANSI -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-tight">E-Presensi Biometrik</h1>
                    <p class="text-[11px] font-medium text-slate-500">Pemerintah Kabupaten Sragen</p>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <div class="hidden sm:block text-right">
                    <span class="block text-xs font-bold text-slate-900">{{ $pns->nama }}</span>
                    <span class="block text-[11px] text-slate-500">NIP. {{ $pns->nip }}</span>
                </div>
                <form method="POST" action="{{ route('pns.logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6 space-y-5 sm:space-y-6">

        <!-- HEADER IDENTITAS PEGAWAI & SERVER TIME -->
        <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-5 sm:p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-blue-500/30 text-blue-200 text-[11px] font-semibold border border-blue-400/20 mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Akun Terverifikasi
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ $pns->nama }}</h2>
                <p class="text-xs text-blue-100 mt-1">
                    NIP: <span class="font-semibold text-white">{{ $pns->nip }}</span> &bull; Unit Kerja: <span class="font-semibold text-white">{{ $pns->unitKerja->nama_unit_kerja ?? 'Dinas Kominfo' }}</span>
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-4 py-3 text-center sm:text-right min-w-[200px]">
                <span class="text-[11px] text-blue-200 block font-medium uppercase tracking-wider">Waktu Presensi</span>
                <span id="realtime-clock" class="text-xl font-mono font-extrabold tracking-wider block my-0.5">--:--:-- WIB</span>
                <span class="text-[11px] text-blue-100 block">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </div>

        <!-- GRID UTAMA: STATUS PRESENSI & KAMERA SCANNER -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">

            <!-- SIDEBAR STATUS HARI INI -->
            <div class="lg:col-span-4 space-y-4">
                
                <!-- CARD STATUS HARI INI -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Hari Ini</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded">
                            {{ \Carbon\Carbon::today()->format('d M Y') }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <!-- Presensi Masuk -->
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-slate-500 block font-medium">Jam Masuk</span>
                                <span class="text-base font-bold text-slate-900 font-mono">
                                    {{ $absensiHariIni && $absensiHariIni->jam_masuk ? \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i:s') . ' WIB' : '--:--:--' }}
                                </span>
                            </div>
                            @if($absensiHariIni && $absensiHariIni->jam_masuk)
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $absensiHariIni->status === 'Tepat Waktu' || $absensiHariIni->status === 'Lebih Awal' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $absensiHariIni->status }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-200 text-slate-600">Belum Ada</span>
                            @endif
                        </div>

                        <!-- Presensi Pulang -->
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-slate-500 block font-medium">Jam Pulang</span>
                                <span class="text-base font-bold text-slate-900 font-mono">
                                    {{ $absensiHariIni && $absensiHariIni->jam_pulang ? \Carbon\Carbon::parse($absensiHariIni->jam_pulang)->format('H:i:s') . ' WIB' : '--:--:--' }}
                                </span>
                            </div>
                            @if($absensiHariIni && $absensiHariIni->jam_pulang)
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $absensiHariIni->status_pulang === 'Tepat Waktu' || $absensiHariIni->status_pulang === 'Lembur' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $absensiHariIni->status_pulang ?? 'Selesai' }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-200 text-slate-600">Belum Ada</span>
                            @endif
                        </div>
                    </div>

                    <!-- ATURAN JAM KERJA -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100 text-[11px] text-slate-500 space-y-1.5">
                        <div class="flex justify-between">
                            <span>Jam Masuk Dinas:</span>
                            <span class="font-bold text-slate-700">07:00 - 08:00 WIB</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jam Pulang Dinas:</span>
                            <span class="font-bold text-slate-700">15:00 - 16:00 WIB</span>
                        </div>
                    </div>
                </div>

                <!-- STATUS BIOMETRIK WAJAH -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-xs">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full {{ !empty($pns->face_embedding) ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        <span class="font-bold text-slate-800">Status Data Wajah:</span>
                    </div>
                    @if(!empty($pns->face_embedding))
                        <p class="text-emerald-700 font-medium">Wajah Anda telah terdaftar dan siap diverifikasi oleh sistem AI.</p>
                    @else
                        <p class="text-rose-600 font-medium">Data wajah belum terdaftar. Silakan hubungi Administrator untuk pendaftaran biometrik.</p>
                    @endif
                </div>

            </div>

            <!-- SCANNER KAMERA BIOMETRIK WAJAH -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    
                    <div class="flex items-center justify-between mb-3.5">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Pemindai Wajah Real-Time</h3>
                            <p class="text-xs text-slate-500">Posisikan wajah Anda tepat di depan kamera</p>
                        </div>
                        <span id="camera-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span id="camera-badge-text">Memuat Cepat AI...</span>
                        </span>
                    </div>

                    <!-- VIEWPORT KAMERA -->
                    <div class="relative w-full aspect-[4/3] sm:aspect-video bg-slate-950 rounded-2xl overflow-hidden border-2 border-slate-800 shadow-inner flex items-center justify-center">
                        <video id="webcam" autoplay muted playsinline class="w-full h-full object-cover camera-mirror"></video>
                        <canvas id="face-canvas" class="absolute inset-0 w-full h-full pointer-events-none camera-mirror"></canvas>
                        
                        <!-- FRAME TARGET WAJAH -->
                        <div id="face-frame" class="absolute inset-0 pointer-events-none flex items-center justify-center transition-all duration-300">
                            <div class="w-44 sm:w-56 h-56 sm:h-72 border-2 border-dashed border-sky-400/70 rounded-3xl animate-pulse"></div>
                        </div>

                        <!-- TOAST FEEDBACK VERIFIKASI -->
                        <div id="detection-toast" class="absolute bottom-4 left-4 right-4 bg-slate-900/95 backdrop-blur-md text-white px-4 py-2.5 rounded-xl text-xs font-semibold text-center border border-white/10 shadow-lg">
                            Menghubungkan kamera...
                        </div>
                    </div>

                    <!-- TOMBOL AKSI PRESENSI (MASUK & PULANG) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                        <button 
                            type="button" 
                            id="btn-masuk"
                            onclick="submitPresensi('masuk')"
                            disabled
                            class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 px-4 rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            <span>Presensi Masuk</span>
                        </button>

                        <button 
                            type="button" 
                            id="btn-pulang"
                            onclick="submitPresensi('pulang')"
                            disabled
                            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 px-4 rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Presensi Pulang</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <!-- TABEL RIWAYAT PRESENSI -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Presensi Saya</h3>
                    <p class="text-xs text-slate-500">Catatan riwayat kehadiran individual pegawai</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">
                    Total: {{ count($riwayatPresensi) }} Data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-y border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Jam Masuk</th>
                            <th class="py-3 px-4">Status Masuk</th>
                            <th class="py-3 px-4">Jam Pulang</th>
                            <th class="py-3 px-4">Status Pulang</th>
                            <th class="py-3 px-4">Metode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($riwayatPresensi as $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd, D MMMM Y') }}
                                </td>
                                <td class="py-3 px-4 font-mono font-medium text-slate-800">
                                    {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i:s') . ' WIB' : '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($item->status === 'Tepat Waktu' || $item->status === 'Lebih Awal')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $item->status }}
                                        </span>
                                    @elseif($item->status === 'Terlambat')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ $item->status }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono font-medium text-slate-800">
                                    {{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i:s') . ' WIB' : '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($item->status_pulang === 'Tepat Waktu' || $item->status_pulang === 'Lembur')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $item->status_pulang }}
                                        </span>
                                    @elseif($item->status_pulang === 'Pulang Cepat')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ $item->status_pulang }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                        {{ $item->metode ?? 'Face Recognition' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada data riwayat presensi yang terekam.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- LOGIKA AI FACE RECOGNITION SUPER CEPAT & AKURAT -->
    <script>
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('realtime-clock');
            if (el) el.innerText = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        const video = document.getElementById('webcam');
        const cameraBadge = document.getElementById('camera-badge');
        const cameraBadgeText = document.getElementById('camera-badge-text');
        const toast = document.getElementById('detection-toast');
        const btnMasuk = document.getElementById('btn-masuk');
        const btnPulang = document.getElementById('btn-pulang');
        
        const currentPnsId = {{ $pns->id }};
        const currentPnsNama = "{{ $pns->nama }}";
        const storedEmbedding = @json($pns->face_embedding);

        let faceMatcher = null;
        let isFaceVerified = false;
        let isProcessing = false;

        const LOCAL_MODEL_URL = "{{ asset('face-api/models') }}";

        async function initBiometricSystem() {
            try {
                // Jalankan kamera terlebih dahulu secara paralel agar instan
                const cameraPromise = navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
                    audio: false
                });

                // Muat model Tiny Face yang sangat ringan & cepat (~190KB)
                const modelPromise = Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(LOCAL_MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(LOCAL_MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(LOCAL_MODEL_URL)
                ]);

                const [stream, _] = await Promise.all([cameraPromise, modelPromise]);

                // Inisialisasi Matcher PNS yang login
                if (storedEmbedding && Array.isArray(storedEmbedding) && storedEmbedding.length > 0) {
                    const descriptors = storedEmbedding.map(d => new Float32Array(Object.values(d)));
                    const labeledDescriptor = new faceapi.LabeledFaceDescriptors(currentPnsNama, descriptors);
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptor, 0.55);
                }

                video.srcObject = stream;
                video.onloadedmetadata = () => {
                    video.play();
                    cameraBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200";
                    cameraBadgeText.innerText = "Kamera Siap";
                    startFaceDetectionLoop();
                };

            } catch (err) {
                console.error("System Init Error:", err);
                cameraBadge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200";
                cameraBadgeText.innerText = "Kamera Tidak Terdeteksi";
                toast.className = "absolute bottom-4 left-4 right-4 bg-rose-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                toast.innerHTML = `Mohon izinkan akses kamera di peramban (browser) Anda.`;
            }
        }

        async function startFaceDetectionLoop() {
            const canvas = document.getElementById('face-canvas');
            const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
            faceapi.matchDimensions(canvas, displaySize);

            // Pengaturan TinyFaceDetector ringan
            const detectorOptions = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });

            setInterval(async () => {
                if (video.paused || video.ended || isProcessing) return;

                const detection = await faceapi.detectSingleFace(video, detectorOptions)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    if (faceMatcher) {
                        const match = faceMatcher.findBestMatch(detection.descriptor);
                        
                        if (match.label !== 'unknown') {
                            isFaceVerified = true;
                            btnMasuk.disabled = false;
                            btnPulang.disabled = false;
                            
                            const confidence = Math.max(0, Math.round((1 - match.distance) * 100));
                            toast.className = "absolute bottom-4 left-4 right-4 bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                            toast.innerHTML = `? Wajah Teridentifikasi: <b>${match.label}</b> (Akurasi: ${confidence}%)`;
                        } else {
                            isFaceVerified = false;
                            btnMasuk.disabled = true;
                            btnPulang.disabled = true;
                            
                            toast.className = "absolute bottom-4 left-4 right-4 bg-rose-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                            toast.innerHTML = `? Wajah Tidak Dikenali! Pastikan pegawai yang bersangkutan adalah <b>${currentPnsNama}</b>.`;
                        }
                    } else {
                        // Fallback jika embedding belum diset
                        isFaceVerified = true;
                        btnMasuk.disabled = false;
                        btnPulang.disabled = false;
                        
                        toast.className = "absolute bottom-4 left-4 right-4 bg-sky-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                        toast.innerHTML = `Wajah Terdeteksi. Silakan tekan tombol Presensi.`;
                    }
                } else {
                    isFaceVerified = false;
                    btnMasuk.disabled = true;
                    btnPulang.disabled = true;
                    
                    toast.className = "absolute bottom-4 left-4 right-4 bg-slate-900/90 backdrop-blur-md text-white px-4 py-2.5 rounded-xl text-xs font-semibold text-center border border-white/10 block";
                    toast.innerHTML = `Posisikan wajah Anda di depan kamera...`;
                }
            }, 250);
        }

        async function submitPresensi(mode) {
            if (!isFaceVerified || isProcessing) return;
            isProcessing = true;

            btnMasuk.disabled = true;
            btnPulang.disabled = true;

            toast.className = "absolute bottom-4 left-4 right-4 bg-blue-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
            toast.innerHTML = `Memverifikasi & Menyimpan Presensi <b>${mode.toUpperCase()}</b>...`;

            try {
                const response = await fetch('/api/simpan-presensi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        pns_id: currentPnsId,
                        mode: mode
                    })
                });

                const res = await response.json();

                if (res.success) {
                    toast.className = "absolute bottom-4 left-4 right-4 bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                    toast.innerHTML = `? ${res.message} (${res.waktu})`;
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    toast.className = "absolute bottom-4 left-4 right-4 bg-rose-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                    toast.innerHTML = `! ${res.message}`;
                    setTimeout(() => {
                        isProcessing = false;
                    }, 3000);
                }
            } catch (err) {
                console.error("Presensi API Error:", err);
                toast.className = "absolute bottom-4 left-4 right-4 bg-rose-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold text-center shadow-lg block";
                toast.innerHTML = `Gagal mengirim data presensi ke server.`;
                isProcessing = false;
            }
        }

        window.addEventListener('DOMContentLoaded', initBiometricSystem);
    </script>
</body>
</html>
