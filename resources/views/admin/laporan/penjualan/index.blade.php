@extends('template.index')

@section('title', 'Laporan Penjualan (Shift Kasir) | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Laporan Penjualan</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Laporan Penjualan</h3>
            <p class="text-muted small mb-0">Laporan akumulasi penjualan saat kasir melakukan tutup toko/shift per cabang atau seluruh outlet.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.laporan.penjualan.export', request()->query()) }}" class="btn btn-success px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" target="_blank">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span class="fw-semibold">Export Excel (.xls)</span>
            </a>
        </div>
    </div>

    <!-- Filter Card: Outlet + Periode (Perhari, Perminggu, Perbulan) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.laporan.penjualan') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Pilih Toko Cabang -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-shop me-1 text-emerald"></i> Toko Cabang
                        </label>
                        <select name="toko_cabang_id" class="form-select rounded-3">
                            <option value="all" {{ $selectedCabang === 'all' ? 'selected' : '' }}>Semua Outlet</option>
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
                        <select name="periode" id="periodeSelect" class="form-select rounded-3" onchange="togglePeriodeInputs()">
                            <option value="perhari" {{ $periode === 'perhari' ? 'selected' : '' }}>Perhari</option>
                            <option value="perminggu" {{ $periode === 'perminggu' ? 'selected' : '' }}>Perminggu</option>
                            <option value="perbulan" {{ $periode === 'perbulan' ? 'selected' : '' }}>Perbulan</option>
                        </select>
                    </div>

                    <!-- Input Periode: Perhari -->
                    <div class="col-12 col-md-4 col-lg-3 periode-input" id="inputPerhari" style="{{ $periode === 'perhari' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilih Tanggal</label>
                        <input type="date" name="tanggal" class="form-control rounded-3" value="{{ $tanggal }}">
                    </div>

                    <!-- Input Periode: Perminggu -->
                    <div class="col-12 col-md-4 col-lg-4 periode-input" id="inputPerminggu" style="{{ $periode === 'perminggu' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Rentang Tanggal</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control rounded-start-3" value="{{ $startDate }}">
                            <span class="input-group-text bg-light">s/d</span>
                            <input type="date" name="end_date" class="form-control rounded-end-3" value="{{ $endDate }}">
                        </div>
                    </div>

                    <!-- Input Periode: Perbulan -->
                    <div class="col-12 col-md-4 col-lg-4 periode-input" id="inputPerbulan" style="{{ $periode === 'perbulan' ? '' : 'display:none;' }}">
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
                    <div class="col-12">
                        <div class="d-flex flex-column flex-sm-row justify-content-sm-start gap-2 pt-2">
                            <button type="submit"
                                    class="btn btn-primary px-4 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Terapkan</span>
                            </button>
                            <a href="{{ route('admin.laporan.penjualan') }}"
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
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
        <!-- Total Omset -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Omset</div>
                            <p class="fw-bold text-heading font-monospace mb-0 text-emerald text-truncate">
                                Rp {{ number_format($totalOmset, 0, ',', '.') }}
                            </p>
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
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Transaksi</div>
                            <p class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                {{ number_format($totalTransaksi, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Trx</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tunai -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Tunai</div>
                            <p class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                Rp {{ number_format($totalTunai, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Non-Tunai -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Non-Tunai</div>
                            <p class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                Rp {{ number_format($totalNonTunai, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Shift Tutup -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="overflow-hidden">
                            <div class="text-muted small fw-semibold text-uppercase tracking-wider">Shift Selesai</div>
                            <p class="fw-bold text-heading font-monospace mb-0 text-truncate">
                                {{ number_format($totalShift, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Shift</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Shift Penjualan -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-heading">
                <i class="bi bi-clock-history me-2 text-emerald"></i> Rekapitulasi Shift Kasir
            </h5>
            <span class="badge bg-success text-white fw-semibold">
                Menampilkan {{ $shifts->total() }} Data Shift Tutup
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Outlet</th>
                        <th>Nama Kasir</th>
                        <th class="text-end">Penjualan</th>
                        <th class="text-end">Tunai</th>
                        <th class="text-end">Non-Tunai</th>
                        <th class="text-center pe-4" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $index => $shift)
                        @php
                            $expectedCash = $shift->modal_awal + $shift->total_tunai;
                            $selisih = $shift->modal_akhir - $expectedCash;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted small">{{ $shifts->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold text-heading">{{ $shift->tokoCabang->nama_cabang ?? 'Outlet Umum' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $shift->user->name ?? $shift->user->username ?? '-' }}</div>
                            </td>
                            <td class="text-end font-monospace fw-bold text-emerald">Rp {{ number_format($shift->total_penjualan, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace small">Rp {{ number_format($shift->total_tunai, 0, ',', '.') }}</td>
                            <td class="text-end font-monospace small">Rp {{ number_format($shift->total_non_tunai, 0, ',', '.') }}</td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" onclick="showDetailShift({{ $shift->id }})">
                                    <i class="bi bi-eye-fill me-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold">Belum Ada Laporan Tutup Kasir</h6>
                                    <p class="small text-muted mb-0">Tidak ada data shift tutup kasir yang sesuai dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shifts->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Detail Transaksi Shift -->
<div class="modal fade" id="modalDetailShift" tabindex="-1" aria-labelledby="modalDetailShiftLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-emerald text-white border-0 py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold" id="modalDetailShiftLabel">
                        <i class="bi bi-receipt-cutoff me-2"></i> Rincian Penjualan Shift Kasir
                    </h5>
                    <div class="small opacity-75" id="modalShiftSubtitle">Memuat data...</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Info Shift Header -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3 small">
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Kasir:</div>
                                <div class="fw-bold" id="modalShiftKasir">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Outlet:</div>
                                <div class="fw-bold" id="modalShiftOutlet">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Modal Awal:</div>
                                <div class="fw-bold text-primary font-monospace" id="modalShiftModalAwal">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted">Modal Akhir Kasir:</div>
                                <div class="fw-bold text-success font-monospace" id="modalShiftModalAkhir">-</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="text-muted">Catatan Buka:</div>
                                <div class="fst-italic text-secondary" id="modalShiftCatatanBuka">-</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="text-muted">Catatan Tutup:</div>
                                <div class="fst-italic text-secondary" id="modalShiftCatatanTutup">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Transaksi dalam shift -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3 px-3">
                        <h6 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-list-check me-2 text-emerald"></i> Daftar Transaksi Pelanggan
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">No. Transaksi</th>
                                    <th>Waktu</th>
                                    <th>Pelanggan</th>
                                    <th>Studio & Film</th>
                                    <th>Cafe / Menu</th>
                                    <th>Metode Bayar</th>
                                    <th class="text-end pe-3">Total Bayar</th>
                                </tr>
                            </thead>
                            <tbody id="modalShiftTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Memuat data transaksi...</td>
                                </tr>
                            </tbody>
                        </table>
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

    function showDetailShift(shiftId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetailShift'));
        modal.show();

        document.getElementById('modalShiftSubtitle').innerText = 'Memuat data shift #' + shiftId + '...';
        document.getElementById('modalShiftTableBody').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-emerald me-2"></div>Memuat transaksi...</td></tr>';

        fetch(`/admin/laporan/penjualan/shift/${shiftId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const shift = data.shift;
                    const transaksis = data.transaksis || [];

                    document.getElementById('modalShiftSubtitle').innerText = 'Shift #' + shift.id + ' | Dibuka: ' + (shift.waktu_buka ? new Date(shift.waktu_buka).toLocaleString('id-ID') : '-') + ' | Ditutup: ' + (shift.waktu_tutup ? new Date(shift.waktu_tutup).toLocaleString('id-ID') : '-');
                    document.getElementById('modalShiftKasir').innerText = shift.user ? (shift.user.name || shift.user.username) : '-';
                    document.getElementById('modalShiftOutlet').innerText = shift.toko_cabang ? shift.toko_cabang.nama_cabang : 'Outlet Umum';
                    document.getElementById('modalShiftModalAwal').innerText = 'Rp ' + Number(shift.modal_awal).toLocaleString('id-ID');
                    document.getElementById('modalShiftModalAkhir').innerText = 'Rp ' + Number(shift.modal_akhir).toLocaleString('id-ID');
                    document.getElementById('modalShiftCatatanBuka').innerText = shift.catatan_buka || '-';
                    document.getElementById('modalShiftCatatanTutup').innerText = shift.catatan_tutup || '-';

                    if (transaksis.length === 0) {
                        document.getElementById('modalShiftTableBody').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada transaksi pada shift ini.</td></tr>';
                        return;
                    }

                    let rows = '';
                    transaksis.forEach(t => {
                        let filmInfo = '-';
                        if (t.film) {
                            filmInfo = `<strong>${t.film.judul}</strong><br><small class="text-muted">${t.room ? t.room.nama_room : ''} (${t.durasi_menit} mnt)</small>`;
                        }

                        let cafeInfo = '-';
                        if (t.details && t.details.length > 0) {
                            cafeInfo = t.details.map(d => `${d.barang ? d.barang.nama_barang : 'Barang'} (${Number(d.qty)}x)`).join(', ');
                        }

                        rows += `<tr>
                            <td class="ps-3 font-monospace fw-semibold text-emerald">${t.nomor_transaksi}</td>
                            <td class="small">${t.waktu_transaksi ? new Date(t.waktu_transaksi).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '-'}</td>
                            <td>${t.nama_pelanggan || 'Umum'}</td>
                            <td>${filmInfo}</td>
                            <td class="small">${cafeInfo}</td>
                            <td><span class="badge bg-light text-dark">${t.tipe_pembayaran ? (t.tipe_pembayaran.metode_pembayaran || t.tipe_pembayaran.nama_tipe) : 'Tunai'}</span></td>
                            <td class="text-end pe-3 font-monospace fw-bold">Rp ${Number(t.total_bayar).toLocaleString('id-ID')}</td>
                        </tr>`;
                    });
                    document.getElementById('modalShiftTableBody').innerHTML = rows;
                }
            })
            .catch(err => {
                document.getElementById('modalShiftTableBody').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat rincian shift.</td></tr>';
            });
    }
</script>
@endpush
@endsection
