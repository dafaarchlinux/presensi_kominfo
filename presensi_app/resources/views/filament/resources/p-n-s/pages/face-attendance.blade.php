<x-filament-panels::page>
    <div style="font-family: 'Plus Jakarta Sans', sans-serif; display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem; align-items: start;">
        
        <!-- KOLOM KIRI: KAMERA & NOTIFIKASI LAYAR -->
        <div style="background: #ffffff; padding: 1.75rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Terminal Face ID</h2>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Sistem Verifikasi Kehadiran Pegawai Otomatis</p>
                </div>
            </div>

            <!-- TOMBOL PILIHAN MODE ABSEN MASUK & PULANG -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <button type="button" id="btn-mode-masuk" onclick="window.gantiModePresensi('masuk')" style="padding: 0.85rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; border: 2px solid #16a34a; background: #16a34a; color: #ffffff; transition: all 0.2s; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);">
                    🟢 ABSEN MASUK
                </button>
                <button type="button" id="btn-mode-pulang" onclick="window.gantiModePresensi('pulang')" style="padding: 0.85rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; border: 2px solid #cbd5e1; background: #f8fafc; color: #64748b; transition: all 0.2s;">
                    🔴 ABSEN PULANG
                </button>
            </div>

            <!-- KOTAK PEMBERITAHUAN UTAMA DI LAYAR -->
            <div id="screen-notification" style="background: #0f172a; color: #ffffff; padding: 1.25rem; border-radius: 12px; font-size: 1rem; font-weight: 600; text-align: center; margin-bottom: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                Memuat Sistem AI & Menunggu Wajah...
            </div>

            <div style="position: relative; width: 100%; aspect-ratio: 16/10; background: #0f172a; border-radius: 12px; overflow: hidden; box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);">
                <video id="video-attendance" autoplay muted playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;"></video>
                <canvas id="overlay-attendance" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;"></canvas>
            </div>
        </div>

        <!-- KOLOM KANAN: PANEL INFORMASI DETAIL -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            
            <div style="background: #ffffff; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.75rem;">Status Mode Presensi</h3>
                <div id="info-current-mode" style="font-size: 0.95rem; font-weight: 700; color: #16a34a; background: #f0fdf4; padding: 0.85rem; border-radius: 10px; border: 1px solid #bbf7d0; text-align: center;">
                    Mode Aktif: ABSEN MASUK
                </div>
            </div>

            <div style="background: #ffffff; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.75rem;">Status Kehadiran</h3>
                <div id="info-status-detail" style="font-size: 0.9rem; color: #475569; background: #f8fafc; padding: 0.85rem; border-radius: 10px; border: 1px solid #e2e8f0; line-height: 1.4;">
                    Belum ada data presensi terekam sesi ini.
                </div>
            </div>

            <div style="background: #ffffff; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                <h3 style="font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 0.75rem;">Identitas Terdeteksi</h3>
                <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.9rem;">
                    <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 0.75rem; color: #64748b; display: block; text-transform:uppercase; font-weight: 600;">Nama & NIP</span>
                        <span id="info-nama" style="font-weight: 700; color: #0f172a;">-</span>
                    </div>
                    <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 0.75rem; color: #64748b; display: block; text-transform:uppercase; font-weight: 600;">Keterangan Waktu</span>
                        <span id="info-keterangan" style="font-weight: 700; color: #0f172a;">-</span>
                    </div>
                </div>
            </div>

            <div style="background: #ffffff; padding: 1.25rem 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.75rem; color: #64748b; display: block; text-transform: uppercase; font-weight: 600;">Waktu Server</span>
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">{{ now()->format('d M Y') }}</span>
                </div>
                <div id="live-clock" style="font-weight: 800; color: #2563eb; font-size: 1.1rem; background: #eff6ff; padding: 0.4rem 0.75rem; border-radius: 8px;">
                    {{ now()->format('H:i:s') }}
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('face-api/face-api.min.js') }}"></script>
        <script src="{{ asset('js/faceAttendance.js') }}"></script>
        <script>
            setInterval(() => {
                const now = new Date();
                const clock = document.getElementById('live-clock');
                if (clock) clock.innerText = now.toTimeString().split(' ')[0];
            }, 1000);
        </script>
    @endpush
</x-filament-panels::page>