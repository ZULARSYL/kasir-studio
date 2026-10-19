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
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Stok & Inventaris</li>
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

    <!-- Summary Cards: Total Asset & Total Barang -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        <!-- Card 1: Total Asset -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-emerald-subtle text-emerald rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Nilai Aset</div>
                        <h4 class="fw-bold text-heading font-monospace mb-1 text-emerald">
                            Rp {{ number_format($totalAsset, 0, ',', '.') }}
                        </h4>
                        <div class="text-muted small text-truncate">Akumulasi modal stok faktur</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Barang Fisik -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-boxes fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Kuantitas Barang</div>
                        <h4 class="fw-bold text-heading font-monospace mb-1">
                            {{ number_format($totalBarangFisik, $totalBarangFisik == (int)$totalBarangFisik ? 0 : 2, ',', '.') }}
                        </h4>
                        <div class="text-muted small text-truncate">Total unit fisik dari faktur</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Varian Barang (SKU) -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-tag-fill fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Varian Produk</div>
                        <h4 class="fw-bold text-heading font-monospace mb-1">
                            {{ number_format($totalJenisBarang, 0, ',', '.') }}
                        </h4>
                        <div class="text-muted small text-truncate">Jenis barang cafe aktif</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Outlet Memiliki Stok -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-4 d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-info-subtle text-info rounded-4 d-flex align-items-center justify-content-center">
                        <i class="bi bi-shop fs-2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Outlet Aktif</div>
                        <h4 class="fw-bold text-heading font-monospace mb-1">
                            {{ number_format($totalOutlet, 0, ',', '.') }}
                        </h4>
                        <div class="text-muted small text-truncate">Cabang dengan persediaan stok</div>
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
                <p class="text-muted small mb-0">Data kuantitas stok otomatis terakumulasi dari faktur pembelian/grosir barang.</p>
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
                            <th class="text-muted small fw-bold">NAMA BARANG</th>
                            <th class="text-muted small fw-bold">NAMA OUTLET (CABANG)</th>
                            <th class="text-muted small fw-bold">KATEGORI (MENU CAFE)</th>
                            <th class="text-muted small fw-bold">SATUAN</th>
                            <th class="text-muted small fw-bold text-center">STOK DARI FAKTUR</th>
                            <th class="text-muted small fw-bold text-end">NILAI ASET (MODAL)</th>
                            <th class="text-muted small fw-bold text-end">HARGA JUAL KASIR</th>
                            <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
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
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-emerald rounded-3 btn-riwayat-faktur" 
                                            data-cabang-id="{{ $item->toko_cabang_id }}"
                                            data-barang-id="{{ $item->barang_id }}"
                                            data-satuan-id="{{ $item->satuan_barang_id }}"
                                            data-nama-barang="{{ $item->nama_barang }}"
                                            data-nama-cabang="{{ $item->nama_cabang }}"
                                            data-satuan="{{ $item->nama_satuan }}"
                                            title="Lihat Riwayat Faktur Masuk">
                                        <i class="bi bi-clock-history me-1"></i> Riwayat
                                    </button>
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

