@extends('template.index')

@section('title', 'Dashboard Admin | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- ----------------------------------------------------
         1. HERO WELCOME BANNER
    ---------------------------------------------------- -->
    <div class="welcome-hero-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="hero-icon-box">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                    <div>
                        <h2 class="hero-title mb-1">Welcome back, {{ auth()->user()->name ?? 'Administrator' }}!</h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="{{ route('admin.kasir.index') }}" class="btn btn-hero-pill btn-hero-solid">
                        <i class="bi bi-shop me-1"></i> Buka POS Kasir
                    </a>
                    <a href="{{ route('admin.laporan.penjualan') }}" class="btn btn-hero-pill">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Lap. Penjualan
                    </a>
                    <a href="{{ route('admin.laporan.laba-rugi') }}" class="btn btn-hero-pill">
                        <i class="bi bi-cash-stack me-1"></i> Laba &amp; Rugi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ----------------------------------------------------
         2. FOUR METRIC KPI CARDS
    ---------------------------------------------------- -->
    <div class="row g-3 g-xl-4 mb-4">
        
        <!-- CARD 1: JUMLAH PENJUALAN FILM PER BULAN -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-emerald-light text-emerald">
                        <i class="bi bi-camera-reels-fill"></i>
                    </div>
                    <span class="badge {{ $filmSalesGrowth >= 0 ? 'bg-emerald-subtle text-emerald' : 'bg-rose-subtle text-rose' }} rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        <i class="bi {{ $filmSalesGrowth >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                        {{ $filmSalesGrowth >= 0 ? '+' : '' }}{{ $filmSalesGrowth }}%
                    </span>
                </div>
                <div class="metric-label mb-3">PENJUALAN FILM PER BULAN</div>
                <div class="metric-value  mb-2">{{ number_format($jumlahPenjualanFilm) }} <span class="fs-6 fw-semibold text-muted">Tiket</span></div>
                <div class="small text-muted mb-3 d-flex justify-content-between align-items-center">
                    <span>{{ number_format($totalPenontonFilm) }} Penonton</span>
                    <strong class="text-dark">Rp {{ number_format($nominalPenjualanFilm, 0, ',', '.') }}</strong>
                </div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-emerald" style="width: {{ min(100, max(15, $jumlahPenjualanFilm * 4)) }}%;"></div>
                </div>
            </div>
        </div>

        <!-- CARD 2: JUMLAH BARANG YANG ADA DI CAFE -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-blue-light text-blue">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <span class="badge bg-blue-subtle text-blue rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        <i class="bi bi-tag-fill me-1"></i>{{ $jumlahKategoriCafe }} Kategori
                    </span>
                </div>
                <div class="metric-label mb-3">JUMLAH BARANG</div>
                <div class="metric-value  mb-2">{{ number_format($jumlahBarangCafe) }} <span class="fs-6 fw-semibold text-muted">Produk</span></div>
                <div class="small text-muted mb-3 d-flex justify-content-between align-items-center">
                    <span>Stok Fisik:</span>
                    <strong class="text-dark">{{ number_format($totalStokFisik) }} Unit</strong>
                </div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-blue" style="width: {{ min(100, max(20, $jumlahBarangCafe * 6)) }}%;"></div>
                </div>
            </div>
        </div>

        <!-- CARD 3: LABA PERBULAN -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-emerald-light text-emerald">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="badge {{ $labaGrowth >= 0 ? 'bg-emerald-subtle text-emerald' : 'bg-rose-subtle text-rose' }} rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        <i class="bi {{ $labaGrowth >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                        {{ $labaGrowth >= 0 ? '+' : '' }}{{ $labaGrowth }}%
                    </span>
                </div>
                <div class="metric-label mb-3">LABA BERSIH PER BULAN</div>
                <div class="metric-value text-emerald mb-2">Rp {{ number_format($labaBulanIni, 0, ',', '.') }}</div>
                <div class="small text-muted mb-3 d-flex justify-content-between align-items-center">
                    <span>Profit:</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2">Bulan Berjalan</span>
                </div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-emerald" style="width: {{ min(100, max(25, $labaBulanIni > 0 ? 75 : 10)) }}%;"></div>
                </div>
            </div>
        </div>

        <!-- CARD 4: PENGELUARAN PERBULAN -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-rose-light text-rose">
                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                    </div>
                    <span class="badge {{ $pengeluaranGrowth <= 0 ? 'bg-emerald-subtle text-emerald' : 'bg-rose-subtle text-rose' }} rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        <i class="bi {{ $pengeluaranGrowth <= 0 ? 'bi-arrow-down-short' : 'bi-arrow-up-short' }}"></i>
                        {{ $pengeluaranGrowth >= 0 ? '+' : '' }}{{ $pengeluaranGrowth }}%
                    </span>
                </div>
                <div class="metric-label mb-3">PENGELUARAN PER BULAN</div>
                <div class="metric-value text-rose mb-2">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</div>
                <div class="small text-muted mb-3 d-flex justify-content-between align-items-center">
                    <span>Operasional:</span>
                    <strong class="text-dark">{{ $pengeluaranBulanIniCount }} Catatan</strong>
                </div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-rose" style="width: {{ min(100, max(20, $pengeluaranBulanIniCount * 8)) }}%;"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- ----------------------------------------------------
         3. SECTION: SALES STATISTICS AREA CHART (OUTLET A vs OUTLET B)
    ---------------------------------------------------- -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden content-panel-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box bg-primary-subtle text-primary">
                    <i class="bi bi-activity"></i>
                </div>
                <div>
                    <h5 class="panel-card-title mb-0">Sales Statistics</h5>
                </div>
            </div>

            <!-- Tombol Pemilih Periode: Perhari, Perminggu, Perbulan -->
            <div class="btn-group panel-period-pill" role="group" aria-label="Periode Chart">
                <button type="button" class="btn btn-period" id="btnPeriodDaily" onclick="setChartPeriod('perhari')">
                    <i class="bi bi-calendar-day me-1"></i>Perhari
                </button>
                <button type="button" class="btn btn-period" id="btnPeriodWeekly" onclick="setChartPeriod('perminggu')">
                    <i class="bi bi-calendar-week me-1"></i>Perminggu
                </button>
                <button type="button" class="btn btn-period active" id="btnPeriodMonthly" onclick="setChartPeriod('perbulan')">
                    <i class="bi bi-calendar-month me-1"></i>Perbulan
                </button>
            </div>
        </div>

        <!-- Summary Bar & Legenda Outlet -->
        <div class="row g-3 align-items-center mb-3 p-3 rounded-4 bg-light-subtle border">
            <div class="col-12 col-md-4">
                <span class="text-muted small d-block" id="chartPeriodLabel">Total Tahun {{ date('Y') }}:</span>
                <div class="panel-stat-number text-primary" id="chartTotalSummary">
                    {{ $chartData['perbulan']['total_formatted'] }}
                </div>
            </div>

            <div class="col-12 col-md-8">
                <div class="d-flex flex-wrap justify-content-md-end gap-3 gap-lg-4">
                    <!-- Legenda Garis 1: Outlet A -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="outlet-legend-dot bg-brand-primary shadow-xs"></span>
                        <div>
                            <strong class="text-heading small d-block">{{ $namaOutletA }}</strong>
                            <span class="fw-bold text-primary font-monospace" id="chartOutletASummary">
                                {{ $chartData['perbulan']['total_a_formatted'] }}
                            </span>
                        </div>
                    </div>

                    <div class="vr d-none d-md-block opacity-25"></div>

                    <!-- Legenda Garis 2: Outlet B -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="outlet-legend-dot bg-amber shadow-xs"></span>
                        <div>
                            <strong class="text-heading small d-block">{{ $namaOutletB }}</strong>
                            <span class="fw-bold text-amber font-monospace" id="chartOutletBSummary">
                                {{ $chartData['perbulan']['total_b_formatted'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas Chart.js Responsive Area Chart -->
        <div class="chart-wrapper position-relative" style="height: 340px; width: 100%;">
            <canvas id="salesStatisticsChart"></canvas>
        </div>
    </div>

    <!-- ----------------------------------------------------
         4. SECTION LIST TOP 5 FILM TERLARIS: OUTLET A & OUTLET B
    ---------------------------------------------------- -->
    <div class="row g-3 g-xl-4">
        
        <!-- SEBELAH KIRI: TOP 5 FILM TERLARIS OUTLET A -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white content-panel-card p-0 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="hero-icon-small bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-trophy-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="panel-card-title mb-0 fs-6">Top 5 Film Terlaris</h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 font-sm">
                                    Outlet A
                                </span>
                            </div>
                            <span class="text-muted small" style="font-size: 0.78rem;">{{ $namaOutletA }}</span>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                        Peringkat Cabang
                    </span>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="list-group list-group-flush gap-3">
                        @forelse($topFilmsOutletA as $filmItem)
                            <div class="top-film-item p-3 rounded-4 border bg-light-subtle d-flex flex-column gap-2 transition-all">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Ranking Badge -->
                                    <div class="film-rank-badge rank-{{ $filmItem['rank'] }}">
                                        #{{ $filmItem['rank'] }}
                                    </div>

                                    <!-- Film Poster Thumbnail -->
                                    <div class="film-poster-thumb rounded-3 overflow-hidden flex-shrink-0 border shadow-xs" style="width: 48px; height: 64px;">
                                        <img src="{{ $filmItem['poster_url'] }}" alt="{{ $filmItem['judul'] }}" class="w-100 h-100 object-fit-cover" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'64\' fill=\'%23cbd5e1\'><rect width=\'48\' height=\'64\' fill=\'%23f1f5f9\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'10\' fill=\'%2394a3b8\'>FILM</text></svg>'">
                                    </div>

                                    <!-- Detail Film -->
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <h6 class="fw-bold mb-0 text-heading text-truncate fs-6" title="{{ $filmItem['judul'] }}">
                                                {{ $filmItem['judul'] }}
                                            </h6>
                                            <span class="badge bg-white text-dark border font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                                {{ $filmItem['usia_rating'] }}
                                            </span>
                                        </div>

                                        <div class="text-muted small d-flex flex-wrap align-items-center gap-2 mb-1" style="font-size: 0.75rem;">
                                            <span><i class="bi bi-film me-1"></i>{{ $filmItem['genres'] }}</span>
                                            <span>&bull;</span>
                                            <span><i class="bi bi-clock me-1"></i>{{ $filmItem['durasi'] }}</span>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                                <i class="bi bi-people-fill me-1"></i>{{ $filmItem['total_tayang'] }}x Ditonton ({{ $filmItem['total_penonton'] }} Penonton)
                                            </span>
                                            <strong class="text-primary font-monospace fs-6">
                                                {{ $filmItem['total_omset_formatted'] }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Popularity Bar -->
                                <div class="progress" style="height: 4px; border-radius: 999px;">
                                    <div class="progress-bar bg-brand-primary" role="progressbar" style="width: {{ $filmItem['popularity_percent'] }}%;" aria-valuenow="{{ $filmItem['popularity_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-film fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada riwayat transaksi film di outlet ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- SEBELAH KANAN: TOP 5 FILM TERLARIS OUTLET B -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white content-panel-card p-0 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="hero-icon-small bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-trophy-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="panel-card-title mb-0 fs-6">Top 5 Film Terlaris</h5>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5 font-sm">
                                    Outlet B
                                </span>
                            </div>
                            <span class="text-muted small" style="font-size: 0.78rem;">{{ $namaOutletB }}</span>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                        Peringkat Cabang
                    </span>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="list-group list-group-flush gap-3">
                        @forelse($topFilmsOutletB as $filmItem)
                            <div class="top-film-item p-3 rounded-4 border bg-light-subtle d-flex flex-column gap-2 transition-all">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Ranking Badge -->
                                    <div class="film-rank-badge rank-{{ $filmItem['rank'] }}">
                                        #{{ $filmItem['rank'] }}
                                    </div>

                                    <!-- Film Poster Thumbnail -->
                                    <div class="film-poster-thumb rounded-3 overflow-hidden flex-shrink-0 border shadow-xs" style="width: 48px; height: 64px;">
                                        <img src="{{ $filmItem['poster_url'] }}" alt="{{ $filmItem['judul'] }}" class="w-100 h-100 object-fit-cover" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'64\' fill=\'%23cbd5e1\'><rect width=\'48\' height=\'64\' fill=\'%23f1f5f9\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'10\' fill=\'%2394a3b8\'>FILM</text></svg>'">
                                    </div>

                                    <!-- Detail Film -->
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <h6 class="fw-bold mb-0 text-heading text-truncate fs-6" title="{{ $filmItem['judul'] }}">
                                                {{ $filmItem['judul'] }}
                                            </h6>
                                            <span class="badge bg-white text-dark border font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                                {{ $filmItem['usia_rating'] }}
                                            </span>
                                        </div>

                                        <div class="text-muted small d-flex flex-wrap align-items-center gap-2 mb-1" style="font-size: 0.75rem;">
                                            <span><i class="bi bi-film me-1"></i>{{ $filmItem['genres'] }}</span>
                                            <span>&bull;</span>
                                            <span><i class="bi bi-clock me-1"></i>{{ $filmItem['durasi'] }}</span>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                                <i class="bi bi-people-fill me-1"></i>{{ $filmItem['total_tayang'] }}x Ditonton ({{ $filmItem['total_penonton'] }} Penonton)
                                            </span>
                                            <strong class="text-amber font-monospace fs-6">
                                                {{ $filmItem['total_omset_formatted'] }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Popularity Bar -->
                                <div class="progress" style="height: 4px; border-radius: 999px;">
                                    <div class="progress-bar bg-amber" role="progressbar" style="width: {{ $filmItem['popularity_percent'] }}%;" aria-valuenow="{{ $filmItem['popularity_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-film fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada riwayat transaksi film di outlet ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@section('styles')
<style>
    /* Welcome Green Card */
    .welcome-hero-card {
        background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
        border-radius: 20px;
        padding: 26px 30px;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 123, 139, 0.35);
        position: relative;
        overflow: hidden;
    }

    .welcome-hero-card::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .hero-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 1.55rem;
    }

    .btn-hero-pill {
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-hero-pill:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    .btn-hero-solid {
        background: #ffffff !important;
        color: var(--brand-primary) !important;
        border-color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .btn-hero-solid:hover {
        background: #f8fafc !important;
        color: var(--brand-primary-dark) !important;
    }

    /* Metric Cards */
    .metric-card {
        background: var(--bg-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        padding: 22px 24px;
        box-shadow: var(--card-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
    }

    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .metric-value {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--text-heading);
        line-height: 1.2;
    }

    .metric-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: var(--text-muted);
    }

    .metric-progress-wrapper {
        height: 4px;
        width: 100%;
        background-color: rgba(148, 163, 184, 0.15);
        border-radius: 9999px;
        overflow: hidden;
    }

    .metric-progress-bar {
        height: 100%;
        border-radius: 9999px;
    }

    /* Color Utilities */
    .bg-brand-primary { background-color: var(--brand-primary, #007B8B) !important; }
    .bg-emerald { background-color: #10b981 !important; }
    .text-emerald { color: #10b981 !important; }
    .bg-emerald-light { background-color: rgba(16, 185, 129, 0.12) !important; }
    .bg-emerald-subtle { background-color: rgba(16, 185, 129, 0.14) !important; }

    .bg-blue { background-color: #3b82f6 !important; }
    .text-blue { color: #3b82f6 !important; }
    .bg-blue-light { background-color: rgba(59, 130, 246, 0.12) !important; }
    .bg-blue-subtle { background-color: rgba(59, 130, 246, 0.14) !important; }

    .bg-rose { background-color: #f43f5e !important; }
    .text-rose { color: #f43f5e !important; }
    .bg-rose-light { background-color: rgba(244, 63, 94, 0.12) !important; }
    .bg-rose-subtle { background-color: rgba(244, 63, 94, 0.14) !important; }

    .bg-amber { background-color: #f59e0b !important; }
    .text-amber { color: #f59e0b !important; }
    .bg-amber-light { background-color: rgba(245, 158, 11, 0.12) !important; }

    .text-heading { color: var(--text-heading) !important; }

    /* Content Panels */
    .content-panel-card {
        background: var(--bg-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        padding: 24px 28px;
        box-shadow: var(--card-shadow);
    }

    .panel-card-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-heading);
    }

    .panel-period-pill {
        background: rgba(148, 163, 184, 0.12);
        border-radius: 10px;
        padding: 3px;
    }

    .btn-period {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 5px 14px;
        border: none;
        color: var(--text-muted);
        border-radius: 8px !important;
        transition: all 0.2s ease;
    }

    .btn-period.active {
        background: var(--brand-primary, #007B8B);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 123, 139, 0.25);
    }

    .panel-stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .outlet-legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Top Film Styling */
    .film-rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        flex-shrink: 0;
        border: 1px solid var(--border-color);
        background: #f8fafc;
        color: var(--text-muted);
    }
    .film-rank-badge.rank-1 {
        background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
        color: #ffffff;
        border-color: #d97706;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
    }
    .film-rank-badge.rank-2 {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        color: #ffffff;
        border-color: #64748b;
    }
    .film-rank-badge.rank-3 {
        background: linear-gradient(135deg, #f97316 0%, #c2410c 100%);
        color: #ffffff;
        border-color: #c2410c;
    }

    .top-film-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .top-film-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
        border-color: var(--brand-primary, #007B8B) !important;
    }
</style>
@endsection

@section('scripts')
<!-- Chart.js Library via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data series yang dipasok dari Controller
    const chartPayload = @json($chartData);
    const namaOutletA = @json($namaOutletA);
    const namaOutletB = @json($namaOutletB);

    let currentPeriod = 'perbulan';
    let salesChart = null;

    const ctx = document.getElementById('salesStatisticsChart');
    if (!ctx) return;

    // Helper formatting Rupiah
    function formatRupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    // Inisialisasi Chart.js dengan 2 Garis (Outlet A & Outlet B)
    function initChart(period) {
        const data = chartPayload[period];
        if (!data) return;

        // Update Summary Text
        document.getElementById('chartPeriodLabel').textContent = data.period_text + ':';
        document.getElementById('chartTotalSummary').textContent = data.total_formatted;
        document.getElementById('chartOutletASummary').textContent = data.total_a_formatted;
        document.getElementById('chartOutletBSummary').textContent = data.total_b_formatted;

        // Gradient Fills
        const chartCtx = ctx.getContext('2d');

        // Gradient 1: Brand Primary (Teal / Outlet A)
        const gradientA = chartCtx.createLinearGradient(0, 0, 0, 320);
        gradientA.addColorStop(0, 'rgba(0, 123, 139, 0.35)');
        gradientA.addColorStop(1, 'rgba(0, 123, 139, 0.0)');

        // Gradient 2: Amber (Outlet B)
        const gradientB = chartCtx.createLinearGradient(0, 0, 0, 320);
        gradientB.addColorStop(0, 'rgba(245, 158, 11, 0.35)');
        gradientB.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: namaOutletA,
                        data: data.outlet_a,
                        borderColor: '#007B8B',
                        backgroundColor: gradientA,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#007B8B',
                        pointBorderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        tension: 0.38,
                        fill: true
                    },
                    {
                        label: namaOutletB,
                        data: data.outlet_b,
                        borderColor: '#f59e0b',
                        backgroundColor: gradientB,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f59e0b',
                        pointBorderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        tension: 0.38,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false // Sudah ditampilkan di custom summary header
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 10,
                        usePointStyle: true,
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.dataset.label + ': ' + formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                size: 12
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(226, 232, 240, 0.6)',
                            borderDash: [4, 4],
                            drawBorder: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                size: 11
                            },
                            callback: function (val) {
                                if (val >= 1000000) {
                                    return (val / 1000000).toFixed(1) + 'M';
                                } else if (val >= 1000) {
                                    return (val / 1000).toFixed(0) + 'k';
                                }
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    // Function untuk Switch Periode
    window.setChartPeriod = function (period) {
        currentPeriod = period;

        document.getElementById('btnPeriodDaily').classList.toggle('active', period === 'perhari');
        document.getElementById('btnPeriodWeekly').classList.toggle('active', period === 'perminggu');
        document.getElementById('btnPeriodMonthly').classList.toggle('active', period === 'perbulan');

        initChart(period);
    };

    // Render default Monthly chart
    initChart('perbulan');
});
</script>
@endsection