@extends('template.index')

@section('title', 'Dashboard Kasir | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- ----------------------------------------------------
         1. HERO WELCOME BANNER (MATCHING ADMIN DASHBOARD HERO)
    ---------------------------------------------------- -->
    <div class="welcome-hero-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="hero-icon-box">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <div>
                        <h2 class="hero-title mb-1">Selamat Datang, {{ $user->name ?? 'Kasir Studio' }}!</h2>
                        <div class="hero-subtitle d-flex flex-wrap align-items-center gap-2">
                            <span><i class="bi bi-geo-alt-fill me-1 text-warning"></i>{{ $namaCabang }}</span>
                            <span>•</span>
                            <span><i class="bi bi-calendar-event me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                            <span>•</span>
                            @if($activeShift)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-clock-fill me-1"></i>Shift Aktif (Buka {{ $activeShift->waktu_buka ? $activeShift->waktu_buka->format('H:i') : '' }} WIB)
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-exclamation-circle me-1"></i>Shift Belum Dibuka
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                    <a href="{{ route('kasir.kasir') }}" class="btn btn-hero-pill btn-hero-solid shadow-sm d-flex align-items-center gap-1.5 fw-bold">
                        <i class="bi bi-calculator fs-6"></i>
                        <span>Menu Kasir (POS)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ----------------------------------------------------
         2. FOUR METRIC KPI CARDS (MATCHING ADMIN DASHBOARD)
    ---------------------------------------------------- -->
    <div class="row g-3 g-xl-4 mb-4">
        <!-- 1. Total Transaksi Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-emerald-light text-emerald">
                        <i class="bi bi-cart-check-fill"></i>
                    </div>
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        Today
                    </span>
                </div>
                <div class="metric-value font-monospace">{{ number_format($totalTransaksiToday, 0, ',', '.') }}</div>
                <div class="metric-label mb-2">TOTAL TRANSAKSI HARI INI</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-emerald" style="width: {{ min(100, $totalTransaksiToday * 10) }}%;"></div>
                </div>
            </div>
        </div>

        <!-- 2. Omzet Penjualan Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-blue-light text-blue">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="badge bg-blue-subtle text-blue rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        Revenue
                    </span>
                </div>
                <div class="metric-value font-monospace">Rp {{ number_format($totalPendapatanToday, 0, ',', '.') }}</div>
                <div class="metric-label mb-2">OMZET PENJUALAN HARI INI</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-blue" style="width: {{ min(100, $totalPendapatanToday > 0 ? 75 : 0) }}%;"></div>
                </div>
            </div>
        </div>

        <!-- 3. Room Terpakai & Tersedia -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-rose-light text-rose">
                        <i class="bi bi-display-fill"></i>
                    </div>
                    <span class="badge {{ $totalRoomTerpakai > 0 ? 'bg-rose-subtle text-rose' : 'bg-success-subtle text-success' }} rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        {{ $totalRoomTerpakai > 0 ? $totalRoomTerpakai . ' Aktif' : 'Semua Kosong' }}
                    </span>
                </div>
                <div class="metric-value font-monospace">{{ $totalRoomTerpakai }} <span class="fs-6 text-muted fw-normal">/ {{ $totalRoom }} Room</span></div>
                <div class="metric-label mb-2">ROOM STUDIO TERPAKAI</div>
                <div class="metric-progress-wrapper">
                    @php
                        $roomPercent = $totalRoom > 0 ? round(($totalRoomTerpakai / $totalRoom) * 100) : 0;
                    @endphp
                    <div class="metric-progress-bar bg-rose" style="width: {{ $roomPercent }}%;"></div>
                </div>
            </div>
        </div>

        <!-- 4. Item Cafe / F&B Terjual -->
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="metric-icon-box bg-amber-light text-amber">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <span class="badge bg-amber-subtle text-amber rounded-pill px-2.5 py-1 fw-semibold font-sm">
                        F&amp;B
                    </span>
                </div>
                <div class="metric-value font-monospace">{{ number_format($totalCafeQtyToday, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Item</span></div>
                <div class="metric-label mb-2">PRODUK CAFE TERJUAL HARI INI</div>
                <div class="metric-progress-wrapper">
                    <div class="metric-progress-bar bg-amber" style="width: {{ min(100, $totalCafeQtyToday * 6) }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ----------------------------------------------------
         3. SECTION: ROOM YANG TERPAKAI DAN SISA WAKTUNYA
    ---------------------------------------------------- -->
    <div class="content-panel-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4 gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box bg-rose-light text-rose">
                    <i class="bi bi-film"></i>
                </div>
                <div>
                    <h5 class="panel-card-title mb-0">Status &amp; Monitoring Room Studio</h5>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-play-circle-fill me-1"></i>{{ $totalRoomTerpakai }} Room Terpakai
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ $totalRoomTersedia }} Room Tersedia
                </span>
            </div>
        </div>

        @if($roomStatuses->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-camera-reels display-4 text-muted opacity-50 mb-3 d-block"></i>
                <h6 class="fw-semibold text-muted">Belum ada room studio di outlet ini</h6>
                <p class="small text-muted mb-0">Silakan hubungi administrator untuk konfigurasi master data room.</p>
            </div>
        @else
            <div class="row g-3 g-xl-4">
                @foreach($roomStatuses as $room)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="room-monitor-card h-100 {{ $room->is_occupied ? 'occupied-card' : 'available-card' }}">
                            <!-- Card Header -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-0 text-heading fs-6 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-door-open-fill text-muted"></i>
                                        <span>{{ $room->nama }}</span>
                                    </h6>
                                    <span class="text-muted small">Kapasitas: {{ $room->kapasitas }} Orang</span>
                                </div>
                                <div>
                                    @if($room->is_occupied)
                                        <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold font-sm shadow-sm d-inline-flex align-items-center gap-1">
                                            <span class="live-pulse-dot"></span>
                                            <span>SEDANG TERPAKAI</span>
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold font-sm">
                                            <i class="bi bi-check2-circle me-1"></i> TERSEDIA
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if($room->is_occupied)
                                <!-- Movie & Customer Details -->
                                <div class="occupied-details-box p-3 rounded-3 mb-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="room-film-icon rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-film fs-4 text-danger"></i>
                                        </div>
                                        <div class="overflow-hidden flex-fill">
                                            <div class="fw-bold text-heading text-truncate fs-6" title="{{ $room->film_judul }}">
                                                {{ $room->film_judul }}
                                            </div>
                                            <div class="text-muted small text-truncate">
                                                <i class="bi bi-person me-1"></i>{{ $room->nama_pelanggan }}
                                            </div>
                                            <div class="text-muted small font-monospace">
                                                <i class="bi bi-receipt me-1"></i>{{ $room->nomor_transaksi }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Time Schedule Range -->
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-danger-subtle small">
                                        <span class="text-muted">
                                            <i class="bi bi-play-fill text-success"></i> Mulai: <strong>{{ $room->waktu_mulai ? $room->waktu_mulai->format('H:i') : '-' }} WIB</strong>
                                        </span>
                                        <span class="text-muted">
                                            <i class="bi bi-stop-fill text-danger"></i> Selesai: <strong>{{ $room->waktu_selesai ? $room->waktu_selesai->format('H:i') : '-' }} WIB</strong>
                                        </span>
                                    </div>
                                </div>

                                <!-- Highlighted Remaining Time Box -->
                                <div class="sisa-waktu-banner p-3 rounded-3 mb-3 text-center">
                                    <div class="text-uppercase small fw-bold text-danger mb-1 letter-spacing-1">
                                        <i class="bi bi-hourglass-split me-1 animate-spin-slow"></i> Sisa Waktu Penayangan
                                    </div>
                                    <div class="countdown-display fw-bold text-danger font-monospace fs-4" 
                                         id="countdown-room-{{ $room->id }}" 
                                         data-seconds="{{ $room->sisa_detik }}">
                                        {{ $room->sisa_waktu_teks }}
                                    </div>
                                    <div class="progress mt-2 rounded-pill" style="height: 6px; background-color: rgba(239, 68, 68, 0.15);">
                                        <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                                             role="progressbar" 
                                             style="width: {{ $room->persentase }}%;" 
                                             aria-valuenow="{{ $room->persentase }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted font-sm mt-1">
                                        <span>Berjalan: {{ round($room->persentase) }}%</span>
                                        <span>Total: {{ $room->durasi_menit }} Menit</span>
                                    </div>
                                </div>
                            @else
                                <!-- Room Available State -->
                                <div class="available-state-box text-center py-4 px-3 rounded-3 mb-3">
                                    <div class="available-icon-box mx-auto mb-2 text-success">
                                        <i class="bi bi-check-circle fs-1"></i>
                                    </div>
                                    <div class="fw-semibold text-dark">Studio Siap Digunakan</div>
                                    <p class="text-muted small mb-0">Tidak ada penayangan aktif saat ini. Siap untuk pelanggan selanjutnya.</p>
                                </div>
                            @endif

                            <!-- Card Footer Action -->
                            <div class="room-card-actions mt-auto pt-3 border-top">
                                @if($room->is_occupied)
                                    <button type="button"
                                           class="btn btn-sm btn-info text-white fw-bold rounded-3 d-flex align-items-center gap-1 shadow-sm room-card-action btn-open-move-room"
                                           data-room-id="{{ $room->id }}"
                                           data-room-nama="{{ $room->nama }}"
                                           title="Pindah ke room lain yang tersedia">
                                        <i class="bi bi-arrow-left-right fs-6"></i>
                                        <span>Pindah Room</span>
                                    </button>
                                    <a href="{{ route('kasir.kasir', ['mode' => 'ganti_film', 'room_id' => $room->id]) }}"
                                           class="btn btn-sm btn-warning text-dark fw-bold rounded-3 d-flex align-items-center gap-1 shadow-sm room-card-action"
                                           title="Ganti Film yang sedang ditayangkan pada room ini">
                                        <i class="bi bi-arrow-repeat fs-6"></i>
                                        <span>Ganti Film</span>
                                    </a>
                                    <button type="button"
                                           class="btn btn-sm btn-danger text-white fw-bold rounded-3 d-flex align-items-center gap-1 shadow-sm room-card-action btn-open-hentikan-pemutaran"
                                           data-room-id="{{ $room->id }}"
                                           data-room-nama="{{ $room->nama }}"
                                           data-film-judul="{{ $room->film_judul }}"
                                           data-sisa-waktu="{{ $room->sisa_waktu_teks }}"
                                           title="Hentikan pemutaran jika penonton telah selesai menonton lebih awal">
                                        <i class="bi bi-stop-circle fs-6"></i>
                                        <span>Hentikan</span>
                                    </button>
                                    <a href="{{ route('kasir.kasir', ['mode' => 'pesanan_cafe', 'room_id' => $room->id]) }}"
                                       class="btn btn-sm btn-outline-primary rounded-3 fw-semibold d-flex align-items-center gap-1 room-card-action"
                                       title="Tambah pesanan makanan atau minuman cafe untuk room ini">
                                        <i class="bi bi-cup-straw"></i>
                                        <span>Pesanan Baru</span>
                                    </a>
                                @else
                                    <a href="{{ route('kasir.kasir') }}" class="btn btn-sm btn-emerald text-white rounded-3 fw-semibold d-flex align-items-center gap-1 room-card-action room-card-action-primary">
                                        <i class="bi bi-cart-plus"></i>
                                        <span>Pilih Room Ini</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ----------------------------------------------------
         4. SECTION: RIWAYAT PEMBELIAN HARI INI (TODAY)
    ---------------------------------------------------- -->
    <div class="content-panel-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4 gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="metric-icon-box bg-emerald-light text-emerald">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h5 class="panel-card-title mb-0">Riwayat Pembelian &amp; Transaksi Hari Ini (Today)</h5>
                    <span class="text-muted small">Daftar seluruh transaksi pembelian tiket film, room, dan pesanan kafe hari ini di {{ $namaCabang }}.</span>
                </div>
            </div>
        </div>

        <!-- Quick Filter / Search Box -->
        <div class="row g-3 mb-3 align-items-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="filterRiwayatInput" class="form-control border-start-0 ps-0" placeholder="Cari nama pelanggan, no transaksi, atau film..." onkeyup="filterRiwayatTable()">
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8 text-md-end text-muted small">
                <span>Menampilkan <strong>{{ $riwayatPembelianToday->count() }}</strong> riwayat pembelian transaksi hari ini.</span>
            </div>
        </div>

        <!-- Responsive Transaction Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="riwayatTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-muted small fw-bold" style="width: 50px;">NO</th>
                        <th class="text-muted small fw-bold">NO. TRANSAKSI</th>
                        <th class="text-muted small fw-bold">WAKTU</th>
                        <th class="text-muted small fw-bold">PELANGGAN</th>
                        <th class="text-muted small fw-bold">FILM &amp; ROOM</th>
                        <th class="text-muted small fw-bold">PESANAN CAFE</th>
                        <th class="text-muted small fw-bold">METODE BAYAR</th>
                        <th class="text-end text-muted small fw-bold">TOTAL PEMBELIAN</th>
                        <th class="text-center pe-3 text-muted small fw-bold" style="width: 100px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatPembelianToday as $index => $trx)
                        <tr class="riwayat-row">
                            <td class="ps-3 fw-semibold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-bold font-monospace text-heading">{{ $trx->nomor_transaksi }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="bi bi-clock me-1 text-muted"></i>{{ $trx->waktu_transaksi ? $trx->waktu_transaksi->format('H:i') : '-' }} WIB
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-heading">{{ $trx->nama_pelanggan }}</div>
                                <span class="text-muted font-sm"><i class="bi bi-person-fill me-1"></i>Kasir: {{ $trx->user?->name ?? 'Kasir' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 180px;" title="{{ $trx->film?->judul ?? 'Film' }}">
                                    <i class="bi bi-film text-danger me-1"></i>{{ $trx->film?->judul ?? '-' }}
                                </div>
                                <span class="badge bg-info-subtle text-info border border-info-subtle font-sm mt-1">
                                    <i class="bi bi-door-closed me-1"></i>{{ $trx->room?->nama ?? 'Room' }}
                                </span>
                            </td>
                            <td>
                                @if($trx->details->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 200px;">
                                        @foreach($trx->details as $detail)
                                            <span class="badge bg-light text-dark border font-sm">
                                                {{ $detail->barang?->nama_barang ?? 'Item' }} ({{ (int)$detail->qty }})
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Tidak ada</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 font-sm">
                                    <i class="bi bi-credit-card-2-front me-1"></i>{{ $trx->tipePembayaran?->nama_tipe ?? 'Tunai' }}
                                </span>
                            </td>
                            <td class="text-end font-monospace fw-bold text-emerald fs-6">
                                Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('kasir.struk', $trx->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" title="Cetak / Lihat Struk">
                                    <i class="bi bi-printer-fill me-1"></i> Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                    <h6 class="fw-semibold">Belum Ada Transaksi Pembelian Hari Ini</h6>
                                    <p class="small text-muted mb-3">Transaksi yang dilakukan melalui menu kasir akan otomatis tercatat di sini.</p>
                                    <a href="{{ route('kasir.kasir') }}" class="btn btn-sm btn-emerald text-white rounded-3 px-3 py-2 fw-semibold">
                                        <i class="bi bi-calculator me-1"></i> Buka Menu Kasir Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($riwayatPembelianToday->isNotEmpty())
                    <tfoot class="table-light border-top border-2">
                        <tr class="fw-bold">
                            <td colspan="7" class="ps-3 text-dark py-3">
                                <i class="bi bi-calculator-fill text-emerald me-2"></i>GRAND TOTAL PEMBELIAN HARI INI
                            </td>
                            <td class="text-end font-monospace text-emerald fs-5 py-3">
                                Rp {{ number_format($totalPendapatanToday, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@include('kasir.partials.modal-move-room')
@include('kasir.partials.modal-hentikan-pemutaran')
@endsection

@section('styles')
<style>
    /* Welcome Hero Card (Matching Admin Style) */
    .welcome-hero-card {
        background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
        border-radius: 20px;
        padding: 28px 32px;
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
        width: 52px;
        height: 52px;
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
        font-size: 1.6rem;
    }

    .hero-subtitle {
        font-size: 0.9rem;
        opacity: 0.95;
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
        color: var(--brand-primary) !important;
        border-color: #ffffff !important;
    }
    .btn-hero-solid:hover {
        background: #f8fafc !important;
        color: var(--brand-primary-dark) !important;
        transform: translateY(-1px);
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
        font-size: 1.65rem;
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
    .bg-amber-subtle { background-color: rgba(245, 158, 11, 0.14) !important; }

    .text-heading { color: var(--text-heading) !important; }
    .font-sm { font-size: 0.78rem; }

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
        font-size: 1.15rem;
        color: var(--text-heading);
    }

    /* Room Monitoring Cards */
    .room-monitor-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        transition: all 0.25s ease;
        position: relative;
    }
    .room-monitor-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px -6px rgba(0, 0, 0, 0.1);
    }

    .occupied-card {
        border-top: 4px solid #ef4444;
        background: #fffafa;
    }

    .available-card {
        border-top: 4px solid #10b981;
    }

    .occupied-details-box {
        background: rgba(254, 242, 242, 0.85);
        border: 1px solid rgba(252, 165, 165, 0.4);
    }

    .room-film-icon {
        width: 44px;
        height: 44px;
        background: #ffffff;
        border: 1px solid #fecaca;
        flex-shrink: 0;
    }

    .sisa-waktu-banner {
        background: #fff;
        border: 1px dashed #fca5a5;
    }

    .available-state-box {
        background: rgba(240, 253, 244, 0.7);
        border: 1px dashed rgba(134, 239, 172, 0.8);
    }

    .room-card-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .room-card-action {
        width: 100%;
        min-height: 40px;
        justify-content: center;
        padding: 0.4rem 0.5rem;
        white-space: nowrap;
    }

    .room-card-action-primary {
        grid-column: 1 / -1;
    }

    @media (max-width: 359.98px) {
        .room-card-action {
            font-size: 0.75rem;
        }
    }

    .available-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Live Pulsing Dot */
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ffffff;
        display: inline-block;
        animation: pulseAnimation 1.5s infinite;
    }

    @keyframes pulseAnimation {
        0% { transform: scale(0.95); opacity: 0.9; }
        50% { transform: scale(1.4); opacity: 0.4; }
        100% { transform: scale(0.95); opacity: 0.9; }
    }

    .letter-spacing-1 {
        letter-spacing: 0.05em;
    }
</style>
@endsection

@push('scripts')
<script>
    // Live countdown timer for active rooms
    document.addEventListener('DOMContentLoaded', function () {
        function updateTimers() {
            const timerElements = document.querySelectorAll('.countdown-display');
            timerElements.forEach(el => {
                let seconds = parseInt(el.getAttribute('data-seconds'), 10);
                if (isNaN(seconds) || seconds <= 0) {
                    el.innerHTML = '<span class="text-danger fw-bold">Waktu Habis</span>';
                    return;
                }

                seconds -= 1;
                el.setAttribute('data-seconds', seconds);

                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;

                let text = '';
                if (hours > 0) {
                    text = `${hours} Jam ${minutes} Menit ${secs} Detik`;
                } else if (minutes > 0) {
                    text = `${minutes} Menit ${secs} Detik`;
                } else {
                    text = `${secs} Detik`;
                }

                el.textContent = text;
            });
        }

        setInterval(updateTimers, 1000);
    });

    // Client-side quick filter for riwayat table
    function filterRiwayatTable() {
        const input = document.getElementById('filterRiwayatInput');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('#riwayatTable tbody tr.riwayat-row');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush