@extends('template.index')

@section('title', 'Laporan Pembelian Barang (Faktur) | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Laporan Pembelian</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Laporan Pembelian Barang</h3>
            <p class="text-muted small mb-0">Laporan transaksi pengadaan dan pembelian stok barang.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.laporan.pembelian.export', request()->query()) }}" class="btn btn-success px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" target="_blank">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span class="fw-semibold">Export Excel (.xls)</span>
            </a>
        </div>
    </div>

    <!-- Filter Card: Outlet + Periode (Perhari, Perminggu, Perbulan) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.laporan.pembelian') }}" id="filterFormPembelian">
                <div class="row g-3 align-items-end">
                    <!-- Pilih Toko Cabang -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-shop me-1 text-emerald"></i> Toko Cabang
                        </label>
                        <select name="toko_cabang_id" class="form-select rounded-3">
                            <option value="all" {{ $selectedCabang === 'all' ? 'selected' : '' }}>-- Semua Outlet --</option>
                            @foreach($tokoCabangs as $cabang)
                                <option value="{{ $cabang->id }}" {{ (string)$selectedCabang === (string)$cabang->id ? 'selected' : '' }}>
                                    {{ $cabang->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilih Tipe Periode -->
                    <div class="col-12 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-calendar3 me-1 text-emerald"></i> Periode
                        </label>
                        <select name="periode" id="periodePembelianSelect" class="form-select rounded-3" onchange="togglePeriodePembelian()">
                            <option value="perhari" {{ $periode === 'perhari' ? 'selected' : '' }}>Perhari</option>
                            <option value="perminggu" {{ $periode === 'perminggu' ? 'selected' : '' }}>Perminggu</option>
                            <option value="perbulan" {{ $periode === 'perbulan' ? 'selected' : '' }}>Perbulan</option>
                        </select>
                    </div>

                    <!-- Input Periode: Perhari -->
                    <div class="col-12 col-md-4 col-lg-3 periode-beli-input" id="inputBeliPerhari" style="{{ $periode === 'perhari' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilih Tanggal</label>
                        <input type="date" name="tanggal" class="form-control rounded-3" value="{{ $tanggal }}">
                    </div>

                    <!-- Input Periode: Perminggu -->
                    <div class="col-12 col-md-4 col-lg-4 periode-beli-input" id="inputBeliPerminggu" style="{{ $periode === 'perminggu' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Rentang Tanggal</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control rounded-start-3" value="{{ $startDate }}">
                            <span class="input-group-text bg-light">s/d</span>
                            <input type="date" name="end_date" class="form-control rounded-end-3" value="{{ $endDate }}">
                        </div>
                    </div>

                    <!-- Input Periode: Perbulan -->
                    <div class="col-12 col-md-4 col-lg-4 periode-beli-input" id="inputBeliPerbulan" style="{{ $periode === 'perbulan' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Bulan & Tahun</label>
                        <div class="input-group">
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
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Filter & Reset -->
                       <!-- Tombol Filter & Reset -->
                    <div class="col-12">
                        <div class="d-flex flex-column flex-sm-row justify-content-sm-start gap-2 pt-2">
                            <button type="submit"
                                    class="btn btn-primary px-4 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('admin.laporan.pembelian') }}"
                               class="btn btn-outline-secondary px-4 rounded-3 d-flex align-items-center justify-content-center gap-2"
                               title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan KPI Cards -->
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
    <!-- Total Nilai Pembelian -->
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
            <div class="card-body p-3 overflow-hidden">
                <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Pembelian</div>
                <h4 class="fw-bold text-heading font-monospace mb-0 text-emerald text-truncate">
                    Rp {{ number_format($totalPembelian, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Total Faktur -->
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
            <div class="card-body p-3 overflow-hidden">
                <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Faktur</div>
                <h4 class="fw-bold text-heading font-monospace mb-0 text-truncate">
                    {{ number_format($totalFaktur, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Total Kuantitas Barang Masuk -->
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
            <div class="card-body p-3 overflow-hidden">
                <div class="text-muted small fw-semibold text-uppercase tracking-wider">Barang Masuk</div>
                <h4 class="fw-bold text-heading font-monospace mb-0 text-truncate">
                    {{ number_format($totalQtyBarang, $totalQtyBarang == (int)$totalQtyBarang ? 0 : 2, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>
</div>

    <!-- Tabel Faktur Pembelian -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-file-earmark-spreadsheet me-2 text-emerald"></i> Rekap Pembelian Barang
            </h5>
            <span class="badge bg-success text-white fw-semibold">
                Menampilkan {{ $fakturs->total() }} Faktur
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>No. Faktur</th>
                        <th>Tanggal</th>
                        <th>Outlet</th>
                        <th>Supplier</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Nominal</th>
                        <th>Dicatat</th>
                        <th class="text-center pe-4" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fakturs as $index => $faktur)
                        @php
                            $jmlItem = $faktur->details ? $faktur->details->count() : 0;
                            $sumQty = $faktur->details ? $faktur->details->sum('qty') : 0;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted small">{{ $fakturs->firstItem() + $index }}</td>
                            <td>
                                <span class="font-monospace fw-bold text-emerald">{{ $faktur->nomor_faktur }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $faktur->tanggal_faktur ? $faktur->tanggal_faktur->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $faktur->tokoCabang->nama_cabang ?? 'Outlet Umum' }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-heading">{{ $faktur->pemasok ?: 'Supplier Umum' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $jmlItem }} Item</span>
                            </td>
                            <td class="text-center font-monospace">
                                {{ number_format($sumQty, $sumQty == (int)$sumQty ? 0 : 2, ',', '.') }}
                            </td>
                            <td class="text-end font-monospace fw-bold text-emerald">
                                Rp {{ number_format($faktur->total_nominal, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $faktur->user->name ?? $faktur->user->username ?? '-' }}</div>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" onclick="showDetailFaktur({{ $faktur->id }})">
                                    <i class="bi bi-eye-fill me-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold">Belum Ada Faktur Pembelian</h6>
                                    <p class="small text-muted mb-0">Tidak ditemukan data faktur barang yang sesuai dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fakturs->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $fakturs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Detail Faktur Pembelian -->
<div class="modal fade" id="modalDetailFaktur" tabindex="-1" aria-labelledby="modalDetailFakturLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-emerald text-white border-0 py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold" id="modalDetailFakturLabel">
                        <i class="bi bi-box-seam me-2"></i> Rincian Barang Faktur Pembelian
                    </h5>
                    <div class="small opacity-75" id="modalFakturSubtitle">Memuat data...</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Info Faktur -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3">
                        <div class="row g-2 small">
                            <div class="col-6 col-md-3">
                                <div class="text-muted">No. Faktur:</div>
                                <div class="fw-bold font-monospace text-emerald" id="dtFakturNomor">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Tanggal:</div>
                                <div class="fw-bold" id="dtFakturTanggal">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Outlet:</div>
                                <div class="fw-bold" id="dtFakturOutlet">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Pemasok:</div>
                                <div class="fw-bold" id="dtFakturSupplier">-</div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="text-muted">Catatan:</div>
                                <div class="fst-italic text-secondary" id="dtFakturCatatan">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Barang Faktur -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Barang</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Beli</th>
                                    <th class="text-end">Harga Jual</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="dtFakturItemsBody">
                                <tr><td colspan="6" class="text-center py-3 text-muted">Memuat item barang...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Total -->
                <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-4 shadow-sm border">
                    <div class="fw-bold text-heading">Total Faktur:</div>
                    <div class="fs-5 font-monospace fw-bold text-emerald" id="dtFakturGrandTotal">Rp 0</div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white py-2 px-4">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePeriodePembelian() {
        const periode = document.getElementById('periodePembelianSelect').value;
        document.querySelectorAll('.periode-beli-input').forEach(el => el.style.display = 'none');
        if (periode === 'perhari') {
            document.getElementById('inputBeliPerhari').style.display = 'block';
        } else if (periode === 'perminggu') {
            document.getElementById('inputBeliPerminggu').style.display = 'block';
        } else if (periode === 'perbulan') {
            document.getElementById('inputBeliPerbulan').style.display = 'block';
        }
    }

    function showDetailFaktur(fakturId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetailFaktur'));
        modal.show();

        document.getElementById('modalFakturSubtitle').innerText = 'Memuat faktur #' + fakturId + '...';
        document.getElementById('dtFakturItemsBody').innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-emerald me-2"></div>Memuat rincian barang...</td></tr>';

        fetch(`/admin/laporan/pembelian/faktur/${fakturId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const f = data.faktur;
                    const items = data.details || [];

                    document.getElementById('modalFakturSubtitle').innerText = f.nomor_faktur + ' - ' + (f.pemasok || 'Supplier Umum');
                    document.getElementById('dtFakturNomor').innerText = f.nomor_faktur;
                    document.getElementById('dtFakturTanggal').innerText = f.tanggal_faktur ? new Date(f.tanggal_faktur).toLocaleDateString('id-ID') : '-';
                    document.getElementById('dtFakturOutlet').innerText = f.toko_cabang ? f.toko_cabang.nama_cabang : 'Outlet Umum';
                    document.getElementById('dtFakturSupplier').innerText = f.pemasok || 'Supplier Umum';
                    document.getElementById('dtFakturCatatan').innerText = f.catatan || '-';
                    document.getElementById('dtFakturGrandTotal').innerText = 'Rp ' + Number(f.total_nominal).toLocaleString('id-ID');

                    if (items.length === 0) {
                        document.getElementById('dtFakturItemsBody').innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">Tidak ada item rincian barang pada faktur ini.</td></tr>';
                        return;
                    }

                    let rows = '';
                    items.forEach(it => {
                        rows += `<tr>
                            <td class="ps-3 fw-semibold">${it.barang ? it.barang.nama_barang : 'Barang'}</td>
                            <td class="text-center">${it.satuan_barang ? it.satuan_barang.nama_satuan : '-'}</td>
                            <td class="text-center font-monospace">${Number(it.qty)}</td>
                            <td class="text-end font-monospace">Rp ${Number(it.harga_awal).toLocaleString('id-ID')}</td>
                            <td class="text-end font-monospace">Rp ${Number(it.harga_jual).toLocaleString('id-ID')}</td>
                            <td class="text-end pe-3 font-monospace fw-bold">Rp ${Number(it.subtotal).toLocaleString('id-ID')}</td>
                        </tr>`;
                    });
                    document.getElementById('dtFakturItemsBody').innerHTML = rows;
                }
            })
            .catch(err => {
                document.getElementById('dtFakturItemsBody').innerHTML = '<tr><td colspan="6" class="text-center py-3 text-danger">Gagal memuat rincian faktur.</td></tr>';
            });
    }
</script>
@endpush
@endsection
