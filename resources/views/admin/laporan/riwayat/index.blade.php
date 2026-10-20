@extends('template.index')

@section('title', 'Riwayat Penjualan Harian | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Riwayat Penjualan</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Riwayat Transaksi Penjualan</h3>
            <p class="text-muted small mb-0">Daftar transaksi harian kasir per pelanggan, studio film, dan pesanan cafe yang dapat difilter harian dan diexport ke Excel.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.laporan.riwayat.export', request()->query()) }}" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" target="_blank">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span class="fw-semibold">Export Excel (.xls)</span>
            </a>
        </div>
    </div>

    <!-- Filter Card: Harian (Perhari), Outlet, dan Pencarian -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.laporan.riwayat') }}">
                <div class="row g-3 align-items-end">
                    <!-- Filter Tanggal (Perhari) -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-calendar-event me-1 text-emerald"></i> Pilih Tanggal (Harian)
                        </label>
                        <input type="date" name="tanggal" class="form-control rounded-3" value="{{ $tanggal }}">
                    </div>

                    <!-- Filter Toko Cabang -->
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

                    <!-- Pencarian No Transaksi / Pelanggan -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-search me-1 text-emerald"></i> Cari Transaksi / Pelanggan
                        </label>
                        <input type="text" name="search" class="form-control rounded-3" placeholder="No. TRX atau Nama Pelanggan..." value="{{ $search }}">
                    </div>

                    <!-- Tombol Terapkan & Reset -->
                    <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 rounded-3 shadow-sm d-flex align-items-center gap-2">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Terapkan</span>
                        </button>
                        <a href="{{ route('admin.laporan.riwayat') }}" class="btn btn-outline-secondary px-3 rounded-3 shadow-sm d-flex align-items-center gap-2" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
        <!-- Total Omset -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-emerald-subtle text-emerald rounded-4 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-cash-coin fs-3"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Penjualan</div>
                            <h4 class="fw-bold text-heading font-monospace mb-0 text-emerald text-truncate">
                                Rp {{ number_format($totalOmset, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-receipt-cutoff fs-3"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Jumlah Transaksi</div>
                            <h4 class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                {{ number_format($totalTransaksi, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Trx</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tiket Studio / Film -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-info-subtle text-info rounded-4 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-film fs-3"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Tiket Studio</div>
                            <h4 class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                Rp {{ number_format($totalTiketStudio, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cafe / Menu -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning rounded-4 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-cup-hot-fill fs-3"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Menu Cafe</div>
                            <h4 class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                Rp {{ number_format($totalCafe, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diskon -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger rounded-4 d-flex align-items-center justify-content-center p-3">
                            <i class="bi bi-tag-fill fs-3"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Diskon</div>
                            <h4 class="fw-bold text-heading font-monospace mb-0 text-truncate text-danger">
                                Rp {{ number_format($totalDiskon, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Transaksi -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-journal-text me-2 text-emerald"></i> Transaksi Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
            </h5>
            <span class="badge bg-light text-muted fw-semibold">
                {{ $transaksis->total() }} Transaksi
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>No. Transaksi</th>
                        <th>Jam</th>
                        <th>Outlet Cabang</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Studio & Film</th>
                        <th>Menu Cafe</th>
                        <th>Metode Bayar</th>
                        <th class="text-end">Total Bayar</th>
                        <th class="text-center pe-4" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $index => $trx)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $transaksis->firstItem() + $index }}</td>
                            <td>
                                <span class="font-monospace fw-bold text-emerald">{{ $trx->nomor_transaksi }}</span>
                            </td>
                            <td class="small text-muted font-monospace">
                                {{ $trx->waktu_transaksi ? $trx->waktu_transaksi->format('H:i') : '-' }} WIB
                            </td>
                            <td>
                                <span class="small fw-semibold">{{ $trx->tokoCabang->nama_cabang ?? 'Outlet Umum' }}</span>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $trx->user->name ?? $trx->user->username ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $trx->nama_pelanggan ?: 'Umum' }}</div>
                            </td>
                            <td>
                                @if($trx->film)
                                    <div class="fw-semibold small text-truncate" style="max-width: 180px;" title="{{ $trx->film->judul }}">
                                        {{ $trx->film->judul }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="bi bi-door-closed me-1"></i>{{ $trx->room->nama_room ?? 'Studio' }} ({{ $trx->durasi_menit }} mnt)
                                    </div>
                                @else
                                    <span class="text-muted small">- Hanya Cafe -</span>
                                @endif
                            </td>
                            <td>
                                @if($trx->details && $trx->details->count() > 0)
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 small">
                                        {{ $trx->details->count() }} Item Cafe
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark fw-semibold">
                                    {{ $trx->tipePembayaran->metode_pembayaran ?? $trx->tipePembayaran->nama_tipe ?? 'Tunai' }}
                                </span>
                            </td>
                            <td class="text-end font-monospace fw-bold text-emerald">
                                Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" onclick="showDetailTransaksi({{ $trx->id }})">
                                    <i class="bi bi-eye-fill me-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold">Tidak Ada Riwayat Transaksi</h6>
                                    <p class="small text-muted mb-0">Belum ada transaksi kasir pada tanggal dan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transaksis->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Detail Transaksi Lengkap -->
<div class="modal fade" id="modalDetailTransaksi" tabindex="-1" aria-labelledby="modalDetailTransaksiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-emerald text-white border-0 py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold" id="modalDetailTransaksiLabel">
                        <i class="bi bi-receipt me-2"></i> Rincian Struk Transaksi
                    </h5>
                    <div class="small opacity-75" id="modalTrxSubtitle">Memuat data...</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Info Nota -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3">
                        <div class="row g-2 small">
                            <div class="col-6 col-md-3">
                                <div class="text-muted">No. Transaksi:</div>
                                <div class="fw-bold font-monospace text-emerald" id="dtTrxNomor">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Waktu Transaksi:</div>
                                <div class="fw-bold" id="dtTrxWaktu">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Pelanggan:</div>
                                <div class="fw-bold" id="dtTrxPelanggan">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Kasir / Outlet:</div>
                                <div class="fw-bold" id="dtTrxKasirOutlet">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Studio & Film -->
                <div class="card border-0 shadow-sm rounded-4 mb-3" id="cardStudioDetail">
                    <div class="card-header bg-white border-0 py-2 px-3 fw-bold small text-muted text-uppercase">
                        <i class="bi bi-film me-1 text-emerald"></i> Tiket Studio & Pemutaran Film
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1" id="dtFilmJudul">-</h6>
                                <div class="small text-muted" id="dtFilmDetail">-</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold font-monospace" id="dtFilmSubtotal">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Item Cafe -->
                <div class="card border-0 shadow-sm rounded-4 mb-3" id="cardCafeDetail">
                    <div class="card-header bg-white border-0 py-2 px-3 fw-bold small text-muted text-uppercase">
                        <i class="bi bi-cup-straw me-1 text-emerald"></i> Pesanan Menu Cafe
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Menu</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="dtCafeTableBody">
                                <tr><td colspan="4" class="text-center py-3 text-muted">Tidak ada pesanan cafe.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rincian Pembayaran & Diskon -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Subtotal Film & Studio:</span>
                            <span class="font-monospace fw-semibold" id="dtSubtotalFilm">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Subtotal Cafe:</span>
                            <span class="font-monospace fw-semibold" id="dtSubtotalCafe">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small text-danger" id="dtDiskonRow">
                            <span>Diskon / Promo (<span id="dtPromoName">-</span>):</span>
                            <span class="font-monospace fw-semibold" id="dtDiskonNominal">-Rp 0</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between py-1 fs-5 fw-bold">
                            <span class="text-heading">Total Pembayaran:</span>
                            <span class="font-monospace text-emerald" id="dtTotalBayar">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Metode Bayar:</span>
                            <span class="fw-semibold" id="dtTipeBayar">Tunai</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Uang Diterima:</span>
                            <span class="font-monospace" id="dtUangDiterima">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Kembalian:</span>
                            <span class="font-monospace text-success fw-bold" id="dtKembalian">Rp 0</span>
                        </div>
                    </div>
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
    function showDetailTransaksi(trxId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetailTransaksi'));
        modal.show();

        fetch(`/admin/laporan/riwayat/detail/${trxId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const trx = data.transaksi;

                    document.getElementById('modalTrxSubtitle').innerText = trx.nomor_transaksi + ' - ' + (trx.nama_pelanggan || 'Pelanggan Umum');
                    document.getElementById('dtTrxNomor').innerText = trx.nomor_transaksi;
                    document.getElementById('dtTrxWaktu').innerText = trx.waktu_transaksi ? new Date(trx.waktu_transaksi).toLocaleString('id-ID') : '-';
                    document.getElementById('dtTrxPelanggan').innerText = trx.nama_pelanggan || 'Umum';
                    document.getElementById('dtTrxKasirOutlet').innerText = (trx.user ? (trx.user.name || trx.user.username) : '-') + ' / ' + (trx.toko_cabang ? trx.toko_cabang.nama_cabang : '-');

                    // Film Studio Card
                    if (trx.film) {
                        document.getElementById('cardStudioDetail').style.display = 'block';
                        document.getElementById('dtFilmJudul').innerText = trx.film.judul;
                        document.getElementById('dtFilmDetail').innerText = (trx.room ? trx.room.nama_room : 'Studio') + ' | Durasi: ' + trx.durasi_menit + ' menit';
                        document.getElementById('dtFilmSubtotal').innerText = 'Rp ' + Number(trx.subtotal_film_room).toLocaleString('id-ID');
                    } else {
                        document.getElementById('cardStudioDetail').style.display = 'none';
                    }

                    // Cafe Items Card
                    const cafeTbody = document.getElementById('dtCafeTableBody');
                    if (trx.details && trx.details.length > 0) {
                        let cafeRows = '';
                        trx.details.forEach(item => {
                            cafeRows += `<tr>
                                <td class="ps-3 fw-semibold">${item.barang ? item.barang.nama_barang : 'Barang'}</td>
                                <td class="text-center font-monospace">${Number(item.qty)} ${item.satuan_barang ? item.satuan_barang.nama_satuan : ''}</td>
                                <td class="text-end font-monospace">Rp ${Number(item.harga_satuan).toLocaleString('id-ID')}</td>
                                <td class="text-end pe-3 font-monospace fw-bold">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                            </tr>`;
                        });
                        cafeTbody.innerHTML = cafeRows;
                        document.getElementById('cardCafeDetail').style.display = 'block';
                    } else {
                        cafeTbody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted">Tidak ada pesanan cafe.</td></tr>';
                    }

                    // Totals
                    document.getElementById('dtSubtotalFilm').innerText = 'Rp ' + Number(trx.subtotal_film_room).toLocaleString('id-ID');
                    document.getElementById('dtSubtotalCafe').innerText = 'Rp ' + Number(trx.subtotal_cafe).toLocaleString('id-ID');

                    if (Number(trx.diskon) > 0) {
                        document.getElementById('dtDiskonRow').style.display = 'flex';
                        document.getElementById('dtPromoName').innerText = trx.promo ? trx.promo.nama_promo : 'Voucher';
                        document.getElementById('dtDiskonNominal').innerText = '-Rp ' + Number(trx.diskon).toLocaleString('id-ID');
                    } else {
                        document.getElementById('dtDiskonRow').style.display = 'none';
                    }

                    document.getElementById('dtTotalBayar').innerText = 'Rp ' + Number(trx.total_bayar).toLocaleString('id-ID');
                    document.getElementById('dtTipeBayar').innerText = trx.tipe_pembayaran ? (trx.tipe_pembayaran.metode_pembayaran || trx.tipe_pembayaran.nama_tipe) : 'Tunai';
                    document.getElementById('dtUangDiterima').innerText = 'Rp ' + Number(trx.uang_diterima).toLocaleString('id-ID');
                    document.getElementById('dtKembalian').innerText = 'Rp ' + Number(trx.kembalian).toLocaleString('id-ID');
                }
            })
            .catch(err => {
                alert('Gagal memuat detail transaksi.');
            });
    }
</script>
@endpush
@endsection