<!-- Modal Riwayat Faktur Masuk Barang -->
<div class="modal fade" id="modalRiwayatStok" tabindex="-1" aria-labelledby="modalRiwayatStokLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-heading" id="modalRiwayatStokLabel">
                        <i class="bi bi-clock-history text-emerald me-2"></i>Riwayat Faktur Masuk
                    </h5>
                    <p class="text-muted small mb-0" id="riwayatStokSubheading">Detail faktur pembelian/grosir yang mengisi stok barang ini.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Loading State -->
                <div id="riwayatLoading" class="text-center py-5">
                    <div class="spinner-border text-emerald" role="status">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <div class="text-muted small mt-2">Mengambil riwayat faktur barang...</div>
                </div>

                <!-- Table Riwayat -->
                <div id="riwayatContent" class="table-responsive" style="display: none;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-muted small fw-bold">NO FAKTUR</th>
                                <th class="text-muted small fw-bold">TANGGAL</th>
                                <th class="text-muted small fw-bold">PEMASOK</th>
                                <th class="text-muted small fw-bold text-center">QTY MASUK</th>
                                <th class="text-muted small fw-bold text-end">HARGA MODAL</th>
                                <th class="text-muted small fw-bold text-end">HARGA JUAL</th>
                                <th class="text-muted small fw-bold text-end pe-4">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatTableBody">
                            <!-- Injected by JavaScript -->
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="ps-4 fw-bold text-dark">Total Akumulasi dari Faktur:</td>
                                <td class="text-center fw-bold text-emerald font-monospace" id="riwayatTotalQty">0</td>
                                <td colspan="2"></td>
                                <td class="text-end fw-bold text-emerald font-monospace pe-4" id="riwayatTotalSubtotal">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('admin.cafe.faktur.index') }}" class="btn btn-emerald rounded-3 px-3" id="btnLihatSemuaFaktur">
                    <i class="bi bi-receipt me-1"></i> Buka Menu Faktur
                </a>
            </div>
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
    const modalEl = document.getElementById('modalRiwayatStok');
    const modalInstance = modalEl ? new bootstrap.Modal(modalEl) : null;

    const riwayatLoading = document.getElementById('riwayatLoading');
    const riwayatContent = document.getElementById('riwayatContent');
    const riwayatTableBody = document.getElementById('riwayatTableBody');
    const riwayatSubheading = document.getElementById('riwayatStokSubheading');
    const riwayatTotalQty = document.getElementById('riwayatTotalQty');
    const riwayatTotalSubtotal = document.getElementById('riwayatTotalSubtotal');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    document.querySelectorAll('.btn-riwayat-faktur').forEach(btn => {
        btn.addEventListener('click', function () {
            const cabangId = this.getAttribute('data-cabang-id');
            const barangId = this.getAttribute('data-barang-id');
            const satuanId = this.getAttribute('data-satuan-id');
            const namaBarang = this.getAttribute('data-nama-barang');
            const namaCabang = this.getAttribute('data-nama-cabang');
            const satuan = this.getAttribute('data-satuan');

            if (riwayatSubheading) {
                riwayatSubheading.textContent = `${namaBarang} (${satuan}) di Outlet ${namaCabang}`;
            }

            // Tampilkan loading state
            if (riwayatLoading) riwayatLoading.style.display = 'block';
            if (riwayatContent) riwayatContent.style.display = 'none';
            if (riwayatTableBody) riwayatTableBody.innerHTML = '';

            modalInstance.show();

            const url = `{{ url('/admin/cafe/stok/riwayat') }}/${cabangId}/${barangId}/${satuanId}`;
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (riwayatLoading) riwayatLoading.style.display = 'none';
                    if (riwayatContent) riwayatContent.style.display = 'block';

                    if (res.data && res.data.length > 0) {
                        let rows = '';
                        res.data.forEach(item => {
                            const qty = Number(item.qty);
                            const qtyFormatted = Number.isInteger(qty) ? qty : qty.toFixed(2);
                            rows += `
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-dark">
                                        <a href="{{ url('/admin/cafe/faktur') }}/${item.faktur_id}" class="text-decoration-none text-emerald" target="_blank" title="Buka Faktur">
                                            ${item.nomor_faktur} <i class="bi bi-box-arrow-up-right small"></i>
                                        </a>
                                    </td>
                                    <td class="small text-muted">${item.tanggal_faktur}</td>
                                    <td class="small">${item.pemasok || '-'}</td>
                                    <td class="text-center fw-bold font-monospace">${qtyFormatted} ${item.nama_satuan}</td>
                                    <td class="text-end font-monospace">${formatRupiah(item.harga_awal)}</td>
                                    <td class="text-end font-monospace text-emerald">${formatRupiah(item.harga_jual)}</td>
                                    <td class="text-end pe-4 font-monospace fw-bold">${formatRupiah(item.subtotal)}</td>
                                </tr>
                            `;
                        });
                        riwayatTableBody.innerHTML = rows;

                        const totalQty = Number(res.total_masuk);
                        riwayatTotalQty.textContent = (Number.isInteger(totalQty) ? totalQty : totalQty.toFixed(2)) + ' ' + satuan;
                        riwayatTotalSubtotal.textContent = formatRupiah(res.total_asset);
                    } else {
                        riwayatTableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Tidak ditemukan riwayat faktur untuk item ini.
                                </td>
                            </tr>
                        `;
                        riwayatTotalQty.textContent = '0 ' + satuan;
                        riwayatTotalSubtotal.textContent = 'Rp 0';
                    }
                })
                .catch(err => {
                    if (riwayatLoading) riwayatLoading.style.display = 'none';
                    if (riwayatContent) riwayatContent.style.display = 'block';
                    riwayatTableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger">
                                Terjadi kendala saat memuat riwayat faktur. Silakan coba kembali.
                            </td>
                        </tr>
                    `;
                });
        });
    });
});
</script>
@endpush
