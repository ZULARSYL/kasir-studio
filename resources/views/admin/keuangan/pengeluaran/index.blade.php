@extends('template.index')

@section('title', 'Pengeluaran Operasional | Keuangan Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div>
                    <div class="fw-semibold">Gagal memproses data pengeluaran:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Keuangan</li>
                    <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Pengeluaran</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Pengeluaran Operasional</h3>
            <p class="text-muted small mb-0">Pencatatan biaya pengeluaran operasional studio.</p>
        </div>
        <div>
            <button type="button" class="btn btn-danger px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahPengeluaran">
                <i class="bi bi-dash-circle-fill fs-5"></i>
                <span class="fw-semibold">Catat Pengeluaran Baru</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-danger-subtle text-danger d-flex align-items-center justify-content-center p-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-graph-down-arrow fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Total Pengeluaran</span>
                        <h4 class="fw-bold text-danger mb-0 font-monospace">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-warning-subtle text-warning d-flex align-items-center justify-content-center p-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-calendar-x-fill fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Pengeluaran Bulan Ini</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center p-3" style="width: 54px; height: 54px;">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Jumlah Catatan Transaksi</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.keuangan.pengeluaran') }}" method="GET" class="row g-2 align-items-end">
                <!-- Filter Cabang -->
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Toko Cabang</label>
                    <select name="toko_cabang_id" class="form-select form-select-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($tokoCabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ request('toko_cabang_id') == $cabang->id ? 'selected' : '' }}>
                                {{ $cabang->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kategori Jenis Biaya -->
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Kategori Biaya</label>
                    <select name="jenis_biaya_id" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($jenisBiayas as $jb)
                            <option value="{{ $jb->id }}" {{ request('jenis_biaya_id') == $jb->id ? 'selected' : '' }}>
                                {{ $jb->kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tanggal Mulai -->
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>

                <!-- Filter Tanggal Sampai -->
                <div class="col-12 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>

                <!-- Filter Search -->
                <div class="col-12 col-md-8 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Pencarian Keterangan</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari rincian pengeluaran..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Submit & Reset -->
                <div class="col-12 col-md-4 col-lg-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-danger w-100" title="Terapkan Filter">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                    @if(request()->filled('search') || request()->filled('toko_cabang_id') || request()->filled('jenis_biaya_id') || request()->filled('start_date') || request()->filled('end_date'))
                        <a href="{{ route('admin.keuangan.pengeluaran') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-muted small fw-bold" style="width: 60px;">NO</th>
                        <th class="text-muted small fw-bold">TANGGAL</th>
                        <th class="text-muted small fw-bold">CABANG</th>
                        <th class="text-muted small fw-bold">KATEGORI</th>
                        <th class="text-muted small fw-bold">NOMINAL</th>
                        <th class="text-muted small fw-bold">KETERANGAN</th>
                        <th class="text-muted small fw-bold">PENCATAT</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 120px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $items->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-calendar-event text-muted"></i>
                                    <span class="fw-medium font-monospace">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $item->tokoCabang?->nama_cabang ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">
                                    {{ $item->jenisBiaya?->kategori ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace fs-6">
                                    - Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $item->keterangan }}">
                                    {{ $item->keterangan ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">
                                    <i class="bi bi-person-fill me-1"></i>{{ $item->user?->name ?? 'Sistem' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPengeluaran{{ $item->id }}" title="Edit Pengeluaran">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusPengeluaran{{ $item->id }}" title="Hapus Pengeluaran">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Pengeluaran -->
                        <div class="modal fade" id="modalEditPengeluaran{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.keuangan.pengeluaran.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom py-3">
                                            <h5 class="modal-title fw-bold text-heading">
                                                <i class="bi bi-pencil-square text-danger me-2"></i>Edit Biaya Pengeluaran
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <!-- Tanggal -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event"></i></span>
                                                    <input type="date" name="tanggal" class="form-control border-start-0" value="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}" required>
                                                </div>
                                            </div>

                                            <!-- Cabang -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Outlet <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                                                    <select name="toko_cabang_id" class="form-select border-start-0" required>
                                                        <option value="" disabled> Pilih Outlet </option>
                                                        @foreach($tokoCabangs as $cabang)
                                                            <option value="{{ $cabang->id }}" {{ $item->toko_cabang_id == $cabang->id ? 'selected' : '' }}>
                                                                {{ $cabang->nama_cabang }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Kategori (Diambil dari Master Jenis Biaya) -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Kategori Biaya <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag"></i></span>
                                                    <select name="jenis_biaya_id" class="form-select border-start-0" required>
                                                        <option value="" disabled> Pilih Kategori (Master Jenis Biaya) </option>
                                                        @foreach($jenisBiayas as $jb)
                                                            <option value="{{ $jb->id }}" {{ $item->jenis_biaya_id == $jb->id ? 'selected' : '' }}>
                                                                {{ $jb->kategori }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Nominal -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Nominal Biaya (Rp) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0 fw-bold">Rp</span>
                                                    <input type="number" name="nominal" class="form-control border-start-0 font-monospace" min="1" step="any" value="{{ round($item->nominal) }}" required>
                                                </div>
                                            </div>

                                            <!-- Keterangan -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Keterangan / Rincian</label>
                                                <textarea name="keterangan" class="form-control rounded-3" rows="3" placeholder="Rincian pos pengeluaran operasional (cth: listrik, wifi, gaji kasir)...">{{ $item->keterangan }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-danger rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success rounded-3 px-4 fw-semibold">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Hapus Pengeluaran -->
                        <div class="modal fade" id="modalHapusPengeluaran{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.keuangan.pengeluaran.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-3 text-danger">
                                                <i class="bi bi-exclamation-triangle-fill display-4"></i>
                                            </div>
                                            <h5 class="fw-bold mb-2">Konfirmasi Hapus</h5>
                                            <p class="text-muted small mb-3">
                                                Yakin ingin menghapus catatan biaya pengeluaran sebesar 
                                                <strong class="text-dark">Rp {{ number_format($item->nominal, 0, ',', '.') }}</strong> 
                                                ({{ $item->jenisBiaya?->kategori ?? '-' }})?
                                            </p>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button type="button" class="btn btn-light rounded-3 px-3 w-50" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger rounded-3 px-3 w-50 fw-semibold">Ya, Hapus</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-receipt-cutoff display-5 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold">Belum Ada Data Pengeluaran</h6>
                                    <p class="small text-muted mb-3">Catatan biaya pengeluaran operasional studio & cafe akan ditampilkan di sini.</p>
                                    <button type="button" class="btn btn-danger btn-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#modalTambahPengeluaran">
                                        <i class="bi bi-dash-circle me-1"></i> Catat Pengeluaran Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">
                        Menampilkan {{ $items->firstItem() ?? 0 }} sampai {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
                    </span>
                    <div>
                        {{ $items->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="modalTambahPengeluaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.keuangan.pengeluaran.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-heading">
                        <i class="bi bi-dash-circle-fill text-danger me-2"></i>Catat Pengeluaran Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Tanggal -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" name="tanggal" class="form-control border-start-0" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Toko Cabang -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Toko Cabang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                            <select name="toko_cabang_id" class="form-select border-start-0" required>
                                <option value="" selected disabled>-- Pilih Cabang --</option>
                                @foreach($tokoCabangs as $cabang)
                                    <option value="{{ $cabang->id }}" {{ old('toko_cabang_id') == $cabang->id ? 'selected' : '' }}>
                                        {{ $cabang->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Kategori (Master Jenis Biaya) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Kategori Biaya Operasional <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag"></i></span>
                            <select name="jenis_biaya_id" class="form-select border-start-0" required>
                                <option value="" selected disabled>-- Pilih Kategori (Master Jenis Biaya) --</option>
                                @foreach($jenisBiayas as $jb)
                                    <option value="{{ $jb->id }}" {{ old('jenis_biaya_id') == $jb->id ? 'selected' : '' }}>
                                        {{ $jb->kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text small">Kategori diambil dari master data Jenis Biaya (misal: Operasional, Listrik, Sewa, Gaji).</div>
                    </div>

                    <!-- Nominal -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nominal Biaya (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold">Rp</span>
                            <input type="number" name="nominal" class="form-control border-start-0 font-monospace" placeholder="Contoh: 150000" min="1" step="any" value="{{ old('nominal') }}" required>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Keterangan / Rincian</label>
                        <textarea name="keterangan" class="form-control rounded-3" rows="3" placeholder="Rincian pengeluaran (misal: Tagihan listrik studio, wifi, maintenance sofa room)...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
