@extends('template.index')

@section('title', 'Menu Kasir POS | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Kasir Status Banner -->
    <div class="kasir-banner-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="kasir-icon-box">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h2 class="hero-title mb-0">Menu Kasir</h2>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Shift Aktif</span>
                        </div>
                        <p class="hero-subtitle mb-0">
                            Selamat bertugas, <strong>{{ auth()->user()->name ?? 'Kasir Pressent' }}</strong>. Cabang: <strong>Studio Pekanbaru</strong>.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button type="button" class="btn btn-hero-pill">
                        <i class="bi bi-clock-history me-1"></i> Riwayat Shift
                    </button>
                    <button type="button" class="btn btn-hero-pill btn-hero-solid">
                        <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Kasir -->
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="metric-icon-box bg-emerald-light text-emerald">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2 py-1 fw-semibold font-sm">Hari Ini</span>
                </div>
                <div class="metric-value">42</div>
                <div class="metric-label">TRANSAKSI SELESAI</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="metric-icon-box bg-blue-light text-blue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <span class="badge bg-blue-subtle text-blue rounded-pill px-2 py-1 fw-semibold font-sm">Shift Ini</span>
                </div>
                <div class="metric-value">Rp 3.840.000</div>
                <div class="metric-label">TOTAL PENDAPATAN KASIR</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="metric-icon-box bg-amber-light text-amber">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <span class="badge bg-amber-subtle text-amber rounded-pill px-2 py-1 fw-semibold font-sm">Live</span>
                </div>
                <div class="metric-value">4 / 6</div>
                <div class="metric-label">ROOM STUDIO TERISI</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="metric-icon-box bg-rose-light text-rose">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                    <span class="badge bg-rose-subtle text-rose rounded-pill px-2 py-1 fw-semibold font-sm">Antrean</span>
                </div>
                <div class="metric-value">3</div>
                <div class="metric-label">ORDER CAFE PROSES</div>
            </div>
        </div>
    </div>

    <!-- Monitoring Room Studio & Cafe POS -->
    <div class="row g-3 g-xl-4">
        <!-- Live Status Room Studio -->
        <div class="col-xl-7">
            <div class="content-panel-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-icon-box bg-emerald-light text-emerald">
                            <i class="bi bi-film"></i>
                        </div>
                        <div>
                            <h5 class="panel-card-title mb-0">Status Room Studio & Film</h5>
                            <span class="text-muted small">Monitoring penggunaan room secara realtime</span>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>

                <div class="row g-3">
                    <!-- Room 1 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Studio Room 01</span>
                                <span class="badge bg-danger rounded-pill px-2 py-1">Digunakan</span>
                            </div>
                            <div class="small text-muted mb-2"><i class="bi bi-film me-1"></i> Film: <strong>Interstellar (VIP)</strong></div>
                            <div class="small text-muted mb-2"><i class="bi bi-clock me-1"></i> Sisa Waktu: <strong class="text-danger">38 Menit</strong></div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" style="width: 70%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Room 2 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Studio Room 02</span>
                                <span class="badge bg-success rounded-pill px-2 py-1">Tersedia</span>
                            </div>
                            <div class="small text-muted mb-2"><i class="bi bi-check-circle me-1"></i> Siap untuk pemesanan baru</div>
                            <div class="small text-muted mb-2"><i class="bi bi-people me-1"></i> Kapasitas: 4 Orang</div>
                            <button class="btn btn-sm btn-emerald w-100 rounded-3 mt-1">
                                <i class="bi bi-plus-circle me-1"></i> Pesan Room Ini
                            </button>
                        </div>
                    </div>

                    <!-- Room 3 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Studio Room 03</span>
                                <span class="badge bg-danger rounded-pill px-2 py-1">Digunakan</span>
                            </div>
                            <div class="small text-muted mb-2"><i class="bi bi-film me-1"></i> Film: <strong>Oppenheimer (VVIP)</strong></div>
                            <div class="small text-muted mb-2"><i class="bi bi-clock me-1"></i> Sisa Waktu: <strong class="text-warning">1 Jam 12 Menit</strong></div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: 45%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Room 4 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Studio Room 04</span>
                                <span class="badge bg-success rounded-pill px-2 py-1">Tersedia</span>
                            </div>
                            <div class="small text-muted mb-2"><i class="bi bi-check-circle me-1"></i> Siap untuk pemesanan baru</div>
                            <div class="small text-muted mb-2"><i class="bi bi-people me-1"></i> Kapasitas: 6 Orang (Deluxe)</div>
                            <button class="btn btn-sm btn-emerald w-100 rounded-3 mt-1">
                                <i class="bi bi-plus-circle me-1"></i> Pesan Room Ini
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Cafe Cepat -->
        <div class="col-xl-5">
            <div class="content-panel-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="metric-icon-box bg-emerald-light text-emerald">
                            <i class="bi bi-cup-hot"></i>
                        </div>
                        <div>
                            <h5 class="panel-card-title mb-0">Kasir Cafe & Snack</h5>
                            <span class="text-muted small">Pesanan makanan dan minuman cafe</span>
                        </div>
                    </div>

                    <div class="list-group list-group-flush mb-3">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <div class="fw-bold">Kopi Susu Gula Aren</div>
                                <div class="text-muted small">Minuman - Dingin</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">Rp 22.000</div>
                                <button class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill">+ Tambah</button>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <div class="fw-bold">Popcorn Caramel Large</div>
                                <div class="text-muted small">Snack Studio</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">Rp 28.000</div>
                                <button class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill">+ Tambah</button>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <div class="fw-bold">French Fries Bolognese</div>
                                <div class="text-muted small">Makanan Ringan</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">Rp 25.000</div>
                                <button class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill">+ Tambah</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-top">
                    <button class="btn btn-emerald w-100 py-2 rounded-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-cart-check"></i>
                        <span>Buka Kasir Lengkap (POS Studio & Cafe)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .kasir-banner-card {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border-radius: 20px;
        padding: 24px 28px;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.35);
    }

    .kasir-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .hero-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 1.5rem;
    }

    .hero-subtitle {
        font-size: 0.9rem;
        opacity: 0.92;
    }

    .btn-hero-pill {
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #ffffff;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .btn-hero-pill:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    .btn-hero-solid {
        background: #ffffff !important;
        color: #059669 !important;
        border-color: #ffffff !important;
    }
    .btn-hero-solid:hover {
        background: #f1f5f9 !important;
        color: #047857 !important;
    }

    .metric-card {
        background: var(--bg-card);
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        padding: 20px 22px;
        box-shadow: var(--card-shadow);
    }

    .metric-icon-box {
        width: 42px;
        height: 42px;
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

    .bg-emerald { background-color: #10b981 !important; }
    .text-emerald { color: #10b981 !important; }
    .bg-emerald-light { background-color: rgba(16, 185, 129, 0.12) !important; }
    .bg-emerald-subtle { background-color: rgba(16, 185, 129, 0.14) !important; }

    .bg-blue { background-color: #3b82f6 !important; }
    .text-blue { color: #3b82f6 !important; }
    .bg-blue-light { background-color: rgba(59, 130, 246, 0.12) !important; }
    .bg-blue-subtle { background-color: rgba(59, 130, 246, 0.14) !important; }

    .bg-rose-light { background-color: rgba(244, 63, 94, 0.12) !important; }
    .bg-rose-subtle { background-color: rgba(244, 63, 94, 0.14) !important; }

    .bg-amber-light { background-color: rgba(245, 158, 11, 0.12) !important; }
    .bg-amber-subtle { background-color: rgba(245, 158, 11, 0.14) !important; }

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

    .btn-emerald {
        background: #059669;
        color: #ffffff;
        font-weight: 600;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-emerald:hover {
        background: #047857;
        color: #ffffff;
    }
</style>
@endsection
