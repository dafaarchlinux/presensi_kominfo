<x-filament-panels::page>
    <style>
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .metric-card {
            background: #ffffff;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .dark .metric-card {
            background: #18181b;
            border-color: #27272a;
        }
        .metric-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
            transform: translateY(-2px);
        }
        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .metric-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .dark .metric-title {
            color: #a1a1aa;
        }
        .metric-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
        }
        .metric-value {
            font-size: 1.875rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }
        .dark .metric-value {
            color: #f4f4f5;
        }
        .metric-sub {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .dark .metric-sub {
            color: #a1a1aa;
        }
    </style>

    <!-- METRICS OVERVIEW -->
    <div class="metric-grid">
        
        <!-- CARD 1: HARI INI -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Kehadiran Hari Ini</span>
                <span class="metric-badge" style="background: #eff6ff; color: #2563eb;">Harian</span>
            </div>
            <div class="metric-value">
                {{ $totalHariIni }} <span style="font-size: 0.875rem; font-weight: 500; color: #94a3b8;">Pegawai</span>
            </div>
            <div class="metric-sub">
                <span style="color: #059669; font-weight: 700;">? Tepat: {{ $tepatWaktuHariIni }}</span>
                <span style="color: #e11d48; font-weight: 700;">! Lambat: {{ $terlambatHariIni }}</span>
            </div>
        </div>

        <!-- CARD 2: PEKAN INI -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Presensi Pekan Ini</span>
                <span class="metric-badge" style="background: #ecfdf5; color: #059669;">Mingguan</span>
            </div>
            <div class="metric-value">
                {{ $totalMingguIni }} <span style="font-size: 0.875rem; font-weight: 500; color: #94a3b8;">Catatan</span>
            </div>
            <div class="metric-sub">
                <span>Akumulasi kehadiran minggu ini</span>
            </div>
        </div>

        <!-- CARD 3: BULAN INI -->
        <div class="metric-card">
            <div class="metric-header">
                <span class="metric-title">Presensi Bulan Ini</span>
                <span class="metric-badge" style="background: #faf5ff; color: #7e22ce;">Bulanan</span>
            </div>
            <div class="metric-value">
                {{ $totalBulanIni }} <span style="font-size: 0.875rem; font-weight: 500; color: #94a3b8;">Presensi</span>
            </div>
            <div class="metric-sub">
                <span style="color: #059669; font-weight: 700;">Tingkat Disiplin: {{ $rasioDisiplin }}%</span>
            </div>
        </div>

    </div>

    <!-- TABEL UTAMA REKAPITULASI -->
    <div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
