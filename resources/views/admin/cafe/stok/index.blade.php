@extends('template.index')

@section('title', 'Stok dan Koreksi Stok Cafe | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Header & Breadcrumb -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Cafe</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Stok & Koreksi Stok</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Stok Barang Cafe (Inventaris Outlet)</h3>
            <p class="text-muted small mb-0">Monitoring stok barang per outlet cabang, kategori menu cafe, satuan, dan akumulasi nilai aset yang bersumber dari faktur barang.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.cafe.faktur.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Daftar Faktur</span>
            </a>
            <a href="{{ route('admin.cafe.faktur.create') }}" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i>
                <span class="fw-semibold">Input Faktur Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Feedback Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-x fs-4 text-danger"></i>
                <div>
                    <h6 class="fw-bold mb-1">Gagal Memproses Data</h6>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary Cards: 3 Cards Penuh Kesamping (Total Asset, Total Kuantitas Barang, Outlet Aktif) -->
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
        <!-- Card 1: Total Nilai Aset -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-emerald-subtle text-emerald rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Nilai Aset</div>
                        <h3 class="fw-bold text-heading font-monospace mb-1 text-emerald text-truncate">
                            Rp {{ number_format($totalAsset, 0, ',', '.') }}
                        </h3>
                        <div class="text-muted small text-truncate">Akumulasi modal stok faktur pembelian</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Kuantitas Barang -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-boxes fs-2"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Kuantitas Barang</div>
                        <h3 class="fw-bold text-heading font-monospace mb-1 text-truncate">
                            {{ number_format($totalBarangFisik, $totalBarangFisik == (int)$totalBarangFisik ? 0 : 2, ',', '.') }} <span class="fs-6 fw-normal text-muted">Unit</span>
                        </h3>
                        <div class="text-muted small text-truncate">Total unit fisik persediaan di seluruh outlet</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Outlet Aktif Memiliki Stok -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-info-subtle text-info rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-shop fs-2"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Outlet Aktif</div>
                        <h3 class="fw-bold text-heading font-monospace mb-1 text-truncate">
                            {{ number_format($totalOutlet, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Cabang</span>
                        </h3>
                        <div class="text-muted small text-truncate">Toko cabang dengan persediaan stok aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.cafe.stok') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Cari nama barang, outlet, atau kategori...">
                    </div>
                </div>

                <!-- Filter Outlet Cabang -->
                <div class="col-12 col-sm-6 col-md-3">
                    <select name="toko_cabang_id" class="form-select">
                        <option value="">-- Semua Toko Cabang --</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ request('toko_cabang_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kategori Menu Cafe -->
                <div class="col-12 col-sm-6 col-md-3">
                    <select name="menu_cafe_id" class="form-select">
                        <option value="">-- Semua Kategori Cafe --</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ request('menu_cafe_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter Buttons -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald px-3 flex-fill rounded-3">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'toko_cabang_id', 'menu_cafe_id']))
                        <a href="{{ route('admin.cafe.stok') }}" class="btn btn-outline-secondary rounded-3" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card: Data Stok Barang -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title fw-bold text-heading mb-0">
                    <i class="bi bi-box-seam-fill text-emerald me-2"></i>Daftar Persediaan Stok Barang
                </h5>
                <p class="text-muted small mb-0">Kuantitas stok terakumulasi dari faktur masuk. Anda dapat melihat detail riwayat, mengedit/koreksi stok, serta menghapus data.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-3 small">
                    Total: <strong class="text-emerald">{{ $stokItems->total() }}</strong> Baris Stok
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-muted small fw-bold" style="width: 50px;">NO</th>
                            <th class="text-muted small fw-bold" style="min-width: 200px;">NAMA BARANG</th>
                            <th class="text-muted small fw-bold">NAMA OUTLET (CABANG)</th>
                            <th class="text-muted small fw-bold">KATEGORI (MENU CAFE)</th>
                            <th class="text-muted small fw-bold">SATUAN</th>
                            <th class="text-muted small fw-bold text-center">STOK DARI FAKTUR</th>
                            <th class="text-muted small fw-bold text-end">NILAI ASET (MODAL)</th>
                            <th class="text-muted small fw-bold text-end">HARGA JUAL KASIR</th>
                            <th class="text-end pe-4 text-muted small fw-bold" style="min-width: 210px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stokItems as $item)
                            <tr>
                                <td class="ps-4 text-muted small fw-semibold">
                                    {{ ($stokItems->currentPage() - 1) * $stokItems->perPage() + $loop->iteration }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <div class="item-avatar-mini rounded-2 bg-light d-flex align-items-center justify-content-center text-emerald">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <span>{{ $item->nama_barang }}</span>
                                    </div>
                                    <div class="text-muted small ms-4 ps-2">
                                        Terakhir masuk: {{ \Carbon\Carbon::parse($item->tanggal_terakhir)->isoFormat('D MMM Y') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-shop text-muted"></i>
                                        <span class="fw-semibold text-dark">{{ $item->nama_cabang }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $kategoriName = $item->nama_kategori ?? 'Umum';
                                        $badgeColor = match(strtolower($kategoriName)) {
                                            'makanan' => 'bg-warning-subtle text-warning border-warning',
                                            'minuman' => 'bg-info-subtle text-info border-info',
                                            'snack' => 'bg-primary-subtle text-primary border-primary',
                                            default => 'bg-secondary-subtle text-secondary border-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }} border px-2 py-1 rounded-2">
                                        <i class="bi bi-tag me-1"></i>{{ $kategoriName }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-2 font-monospace">
                                        {{ $item->nama_satuan }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $stokVal = (float) $item->total_stok;
                                        $stokFormatted = $stokVal == (int)$stokVal ? number_format($stokVal, 0, ',', '.') : number_format($stokVal, 2, ',', '.');
                                    @endphp
                                    <span class="badge bg-emerald-subtle text-emerald fs-6 px-3 py-1 rounded-3 fw-bold font-monospace border border-emerald">
                                        {{ $stokFormatted }} {{ $item->nama_satuan }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace fw-bold text-emerald">
                                    Rp {{ number_format($item->total_asset_item, 0, ',', '.') }}
                                    <div class="text-muted small fw-normal" style="font-size: 0.72rem;">
                                        Avg modal: Rp {{ number_format($item->avg_harga_awal, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="text-end font-monospace fw-semibold text-dark">
                                    Rp {{ number_format($item->harga_jual_terakhir, 0, ',', '.') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Aksi 1: Detail -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info rounded-2 px-2 py-1 btn-detail-stok"
                                                data-cabang-id="{{ $item->toko_cabang_id }}"
                                                data-barang-id="{{ $item->barang_id }}"
                                                data-satuan-id="{{ $item->satuan_barang_id }}"
                                                data-nama-barang="{{ $item->nama_barang }}"
                                                data-nama-cabang="{{ $item->nama_cabang }}"
                                                data-kategori="{{ $item->nama_kategori ?? 'Umum' }}"
                                                data-satuan="{{ $item->nama_satuan }}"
                                                data-stok="{{ $item->total_stok }}"
                                                data-asset="{{ $item->total_asset_item }}"
                                                data-avg-harga="{{ $item->avg_harga_awal }}"
                                                data-harga-jual="{{ $item->harga_jual_terakhir }}"
                                                title="Lihat Detail & Riwayat Faktur">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </button>

                                        <!-- Aksi 2: Edit / Koreksi -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning rounded-2 px-2 py-1 btn-edit-stok"
                                                data-cabang-id="{{ $item->toko_cabang_id }}"
                                                data-barang-id="{{ $item->barang_id }}"
                                                data-satuan-id="{{ $item->satuan_barang_id }}"
                                                data-nama-barang="{{ $item->nama_barang }}"
                                                data-nama-cabang="{{ $item->nama_cabang }}"
                                                data-satuan="{{ $item->nama_satuan }}"
                                                data-stok="{{ $item->total_stok }}"
                                                data-avg-harga="{{ $item->avg_harga_awal }}"
                                                data-harga-jual="{{ $item->harga_jual_terakhir }}"
                                                title="Edit / Koreksi Stok & Harga Jual">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>

                                        <!-- Aksi 3: Hapus -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1 btn-hapus-stok"
                                                data-cabang-id="{{ $item->toko_cabang_id }}"
                                                data-barang-id="{{ $item->barang_id }}"
                                                data-satuan-id="{{ $item->satuan_barang_id }}"
                                                data-nama-barang="{{ $item->nama_barang }}"
                                                data-nama-cabang="{{ $item->nama_cabang }}"
                                                title="Hapus Stok Item Ini">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-box2-heart fs-1"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Stok Barang Tercatat</h6>
                                    <p class="text-muted small mb-3">
                                        @if(request()->hasAny(['search', 'toko_cabang_id', 'menu_cafe_id']))
                                            Tidak ada stok barang yang cocok dengan kriteria filter pencarian Anda.
                                        @else
                                            Stok barang diperoleh saat Anda melakukan input faktur pembelian/grosir barang.
                                        @endif
                                    </p>
                                    @if(request()->hasAny(['search', 'toko_cabang_id', 'menu_cafe_id']))
                                        <a href="{{ route('admin.cafe.stok') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                                        </a>
                                    @else
                                        <a href="{{ route('admin.cafe.faktur.create') }}" class="btn btn-sm btn-emerald rounded-3 px-3 py-2">
                                            <i class="bi bi-plus-circle me-1"></i> Input Faktur Barang Sekarang
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($stokItems->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan {{ $stokItems->firstItem() ?? 0 }} sampai {{ $stokItems->lastItem() ?? 0 }} dari {{ $stokItems->total() }} stok item
                </div>
                <div>
                    {{ $stokItems->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: DETAIL STOK & RIWAYAT FAKTUR                    -->
<!-- ======================================================== -->
<div class="modal fade" id="modalDetailStok" tabindex="-1" aria-labelledby="modalDetailStokLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-heading" id="modalDetailStokLabel">
                        <i class="bi bi-info-circle-fill text-info me-2"></i>Detail Persediaan & Riwayat Faktur
                    </h5>
                    <p class="text-muted small mb-0" id="detailStokSubheading">Informasi lengkap stok barang dan mutasi faktur masuk.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Overview Cards dalam Modal -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <div class="text-muted small fw-semibold">TOTAL STOK</div>
                            <div class="fs-5 fw-bold text-emerald font-monospace" id="detailCardStok">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <div class="text-muted small fw-semibold">NILAI ASET</div>
                            <div class="fs-6 fw-bold text-dark font-monospace" id="detailCardAsset">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <div class="text-muted small fw-semibold">AVG MODAL</div>
                            <div class="fs-6 fw-bold text-muted font-monospace" id="detailCardAvgHarga">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <div class="text-muted small fw-semibold">HARGA JUAL KASIR</div>
                            <div class="fs-6 fw-bold text-primary font-monospace" id="detailCardHargaJual">Rp 0</div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-heading mb-2">
                    <i class="bi bi-clock-history me-1 text-emerald"></i>Riwayat Faktur Masuk (Pembelian / Grosir)
                </h6>

                <!-- Loading State -->
                <div id="detailLoading" class="text-center py-4">
                    <div class="spinner-border text-emerald" role="status">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <div class="text-muted small mt-2">Mengambil riwayat faktur barang...</div>
                </div>

                <!-- Table Riwayat Faktur -->
                <div id="detailContent" class="table-responsive border rounded-3" style="display: none;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 text-muted small fw-bold">NO FAKTUR</th>
                                <th class="text-muted small fw-bold">TANGGAL</th>
                                <th class="text-muted small fw-bold">PEMASOK</th>
                                <th class="text-muted small fw-bold text-center">QTY MASUK</th>
                                <th class="text-muted small fw-bold text-end">HARGA MODAL</th>
                                <th class="text-muted small fw-bold text-end">HARGA JUAL</th>
                                <th class="text-muted small fw-bold text-end pe-3">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('admin.cafe.faktur.index') }}" class="btn btn-emerald rounded-3 px-3" target="_blank">
                    <i class="bi bi-receipt me-1"></i> Kelola Faktur
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: EDIT / KOREKSI STOK BARANG                      -->
<!-- ======================================================== -->
<div class="modal fade" id="modalEditStok" tabindex="-1" aria-labelledby="modalEditStokLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="formEditStok" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom py-3">
                    <div>
                        <h5 class="modal-title fw-bold text-heading" id="modalEditStokLabel">
                            <i class="bi bi-pencil-square text-warning me-2"></i>Edit / Koreksi Stok Barang
                        </h5>
                        <p class="text-muted small mb-0">Penyesuaian kuantitas stok fisik dan harga penjualan kasir.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Info Barang Readonly -->
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="row g-2 small">
                            <div class="col-7">
                                <span class="text-muted d-block">Nama Barang:</span>
                                <strong class="text-dark" id="editLabelNamaBarang">-</strong>
                            </div>
                            <div class="col-5">
                                <span class="text-muted d-block">Outlet Cabang:</span>
                                <strong class="text-dark" id="editLabelNamaCabang">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Input Kuantitas Stok Baru -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Jumlah Stok Fisik (Koreksi) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="any" min="0" name="stok" id="inputEditStok" class="form-control rounded-start-3 font-monospace fw-bold text-center" required>
                            <span class="input-group-text bg-light text-muted font-monospace" id="editLabelSatuan">Pcs</span>
                        </div>
                        <div class="form-text small">Sesuaikan kuantitas stok aktual barang di outlet ini (misal hasil stock opname).</div>
                    </div>

                    <!-- Input Harga Jual Kasir -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Harga Penjualan Kasir (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Rp</span>
                            <input type="number" step="any" min="0" name="harga_jual" id="inputEditHargaJual" class="form-control rounded-end-3 font-monospace fw-bold text-end" required>
                        </div>
                        <div class="form-text small">Harga yang akan digunakan oleh kasir pada menu POS penjualan.</div>
                    </div>

                    <!-- Input Harga Modal Beli (Awal) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Harga Modal (Awal) (Rp)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">Rp</span>
                            <input type="number" step="any" min="0" name="harga_awal" id="inputEditHargaAwal" class="form-control rounded-end-3 font-monospace text-end">
                        </div>
                        <div class="form-text small">Harga modal pembelian per satuan (digunakan untuk menghitung nilai aset).</div>
                    </div>

                    <!-- Catatan Koreksi -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Catatan / Alasan Koreksi (Opsional)</label>
                        <textarea name="catatan" rows="2" class="form-control rounded-3" placeholder="Contoh: Koreksi selisih stok opname fisik, barang rusak, penyesuaian harga jual..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: KONFIRMASI HAPUS STOK                           -->
<!-- ======================================================== -->
<div class="modal fade" id="modalHapusStok" tabindex="-1" aria-labelledby="modalHapusStokLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="formHapusStok" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-danger" id="modalHapusStokLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Stok
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash-fill text-danger display-4"></i>
                    </div>
                    <h5 class="fw-bold text-heading mb-2">Hapus Persediaan Stok Ini?</h5>
                    <p class="text-muted small mb-3">
                        Anda akan menghapus data stok barang <strong class="text-dark" id="hapusLabelNamaBarang">-</strong> di outlet <strong class="text-dark" id="hapusLabelNamaCabang">-</strong>.
                    </p>
                    <div class="alert alert-warning text-start border-0 rounded-3 small p-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Rincian faktur masuk untuk barang ini di outlet terkait akan dihapus dan nilai total nota faktur akan disesuaikan otomatis.
                    </div>
                </div>
                <div class="modal-footer border-top py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <i class="bi bi-trash-fill me-1"></i> Ya, Hapus Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .btn-emerald {
        background: #059669;
        color: #ffffff;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-emerald:hover {
        background: #047857;
        color: #ffffff;
    }
    .btn-outline-emerald {
        border: 1px solid #059669;
        color: #059669;
        background: transparent;
    }
    .btn-outline-emerald:hover {
        background: #059669;
        color: #ffffff;
    }
    .text-emerald {
        color: #059669 !important;
    }
    .border-emerald {
        border-color: #059669 !important;
    }
    .bg-emerald-subtle {
        background-color: #d1fae5 !important;
    }
    .stat-icon-wrapper {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
    }
    .item-avatar-mini {
        width: 32px;
        height: 32px;
    }
    .card-stat {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .tracking-wider {
        letter-spacing: 0.05em;
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------
    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    // -------------------------------------------------------------
    // 1. AKSI DETAIL
    // -------------------------------------------------------------
    const modalDetailEl = document.getElementById('modalDetailStok');
    const modalDetailInstance = modalDetailEl ? bootstrap.Modal.getOrCreateInstance(modalDetailEl) : null;

    const detailLoading = document.getElementById('detailLoading');
    const detailContent = document.getElementById('detailContent');
    const detailTableBody = document.getElementById('detailTableBody');
    const detailSubheading = document.getElementById('detailStokSubheading');
    const detailCardStok = document.getElementById('detailCardStok');
    const detailCardAsset = document.getElementById('detailCardAsset');
    const detailCardAvgHarga = document.getElementById('detailCardAvgHarga');
    const detailCardHargaJual = document.getElementById('detailCardHargaJual');

    document.querySelectorAll('.btn-detail-stok').forEach(btn => {
        btn.addEventListener('click', function () {
            const cabangId = this.getAttribute('data-cabang-id');
            const barangId = this.getAttribute('data-barang-id');
            const satuanId = this.getAttribute('data-satuan-id');
            const namaBarang = this.getAttribute('data-nama-barang');
            const namaCabang = this.getAttribute('data-nama-cabang');
            const kategori = this.getAttribute('data-kategori');
            const satuan = this.getAttribute('data-satuan');
            const stok = Number(this.getAttribute('data-stok') || 0);
            const asset = Number(this.getAttribute('data-asset') || 0);
            const avgHarga = Number(this.getAttribute('data-avg-harga') || 0);
            const hargaJual = Number(this.getAttribute('data-harga-jual') || 0);

            if (detailSubheading) {
                detailSubheading.textContent = `${namaBarang} [${kategori}] di Outlet ${namaCabang}`;
            }

            if (detailCardStok) {
                detailCardStok.textContent = (Number.isInteger(stok) ? stok : stok.toFixed(2)) + ' ' + satuan;
            }
            if (detailCardAsset) detailCardAsset.textContent = formatRupiah(asset);
            if (detailCardAvgHarga) detailCardAvgHarga.textContent = formatRupiah(avgHarga);
            if (detailCardHargaJual) detailCardHargaJual.textContent = formatRupiah(hargaJual);

            // Loading state
            if (detailLoading) detailLoading.style.display = 'block';
            if (detailContent) detailContent.style.display = 'none';
            if (detailTableBody) detailTableBody.innerHTML = '';

            if (modalDetailInstance) modalDetailInstance.show();

            const url = `{{ url('/admin/cafe/stok/riwayat') }}/${cabangId}/${barangId}/${satuanId}`;
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (detailLoading) detailLoading.style.display = 'none';
                    if (detailContent) detailContent.style.display = 'block';

                    if (res.data && res.data.length > 0) {
                        let rows = '';
                        res.data.forEach(item => {
                            const qty = Number(item.qty);
                            const qtyFormatted = Number.isInteger(qty) ? qty : qty.toFixed(2);
                            rows += `
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark">
                                        <a href="{{ url('/admin/cafe/faktur') }}/${item.faktur_id}" class="text-decoration-none text-emerald" target="_blank" title="Buka Faktur">
                                            ${item.nomor_faktur} <i class="bi bi-box-arrow-up-right small"></i>
                                        </a>
                                    </td>
                                    <td class="small text-muted">${item.tanggal_faktur}</td>
                                    <td class="small">${item.pemasok || '-'}</td>
                                    <td class="text-center fw-bold font-monospace">${qtyFormatted} ${item.nama_satuan}</td>
                                    <td class="text-end font-monospace">${formatRupiah(item.harga_awal)}</td>
                                    <td class="text-end font-monospace text-emerald">${formatRupiah(item.harga_jual)}</td>
                                    <td class="text-end pe-3 font-monospace fw-bold">${formatRupiah(item.subtotal)}</td>
                                </tr>
                            `;
                        });
                        detailTableBody.innerHTML = rows;
                    } else {
                        detailTableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Tidak ditemukan riwayat faktur untuk item ini.
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(() => {
                    if (detailLoading) detailLoading.style.display = 'none';
                    if (detailContent) detailContent.style.display = 'block';
                    detailTableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger">
                                Terjadi kendala saat memuat riwayat faktur. Silakan coba kembali.
                            </td>
                        </tr>
                    `;
                });
        });
    });

    // -------------------------------------------------------------
    // 2. AKSI EDIT / KOREKSI
    // -------------------------------------------------------------
    const modalEditEl = document.getElementById('modalEditStok');
    const modalEditInstance = modalEditEl ? bootstrap.Modal.getOrCreateInstance(modalEditEl) : null;
    const formEdit = document.getElementById('formEditStok');
    const editLabelNamaBarang = document.getElementById('editLabelNamaBarang');
    const editLabelNamaCabang = document.getElementById('editLabelNamaCabang');
    const editLabelSatuan = document.getElementById('editLabelSatuan');
    const inputEditStok = document.getElementById('inputEditStok');
    const inputEditHargaJual = document.getElementById('inputEditHargaJual');
    const inputEditHargaAwal = document.getElementById('inputEditHargaAwal');

    document.querySelectorAll('.btn-edit-stok').forEach(btn => {
        btn.addEventListener('click', function () {
            const cabangId = this.getAttribute('data-cabang-id');
            const barangId = this.getAttribute('data-barang-id');
            const satuanId = this.getAttribute('data-satuan-id');
            const namaBarang = this.getAttribute('data-nama-barang');
            const namaCabang = this.getAttribute('data-nama-cabang');
            const satuan = this.getAttribute('data-satuan');
            const stok = Number(this.getAttribute('data-stok') || 0);
            const avgHarga = Number(this.getAttribute('data-avg-harga') || 0);
            const hargaJual = Number(this.getAttribute('data-harga-jual') || 0);

            if (formEdit) {
                formEdit.action = `{{ url('/admin/cafe/stok') }}/${cabangId}/${barangId}/${satuanId}`;
            }

            if (editLabelNamaBarang) editLabelNamaBarang.textContent = namaBarang;
            if (editLabelNamaCabang) editLabelNamaCabang.textContent = namaCabang;
            if (editLabelSatuan) editLabelSatuan.textContent = satuan;

            if (inputEditStok) inputEditStok.value = Number.isInteger(stok) ? stok : stok.toFixed(2);
            if (inputEditHargaJual) inputEditHargaJual.value = Math.round(hargaJual);
            if (inputEditHargaAwal) inputEditHargaAwal.value = Math.round(avgHarga);

            if (modalEditInstance) modalEditInstance.show();
        });
    });

    // -------------------------------------------------------------
    // 3. AKSI HAPUS
    // -------------------------------------------------------------
    const modalHapusEl = document.getElementById('modalHapusStok');
    const modalHapusInstance = modalHapusEl ? bootstrap.Modal.getOrCreateInstance(modalHapusEl) : null;
    const formHapus = document.getElementById('formHapusStok');
    const hapusLabelNamaBarang = document.getElementById('hapusLabelNamaBarang');
    const hapusLabelNamaCabang = document.getElementById('hapusLabelNamaCabang');

    document.querySelectorAll('.btn-hapus-stok').forEach(btn => {
        btn.addEventListener('click', function () {
            const cabangId = this.getAttribute('data-cabang-id');
            const barangId = this.getAttribute('data-barang-id');
            const satuanId = this.getAttribute('data-satuan-id');
            const namaBarang = this.getAttribute('data-nama-barang');
            const namaCabang = this.getAttribute('data-nama-cabang');

            if (formHapus) {
                formHapus.action = `{{ url('/admin/cafe/stok') }}/${cabangId}/${barangId}/${satuanId}`;
            }

            if (hapusLabelNamaBarang) hapusLabelNamaBarang.textContent = namaBarang;
            if (hapusLabelNamaCabang) hapusLabelNamaCabang.textContent = namaCabang;

            if (modalHapusInstance) modalHapusInstance.show();
        });
    });
});
</script>
@endpush
