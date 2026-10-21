@extends('template.index')

@section('title', 'Laporan Stok Barang | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Laporan Stok</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Laporan Stok Barang</h3>
            <p class="text-muted small mb-0">Informasi kuantitas stok barang di sistem, total nilai aset per item, dan grand total keseluruhan stok.</p>
        </div>
        <div>
            <a href="{{ route('admin.laporan.stok.export', request()->all()) }}"
               class="btn btn-success px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-excel fs-5"></i>
                <span class="fw-semibold">Ekspor Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <form action="{{ route('admin.laporan.stok') }}" method="GET" id="formFilterStok">
                <div class="row g-3 align-items-end">
                    <!-- Filter Toko Cabang -->
                    <div class="col-12 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Toko Cabang</label>
                        <select name="toko_cabang_id" class="form-select form-select-sm rounded-3">
                            <option value="all" {{ $selectedCabang === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                            @foreach($tokoCabangs as $cabang)
                                <option value="{{ $cabang->id }}" {{ (string)$selectedCabang === (string)$cabang->id ? 'selected' : '' }}>
                                    {{ $cabang->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tipe Periode -->
                    <div class="col-12 col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilihan Periode</label>
                        <select name="periode" id="periodeSelect" class="form-select form-select-sm rounded-3" onchange="togglePeriodeInputs()">
                            <option value="perhari" {{ $periode === 'perhari' ? 'selected' : '' }}>Per Hari</option>
                            <option value="perminggu" {{ $periode === 'perminggu' ? 'selected' : '' }}>Per Minggu</option>
                            <option value="perbulan" {{ $periode === 'perbulan' ? 'selected' : '' }}>Per Bulan</option>
                            <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua Periode</option>
                        </select>
                    </div>

                    <!-- Input Periode: Perhari -->
                    <div class="col-12 col-md-4 col-lg-3 periode-input" id="inputPerhari" style="{{ $periode === 'perhari' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilih Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm rounded-3" value="{{ $tanggal }}">
                    </div>

                    <!-- Input Periode: Perminggu -->
                    <div class="col-12 col-md-4 col-lg-3 periode-input" id="inputPerminggu" style="{{ $periode === 'perminggu' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Rentang Tanggal</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="start_date" class="form-control rounded-start-3" value="{{ $startDate }}">
                            <span class="input-group-text bg-light">s/d</span>
                            <input type="date" name="end_date" class="form-control rounded-end-3" value="{{ $endDate }}">
                        </div>
                    </div>

                    <!-- Input Periode: Perbulan -->
                    <div class="col-12 col-md-4 col-lg-3 periode-input" id="inputPerbulan" style="{{ $periode === 'perbulan' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Bulan &amp; Tahun</label>
                        <div class="input-group input-group-sm">
                            <select name="bulan" class="form-select">
                                @php
                                    $bulanList = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                @endphp
                                @foreach($bulanList as $num => $nama)
                                    <option value="{{ $num }}" {{ (int)$bulan === $num ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                            <select name="tahun" class="form-select">
                                @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Cari Nama Barang / Kategori</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik nama barang..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Tombol Terapkan & Reset -->
                    <div class="col-12 col-md-6 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 flex-fill py-1.5 shadow-sm d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Terapkan</span>
                        </button>
                        <a href="{{ route('admin.laporan.stok') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1.5" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Stats Cards (Grand Total & Metrics) -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        <!-- 1. Total Nilai Aset -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Total Nilai Aset</div>
                        <h4 class="fw-bold text-success font-monospace mb-0 text-truncate">
                            Rp {{ number_format($grandTotalAset, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Grand Total Kuantitas Stok -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-boxes fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Kuantitas Stok</div>
                        <h4 class="fw-bold text-primary font-monospace mb-0 text-truncate">
                            {{ number_format($grandTotalStok, 0, ',', '.') }} Unit
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Barang -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-4 border-info">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-info-subtle text-info d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-tag-fill fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Total Barang</div>
                        <h4 class="fw-bold text-dark font-monospace mb-0 text-truncate">
                            {{ $totalJenisBarang }} Item
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Toko Cabang -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 border-start border-4 border-warning">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-warning-subtle text-warning d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-buildings-fill fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Outlet</div>
                        <h4 class="fw-bold text-dark font-monospace mb-0 text-truncate">
                            {{ $totalOutlet }} Outlet
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="fw-bold text-heading">
                    <i class="bi bi-box-seam-fill text-emerald me-2"></i>Daftar Rincian Stok Barang
                </div>
                <div class="small text-muted d-flex align-items-center gap-3">
                    <span><i class="bi bi-geo-alt-fill text-danger me-1"></i><strong>Outlet:</strong> {{ $namaCabang }}</span>
                    <span><i class="bi bi-calendar3 text-primary me-1"></i><strong>Periode:</strong> {{ $labelPeriode }}</span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-muted small fw-bold" style="width: 60px;">NO</th>
                        <th class="text-muted small fw-bold">NAMA BARANG</th>
                        <th class="text-muted small fw-bold">KATEGORI</th>
                        <th class="text-muted small fw-bold">OUTLET</th>
                        <th class="text-muted small fw-bold">SATUAN</th>
                        <th class="text-center text-muted small fw-bold">STOK</th>
                        <th class="text-end pe-4 text-muted small fw-bold">TOTAL ASET</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokItems as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $stokItems->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-heading">{{ $item->nama_barang }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                    {{ $item->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $item->nama_cabang }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $item->nama_satuan ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if((float)$item->total_stok > 10)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold font-monospace fs-6">
                                        {{ number_format($item->total_stok, 0, ',', '.') }}
                                    </span>
                                @elseif((float)$item->total_stok > 0)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1.5 fw-bold font-monospace fs-6">
                                        {{ number_format($item->total_stok, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-bold font-monospace fs-6">
                                        0
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4 font-monospace fw-bold text-success fs-6">
                                Rp {{ number_format($item->total_asset_item, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-box-seam display-4 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold">Tidak Ada Data Stok Ditemukan</h6>
                                    <p class="small text-muted mb-0">Silakan ubah filter periode atau pilih toko cabang lainnya.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($stokItems->isNotEmpty())
                    <tfoot class="table-light border-top border-2">
                        <tr class="fw-bold">
                            <td colspan="5" class="ps-4 text-dark py-3">
                                <i class="bi bi-calculator-fill text-emerald me-2"></i>GRAND TOTAL KESELURUHAN STOK
                            </td>
                            <td class="text-center py-3 text-primary font-monospace fs-6">
                                {{ number_format($grandTotalStok, 0, ',', '.') }} Unit
                            </td>
                            <td class="text-end pe-4 py-3 text-success font-monospace fs-5">
                                Rp {{ number_format($grandTotalAset, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($stokItems->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Menampilkan {{ $stokItems->firstItem() ?? 0 }} sampai {{ $stokItems->lastItem() ?? 0 }} dari {{ $stokItems->total() }} data
                    </span>
                    <div>
                        {{ $stokItems->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    function togglePeriodeInputs() {
        const periode = document.getElementById('periodeSelect').value;
        document.querySelectorAll('.periode-input').forEach(el => el.style.display = 'none');
        if (periode === 'perhari') {
            document.getElementById('inputPerhari').style.display = 'block';
        } else if (periode === 'perminggu') {
            document.getElementById('inputPerminggu').style.display = 'block';
        } else if (periode === 'perbulan') {
            document.getElementById('inputPerbulan').style.display = 'block';
        }
    }
</script>
@endpush
@endsection
