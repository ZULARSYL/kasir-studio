@extends('template.index')

@section('title', 'Master Data Shift | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Master Data</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Master Data Shift</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Master Data Shift &amp; Operasional Kasir</h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahShift">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Tambah Shift Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-emerald-subtle text-emerald p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Master Shift</div>
                        <h4 class="fw-bold mb-0 text-heading">{{ $totalShift }} Shift</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-person-workspace fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Shift Berjalan Saat Ini</div>
                        <h4 class="fw-bold mb-0 text-primary">{{ $activeBranchShifts->count() }} Outlet</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Master Shift, Monitoring Shift Aktif, Riwayat Pergantian Shift) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-2">
        <ul class="nav nav-pills gap-2" id="shiftTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-3 px-3.5 py-2 fw-semibold d-flex align-items-center gap-2" id="tab-master-btn" data-bs-toggle="pill" data-bs-target="#tab-master-pane" type="button" role="tab">
                    <i class="bi bi-gear-wide-connected"></i>
                    <span>Konfigurasi Master Shift</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $items->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-3 px-3.5 py-2 fw-semibold d-flex align-items-center gap-2" id="tab-monitoring-btn" data-bs-toggle="pill" data-bs-target="#tab-monitoring-pane" type="button" role="tab">
                    <i class="bi bi-broadcast text-danger"></i>
                    <span>Monitoring Kasir Aktif</span>
                    @if($activeBranchShifts->count() > 0)
                        <span class="badge bg-danger text-white rounded-pill ms-1">{{ $activeBranchShifts->count() }} Buka</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-3 px-3.5 py-2 fw-semibold d-flex align-items-center gap-2" id="tab-riwayat-btn" data-bs-toggle="pill" data-bs-target="#tab-riwayat-pane" type="button" role="tab">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat Pergantian Shift</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="shiftTabsContent">
        <!-- ================================================================= -->
        <!-- TAB 1: KONFIGURASI MASTER SHIFT                                   -->
        <!-- ================================================================= -->
        <div class="tab-pane fade show active" id="tab-master-pane" role="tabpanel">
            <!-- Filter & Search Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white">
                <div class="card-body p-3">
                    <form action="{{ route('admin.master.shift.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                       placeholder="Cari nama shift, jam operasional, atau keterangan..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select text-muted" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-emerald flex-fill">Filter</button>
                            @if(request()->anyFilled(['search', 'status']))
                                <a href="{{ route('admin.master.shift.index') }}" class="btn btn-outline-secondary" title="Reset">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card Master Shift -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-muted small fw-bold">NAMA SHIFT</th>
                                <th class="text-muted small fw-bold">JADWAL OPERASIONAL</th>
                                <th class="text-muted small fw-bold">ATURAN MODAL AWAL</th>
                                <th class="text-muted small fw-bold">KETERANGAN OPERASIONAL</th>
                                <th class="text-center text-muted small fw-bold" style="width: 120px;">STATUS</th>
                                <th class="text-end pe-4 text-muted small fw-bold" style="width: 130px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $shift)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-heading fs-6 d-flex align-items-center gap-2">
                                            <span>{{ $shift->nama_shift }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill font-monospace fw-semibold fs-7">
                                            <i class="bi bi-alarm me-1 text-primary"></i>{{ $shift->formatted_jadwal }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($shift->isOtomatisModal())
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold font-sm">
                                                <i class="bi bi-arrow-repeat me-1"></i>Otomatis Saldo Akhir Shift 1
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-semibold font-sm">
                                                <i class="bi bi-pencil-square me-1"></i>Input Manual oleh Kasir
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $shift->keterangan ?: '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.master.shift.toggle-status', $shift->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm border-0 p-0" title="Klik untuk mengubah status">
                                                @if($shift->isActive())
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                                        <i class="bi bi-x-circle me-1"></i>Nonaktif
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditShift{{ $shift->id }}" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteShift{{ $shift->id }}" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Shift -->
                                <div class="modal fade" id="modalEditShift{{ $shift->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-emerald text-white border-0 py-3 px-4">
                                                <h5 class="modal-title fw-bold">Edit Master Shift &mdash; {{ $shift->nama_shift }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.master.shift.update', $shift->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted">Nama Shift <span class="text-danger">*</span></label>
                                                        <input type="text" name="nama_shift" class="form-control rounded-3" value="{{ old('nama_shift', $shift->nama_shift) }}" required>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small text-muted">Jam Mulai <span class="text-danger">*</span></label>
                                                            <input type="text" name="jam_mulai" class="form-control rounded-3 font-monospace" placeholder="11:00" value="{{ old('jam_mulai', $shift->jam_mulai) }}" required>
                                                            <div class="form-text small">Format: HH:mm (WIB)</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small text-muted">Jam Selesai <span class="text-danger">*</span></label>
                                                            <input type="text" name="jam_selesai" class="form-control rounded-3 font-monospace" placeholder="16:00" value="{{ old('jam_selesai', $shift->jam_selesai) }}" required>
                                                            <div class="form-text small">Format: HH:mm (WIB)</div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-muted">Aturan Modal Awal <span class="text-danger">*</span></label>
                                                        <select name="aturan_modal_awal" class="form-select rounded-3" required>
                                                            <option value="manual" {{ old('aturan_modal_awal', $shift->aturan_modal_awal) === 'manual' ? 'selected' : '' }}>
                                                                Input Manual oleh Kasir (Shift 1)
                                                            </option>
                                                            <option value="otomatis_sebelumnya" {{ old('aturan_modal_awal', $shift->aturan_modal_awal) === 'otomatis_sebelumnya' ? 'selected' : '' }}>
                                                                Otomatis Saldo Akhir Tutup Kasir Shift 1 (Shift 2)
                                                            </option>
                                                        </select>
                                                        <div class="form-text small">Tentukan apakah nominal modal awal diisi manual atau diambil otomatis dari shift sebelumnya.</div>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small text-muted">Urutan Shift</label>
                                                            <input type="number" name="urutan" class="form-control rounded-3" value="{{ old('urutan', $shift->urutan) }}" min="1">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small text-muted">Status</label>
                                                            <select name="status" class="form-select rounded-3">
                                                                <option value="aktif" {{ old('status', $shift->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="nonaktif" {{ old('status', $shift->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label fw-bold small text-muted">Keterangan / Deskripsi</label>
                                                        <textarea name="keterangan" class="form-control rounded-3" rows="2">{{ old('keterangan', $shift->keterangan) }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 bg-light py-2 px-4">
                                                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus Shift -->
                                <div class="modal fade" id="modalDeleteShift{{ $shift->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                                                <h6 class="modal-title fw-bold">Konfirmasi Hapus</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 text-center">
                                                <i class="bi bi-exclamation-circle text-danger display-4 mb-3 d-block"></i>
                                                <p class="small text-muted mb-0">Apakah Anda yakin ingin menghapus <strong>{{ $shift->nama_shift }}</strong>? Jika shift memiliki riwayat transaksi, statusnya akan otomatis dinonaktifkan.</p>
                                            </div>
                                            <div class="modal-footer border-0 bg-light py-2 px-3 d-flex justify-content-between">
                                                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('admin.master.shift.destroy', $shift->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-3 px-3 fw-bold">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-clock fs-1 d-block mb-2 opacity-50"></i>
                                        Belum ada konfigurasi master shift.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 2: MONITORING KASIR AKTIF                                     -->
        <!-- ================================================================= -->
        <div class="tab-pane fade" id="tab-monitoring-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-broadcast text-danger me-2"></i>Status Shift Kasir Aktif Saat Ini
                        </h5>
                        <div class="small text-muted">Monitoring langsung kasir yang sedang bertugas di setiap outlet cabang.</div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold font-sm">
                        <i class="bi bi-circle-fill text-success me-1 font-xs"></i>{{ $activeBranchShifts->count() }} Shift Terbuka
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-muted small fw-bold">OUTLET CABANG</th>
                                <th class="text-muted small fw-bold">SHIFT</th>
                                <th class="text-muted small fw-bold">PETUGAS KASIR</th>
                                <th class="text-muted small fw-bold">WAKTU BUKA</th>
                                <th class="text-muted small fw-bold">STATUS JADWAL (FLEKSIBEL)</th>
                                <th class="text-end text-muted small fw-bold">MODAL AWAL</th>
                                <th class="text-end pe-4 text-muted small fw-bold">TOTAL PENJUALAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeBranchShifts as $active)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $active->tokoCabang?->nama_cabang ?? 'Outlet Umum' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold">
                                            {{ $active->nama_shift ?? $active->shift?->nama_shift ?? 'Shift Kasir' }}
                                        </span>
                                        <div class="small text-muted mt-0.5 font-monospace">
                                            Jadwal: {{ $active->jam_mulai_jadwal ?? $active->shift?->jam_mulai ?? '-' }} – {{ $active->jam_selesai_jadwal ?? $active->shift?->jam_selesai ?? '-' }} WIB
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $active->user?->name ?? 'Kasir' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold font-monospace">{{ $active->waktu_buka ? $active->waktu_buka->format('H:i') : '-' }} WIB</div>
                                        <div class="small text-muted font-sm">{{ $active->waktu_buka ? $active->waktu_buka->isoFormat('D MMM Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        @if($active->keterangan_waktu)
                                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small">
                                                <i class="bi bi-info-circle me-1 text-primary"></i>{{ $active->keterangan_waktu }}
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                                                <i class="bi bi-check2 me-1"></i>Sesuai Operasional
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark">
                                        Rp {{ number_format($active->modal_awal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end pe-4 font-monospace fw-bold text-emerald">
                                        Rp {{ number_format($active->total_penjualan, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-door-closed fs-1 d-block mb-2 opacity-50"></i>
                                        Tidak ada shift kasir yang sedang aktif saat ini di seluruh cabang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 3: RIWAYAT PERGANTIAN SHIFT                                   -->
        <!-- ================================================================= -->
        <div class="tab-pane fade" id="tab-riwayat-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="fw-bold mb-0 text-heading">
                        <i class="bi bi-clock-history text-emerald me-2"></i>Riwayat Sesi &amp; Pergantian Shift Kasir
                    </h5>
                    <div class="small text-muted">Catatan perubahan waktu operasional, estafet saldo modal awal, dan kepatuhan tutup kasir.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-muted small fw-bold">WAKTU SESI</th>
                                <th class="text-muted small fw-bold">CABANG &amp; PETUGAS</th>
                                <th class="text-muted small fw-bold">SHIFT</th>
                                <th class="text-muted small fw-bold">SUMBER MODAL AWAL</th>
                                <th class="text-end text-muted small fw-bold">MODAL AWAL</th>
                                <th class="text-end text-muted small fw-bold">TOTAL PENJUALAN</th>
                                <th class="text-end text-muted small fw-bold">MODAL AKHIR</th>
                                <th class="text-center pe-4 text-muted small fw-bold">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shiftHandovers as $shiftItem)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold font-monospace text-dark">
                                            {{ $shiftItem->waktu_buka ? $shiftItem->waktu_buka->format('H:i') : '-' }} – 
                                            {{ $shiftItem->waktu_tutup ? $shiftItem->waktu_tutup->format('H:i') : 'Aktif' }} WIB
                                        </div>
                                        <div class="small text-muted font-sm">
                                            {{ $shiftItem->waktu_buka ? $shiftItem->waktu_buka->isoFormat('D MMM Y') : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $shiftItem->tokoCabang?->nama_cabang ?? 'Outlet' }}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-person me-1"></i>{{ $shiftItem->user?->name ?? 'Kasir' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-semibold">
                                            {{ $shiftItem->nama_shift ?? $shiftItem->shift?->nama_shift ?? 'Shift Kasir' }}
                                        </span>
                                        @if($shiftItem->keterangan_waktu)
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                <i class="bi bi-info-circle me-1 text-primary"></i>{{ $shiftItem->keterangan_waktu }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shiftItem->pergantian_dari_kasir_shift_id && $shiftItem->previousShift)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">
                                                <i class="bi bi-arrow-repeat me-1"></i>Dari Saldo Akhir {{ $shiftItem->previousShift->nama_shift ?? 'Shift Sebelumnya' }}
                                            </span>
                                            <div class="small text-muted mt-0.5" style="font-size: 0.72rem;">
                                                Ditutup oleh: {{ $shiftItem->previousShift->user?->name ?? 'Kasir' }}
                                            </div>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small">
                                                <i class="bi bi-cash me-1"></i>Modal Kasir Manual
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace fw-semibold text-dark">
                                        Rp {{ number_format($shiftItem->modal_awal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-emerald">
                                        Rp {{ number_format($shiftItem->total_penjualan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold {{ $shiftItem->modal_akhir !== null ? 'text-primary' : 'text-muted' }}">
                                        {{ $shiftItem->modal_akhir !== null ? 'Rp ' . number_format($shiftItem->modal_akhir, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($shiftItem->status === 'buka')
                                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small">
                                                <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true" style="width: 8px; height: 8px;"></span>
                                                Sedang Buka
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                                                <i class="bi bi-check-circle-fill me-1"></i>Tutup Selesai
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-50"></i>
                                        Belum ada riwayat sesi shift kasir.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Shift Baru -->
<div class="modal fade" id="modalTambahShift" tabindex="-1" aria-labelledby="modalTambahShiftLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-emerald text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold" id="modalTambahShiftLabel">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Master Shift Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.master.shift.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Shift <span class="text-danger">*</span></label>
                        <input type="text" name="nama_shift" class="form-control rounded-3" placeholder="Contoh: Shift 3, Shift Pagi, dll" value="{{ old('nama_shift') }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="text" name="jam_mulai" class="form-control rounded-3 font-monospace" placeholder="11:00" value="{{ old('jam_mulai') }}" required>
                            <div class="form-text small">Format: HH:mm (WIB)</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="text" name="jam_selesai" class="form-control rounded-3 font-monospace" placeholder="16:00" value="{{ old('jam_selesai') }}" required>
                            <div class="form-text small">Format: HH:mm (WIB)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Aturan Modal Awal <span class="text-danger">*</span></label>
                        <select name="aturan_modal_awal" class="form-select rounded-3" required>
                            <option value="manual" {{ old('aturan_modal_awal') === 'manual' ? 'selected' : '' }}>
                                Input Manual oleh Kasir (Contoh: Shift 1)
                            </option>
                            <option value="otomatis_sebelumnya" {{ old('aturan_modal_awal') === 'otomatis_sebelumnya' ? 'selected' : '' }}>
                                Otomatis Saldo Akhir Tutup Kasir Shift Sebelumnya (Contoh: Shift 2)
                            </option>
                        </select>
                        <div class="form-text small">Pilih apakah modal awal diinput manual atau otomatis mengambil saldo akhir dari shift sebelumnya.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">Urutan Shift</label>
                            <input type="number" name="urutan" class="form-control rounded-3" value="{{ old('urutan', $totalShift + 1) }}" min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted">Keterangan / Deskripsi Operasional</label>
                        <textarea name="keterangan" class="form-control rounded-3" rows="2" placeholder="Catatan jam operasional dan ketentuan kasir...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold">Simpan Shift Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
