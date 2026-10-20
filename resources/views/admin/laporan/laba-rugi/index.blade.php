@extends('template.index')

@section('title', 'Laporan Laba & Rugi | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Laba & Rugi</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Laporan Laba &amp; Rugi</h3>
            <p class="text-muted small mb-0">Rincian laporan laba & rugi.</p>
        </div>
        <div>
            <a href="{{ route('admin.laporan.laba-rugi.export', request()->all()) }}"
               class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-excel fs-5"></i>
                <span class="fw-semibold">Ekspor Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <form action="{{ route('admin.laporan.laba-rugi') }}" method="GET" id="formFilterLabaRugi">
                <div class="row g-3 align-items-end">
                    <!-- Filter Toko Cabang -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Toko Cabang</label>
                        <select name="toko_cabang_id" class="form-select rounded-3">
                            <option value="all" {{ $selectedCabang === 'all' ? 'selected' : '' }}>Semua Toko Cabang</option>
                            @foreach($tokoCabangs as $cabang)
                                <option value="{{ $cabang->id }}" {{ (string)$selectedCabang === (string)$cabang->id ? 'selected' : '' }}>
                                    {{ $cabang->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tipe Periode -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilihan Periode</label>
                        <select name="periode" id="periodeSelect" class="form-select rounded-3" onchange="togglePeriodeInputs()">
                            <option value="perhari" {{ $periode === 'perhari' ? 'selected' : '' }}>Per Hari</option>
                            <option value="perminggu" {{ $periode === 'perminggu' ? 'selected' : '' }}>Per Minggu</option>
                            <option value="perbulan" {{ $periode === 'perbulan' ? 'selected' : '' }}>Per Bulan</option>
                            <option value="pertahun" {{ $periode === 'pertahun' ? 'selected' : '' }}>Per Tahun</option>
                        </select>
                    </div>

                    <!-- Input Periode: Perhari -->
                    <div class="col-12 col-md-4 col-lg-4 periode-input" id="inputPerhari" style="{{ $periode === 'perhari' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilih Tanggal</label>
                        <input type="date" name="tanggal" class="form-control rounded-3" value="{{ $tanggal }}">
                    </div>

                    <!-- Input Periode: Perminggu -->
                    <div class="col-12 col-md-4 col-lg-4 periode-input" id="inputPerminggu" style="{{ $periode === 'perminggu' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Rentang Tanggal (Mingguan)</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control rounded-start-3" value="{{ $startDate }}">
                            <span class="input-group-text bg-light">s/d</span>
                            <input type="date" name="end_date" class="form-control rounded-end-3" value="{{ $endDate }}">
                        </div>
                    </div>

                    <!-- Input Periode: Perbulan -->
                    <div class="col-12 col-md-4 col-lg-4 periode-input" id="inputPerbulan" style="{{ $periode === 'perbulan' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Bulan &amp; Tahun</label>
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
                                @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Input Periode: Pertahun -->
                    <div class="col-12 col-md-4 col-lg-4 periode-input" id="inputPertahun" style="{{ $periode === 'pertahun' ? '' : 'display:none;' }}">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilih Tahun</label>
                        <select name="tahun_pertahun" class="form-select rounded-3">
                            @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Tombol Terapkan & Reset -->
                    <div class="col-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-brand rounded-3 flex-fill py-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-funnel-fill"></i>
                            <span>Terapkan</span>
                        </button>
                        <a href="{{ route('admin.laporan.laba-rugi') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan Cepat KPI Cards -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
        <!-- 1. Total Pendapatan -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-cash-coin fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Total Pendapatan</div>
                        <h5 class="fw-bold text-success font-monospace mb-0 text-truncate">
                            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                        </h5>
                        <span class="text-muted small">{{ $totalTransaksiCount }} Transaksi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. HPP & Laba Kotor -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-graph-up fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Laba Kotor</div>
                        <h5 class="fw-bold text-primary font-monospace mb-0 text-truncate">
                            Rp {{ number_format($labaKotor, 0, ',', '.') }}
                        </h5>
                        <span class="text-muted small">HPP: Rp {{ number_format($hppMakananMinuman, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Beban Operasional -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-danger-subtle text-danger d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Beban Operasional</div>
                        <h5 class="fw-bold text-danger font-monospace mb-0 text-truncate">
                            Rp {{ number_format($totalBebanOperasional, 0, ',', '.') }}
                        </h5>
                        <span class="text-muted small">{{ $totalPengeluaranCount }} Pengeluaran</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Total Netto -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-3 {{ $statusNetto === 'laba' ? 'border-start border-4 border-success' : 'border-start border-4 border-danger' }}">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 {{ $statusNetto === 'laba' ? 'bg-emerald-subtle text-emerald' : 'bg-danger-subtle text-danger' }} d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px;">
                        <i class="bi {{ $statusNetto === 'laba' ? 'bi-trophy-fill' : 'bi-exclamation-octagon-fill' }} fs-3"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-muted small fw-semibold text-uppercase">Total Netto</div>
                        <h5 class="fw-bold {{ $statusNetto === 'laba' ? 'text-emerald' : 'text-danger' }} font-monospace mb-0 text-truncate">
                            {{ $totalNetto < 0 ? '-' : '+' }} Rp {{ number_format(abs($totalNetto), 0, ',', '.') }}
                        </h5>
                        <span class="badge {{ $statusNetto === 'laba' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-0.5 small">
                            {{ $statusNetto === 'laba' ? 'Laba Bersih' : 'Rugi Bersih' }} ({{ $marginNettoPersen }}%)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Report Statement Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <!-- Header Laporan -->
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold text-heading mb-1">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-emerald me-2"></i>Laporan Laba &amp; Rugi Komprehensif
                    </h5>
                    <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                        <span><i class="bi bi-geo-alt-fill text-danger me-1"></i><strong>Outlet:</strong> {{ $namaCabang }}</span>
                        <span><i class="bi bi-calendar3 text-primary me-1"></i><strong>Periode:</strong> {{ $labelPeriode }}</span>
                    </div>
                </div>
                <div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-3">
                        <i class="bi bi-clock me-1"></i>Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-muted small fw-bold" style="width: 60%;">KOMPONEN KEUANGAN</th>
                            <th class="text-end pe-4 text-muted small fw-bold" style="width: 40%;">JUMLAH (RP)</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- ============================================== -->
                        <!-- A. PENDAPATAN USAHA -->
                        <!-- ============================================== -->
                        <tr class="table-secondary bg-opacity-50">
                            <td class="ps-4 fw-bold text-dark fs-6" colspan="2">
                                <i class="bi bi-cash-stack text-success me-2"></i>A. PENDAPATAN USAHA
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-secondary">
                                <i class="bi bi-film me-2 text-muted"></i>Pendapatan Film
                            </td>
                            <td class="text-end pe-4 font-monospace">
                                Rp {{ number_format($pendapatanFilm, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-secondary">
                                <i class="bi bi-cup-hot me-2 text-muted"></i>Pendapatan Kafe
                            </td>
                            <td class="text-end pe-4 font-monospace">
                                Rp {{ number_format($pendapatanKafe, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-secondary">
                                <i class="bi bi-tag me-2 text-danger"></i>Diskon Penjualan
                            </td>
                            <td class="text-end pe-4 font-monospace text-danger">
                                (Rp {{ number_format($diskonPenjualan, 0, ',', '.') }})
                            </td>
                        </tr>
                        <tr class="bg-success-subtle bg-opacity-25 border-top border-bottom">
                            <td class="ps-5 fw-bold text-success">
                                <i class="bi bi-check2-circle me-2"></i>TOTAL PENDAPATAN USAHA
                            </td>
                            <td class="text-end pe-4 fw-bold text-success font-monospace fs-6">
                                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- ============================================== -->
                        <!-- B. HARGA POKOK PENJUALAN (HPP) -->
                        <!-- ============================================== -->
                        <tr class="table-secondary bg-opacity-50">
                            <td class="ps-4 fw-bold text-dark fs-6" colspan="2">
                                <i class="bi bi-box-seam text-primary me-2"></i>B. HARGA POKOK PENJUALAN (HPP)
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-secondary">
                                <i class="bi bi-cart4 me-2 text-muted"></i>HPP Makanan dan Minuman
                            </td>
                            <td class="text-end pe-4 font-monospace text-danger">
                                (Rp {{ number_format($hppMakananMinuman, 0, ',', '.') }})
                            </td>
                        </tr>
                        <tr class="bg-primary-subtle bg-opacity-25 border-top border-bottom">
                            <td class="ps-5 fw-bold text-primary fs-6">
                                <i class="bi bi-graph-up-arrow me-2"></i>LABA KOTOR
                            </td>
                            <td class="text-end pe-4 fw-bold text-primary font-monospace fs-6">
                                Rp {{ number_format($labaKotor, 0, ',', '.') }}
                            </td>
                        </tr>

                        <!-- ============================================== -->
                        <!-- C. BEBAN OPERASIONAL -->
                        <!-- ============================================== -->
                        <tr class="table-secondary bg-opacity-50">
                            <td class="ps-4 fw-bold text-dark fs-6" colspan="2">
                                <i class="bi bi-wallet2 text-danger me-2"></i>C. BEBAN OPERASIONAL
                            </td>
                        </tr>

                        @forelse($bebanKategoriList as $beban)
                            <tr>
                                <td class="ps-5 text-secondary">
                                    <i class="bi bi-dash-circle text-danger me-2"></i>{{ $beban['kategori'] }}
                                    @if($beban['jumlah_transaksi'] > 0)
                                        <span class="badge bg-light text-muted border ms-2 small">{{ $beban['jumlah_transaksi'] }} Catatan</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 font-monospace text-danger">
                                    (Rp {{ number_format($beban['nominal'], 0, ',', '.') }})
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="ps-5 text-muted small fst-italic" colspan="2">Belum ada catatan beban operasional pada periode ini.</td>
                            </tr>
                        @endforelse

                        <tr class="bg-danger-subtle bg-opacity-25 border-top border-bottom">
                            <td class="ps-5 fw-bold text-danger">
                                <i class="bi bi-dash-circle-fill me-2"></i>TOTAL BEBAN OPERASIONAL
                            </td>
                            <td class="text-end pe-4 fw-bold text-danger font-monospace fs-6">
                                (Rp {{ number_format($totalBebanOperasional, 0, ',', '.') }})
                            </td>
                        </tr>

                        <!-- ============================================== -->
                        <!-- TOTAL NETTO (LABA / RUGI BERSIH) -->
                        <!-- ============================================== -->
                        <tr class="{{ $statusNetto === 'laba' ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10' }} border-top border-3 {{ $statusNetto === 'laba' ? 'border-success' : 'border-danger' }}">
                            <td class="ps-4 py-3">
                                <div class="fw-bold {{ $statusNetto === 'laba' ? 'text-success' : 'text-danger' }} fs-5 mb-0">
                                    <i class="bi {{ $statusNetto === 'laba' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} me-2"></i>TOTAL NETTO ({{ $statusNetto === 'laba' ? 'LABA BERSIH' : 'RUGI BERSIH' }})
                                </div>
                                <span class="small text-muted ps-4">Laba Kotor dikurangi Total Beban Operasional | Margin Bersih: <strong>{{ $marginNettoPersen }}%</strong></span>
                            </td>
                            <td class="text-end pe-4 py-3">
                                <div class="fw-bold font-monospace fs-4 {{ $statusNetto === 'laba' ? 'text-success' : 'text-danger' }}">
                                    {{ $totalNetto < 0 ? '-' : '+' }} Rp {{ number_format(abs($totalNetto), 0, ',', '.') }}
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light py-3 px-4 border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small text-muted">
                <span>
                    <i class="bi bi-info-circle me-1"></i> Data dihitung secara realtime berdasarkan transaksi kasir yang selesai dan pengeluaran operasional.
                </span>
                <span class="font-monospace">
                    Status: <strong class="{{ $statusNetto === 'laba' ? 'text-success' : 'text-danger' }}">{{ strtoupper($statusNetto) }}</strong>
                </span>
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
        } else if (periode === 'pertahun') {
            document.getElementById('inputPertahun').style.display = 'block';
        }
    }

    // Handle penyesuaian parameter tahun_pertahun sebelum form disubmit
    document.getElementById('formFilterLabaRugi').addEventListener('submit', function(e) {
        const periode = document.getElementById('periodeSelect').value;
        if (periode === 'pertahun') {
            const tahunInput = document.querySelector('select[name="tahun_pertahun"]');
            if (tahunInput) {
                // Set hidden or select tahun parameter
                let hiddenTahun = document.querySelector('input[type="hidden"][name="tahun"]');
                if (!hiddenTahun) {
                    hiddenTahun = document.createElement('input');
                    hiddenTahun.type = 'hidden';
                    hiddenTahun.name = 'tahun';
                    this.appendChild(hiddenTahun);
                }
                hiddenTahun.value = tahunInput.value;
            }
        }
    });
</script>
@endpush
@endsection
