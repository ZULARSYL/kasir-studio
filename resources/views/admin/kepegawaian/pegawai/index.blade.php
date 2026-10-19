@extends('template.index')

@section('title', 'Data Pegawai | Kepegawaian Pressent Studio & Cafe')

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
                    <div class="fw-semibold">Gagal menyimpan data pegawai:</div>
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
                    <li class="breadcrumb-item text-muted">Kepegawaian</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Pegawai</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Pegawai</h3>
        </div>
        <div>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahPegawai">
                <i class="bi bi-person-plus-fill fs-5"></i>
                <span class="fw-semibold">Tambah Pegawai Baru</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-emerald-subtle text-emerald d-flex align-items-center justify-content-center">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Total Pegawai Terdaftar</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ $totalPegawai }} Orang</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Total Posisi Jabatan</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ $totalJabatan }} Posisi</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.kepegawaian.pegawai') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Text -->
                <div class="col-12 col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama pegawai, nomor HP, atau alamat..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <!-- Filter Jabatan -->
                <div class="col-12 col-md-4 col-lg-3">
                    <select name="jabatan_id" class="form-select">
                        <option value="">-- Semua Jabatan --</option>
                        @foreach($jabatans as $jb)
                            <option value="{{ $jb->id }}" {{ request('jabatan_id') == $jb->id ? 'selected' : '' }}>
                                {{ $jb->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Submit & Reset -->
                <div class="col-12 col-md-2 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Cari</button>
                    @if(request()->filled('search') || request()->filled('jabatan_id'))
                        <a href="{{ route('admin.kepegawaian.pegawai') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-muted small fw-bold" style="width: 60px;">NO</th>
                        <th class="text-muted small fw-bold">NAMA PEGAWAI</th>
                        <th class="text-muted small fw-bold">JABATAN</th>
                        <th class="text-muted small fw-bold">NOMOR HP</th>
                        <th class="text-muted small fw-bold">ALAMAT</th>
                        <th class="text-muted small fw-bold">TANGGAL DIBUAT</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 130px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $items->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <div class="fw-bold text-heading">{{ $item->nama_pegawai }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item->jabatan)
                                    <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-3 py-1 fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i>{{ $item->jabatan->nama_jabatan }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-muted rounded-pill px-2.5 py-1">
                                        Tanpa Jabatan
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-muted small d-flex align-items-center gap-1.5 text-truncate" style="max-width: 250px;" title="{{ $item->nomor_hp }}">
                                    <span class="text-truncate">{{ $item->nomor_hp }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small d-flex align-items-center gap-1.5 text-truncate" style="max-width: 250px;" title="{{ $item->alamat }}">
                                    <span class="text-truncate">{{ $item->alamat }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small font-monospace">{{ $item->created_at ? $item->created_at->isoFormat('D MMM Y') : '-' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPegawai{{ $item->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusPegawai{{ $item->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Pegawai -->
                        <div class="modal fade" id="modalEditPegawai{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.kepegawaian.pegawai.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom py-3">
                                            <h5 class="modal-title fw-bold text-heading">
                                                <i class="bi bi-pencil-square text-emerald me-2"></i>Edit Data Pegawai
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <!-- Nama Pegawai -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Nama Lengkap Pegawai <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                                    <input type="text" name="nama_pegawai" class="form-control border-start-0" value="{{ $item->nama_pegawai }}" required>
                                                </div>
                                            </div>

                                            <!-- Jabatan (Diambil dari Master Jabatan) -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Jabatan Pegawai <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge"></i></span>
                                                    <select name="jabatan_id" class="form-select border-start-0" required>
                                                        <option value="" disabled>-- Pilih Jabatan --</option>
                                                        @foreach($jabatans as $jb)
                                                            <option value="{{ $jb->id }}" {{ $item->jabatan_id == $jb->id ? 'selected' : '' }}>
                                                                {{ $jb->nama_jabatan }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-text small">Diambil secara dinamis dari submenu Jabatan.</div>
                                            </div>

                                            <!-- Nomor HP -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                                                    <input type="tel" name="nomor_hp" class="form-control border-start-0 font-monospace" value="{{ $item->nomor_hp }}" required>
                                                </div>
                                            </div>

                                            <!-- Alamat -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Alamat Domisili <span class="text-danger">*</span></label>
                                                <textarea name="alamat" class="form-control rounded-3" rows="3" required>{{ $item->alamat }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Hapus Pegawai -->
                        <div class="modal fade" id="modalHapusPegawai{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-body text-center p-4">
                                        <div class="stat-icon-circle bg-danger-subtle text-danger mx-auto mb-3">
                                            <i class="bi bi-exclamation-triangle fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Hapus Pegawai?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus data pegawai <strong>{{ $item->nama_pegawai }}</strong>?</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.kepegawaian.pegawai.destroy', $item->id) }}" method="POST" class="flex-fill">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100 rounded-3 fw-semibold">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-people fs-1 opacity-50"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada data pegawai</h6>
                                <p class="text-muted small">Tambahkan data pegawai baru dengan memilih jabatan yang tersedia.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahPegawai">
                                    Tambah Pegawai Sekarang
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Pegawai -->
<div class="modal fade" id="modalTambahPegawai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.kepegawaian.pegawai.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-heading">
                        <i class="bi bi-person-plus text-emerald me-2"></i>Tambah Pegawai Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nama Pegawai -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap Pegawai <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="nama_pegawai" class="form-control border-start-0" placeholder="Contoh: Ahmad Fauzan" value="{{ old('nama_pegawai') }}" required>
                        </div>
                    </div>

                    <!-- Jabatan (Diambil dari Master Jabatan) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Jabatan Pegawai <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge"></i></span>
                            <select name="jabatan_id" class="form-select border-start-0" required>
                                <option value="" disabled selected>-- Pilih Jabatan Pegawai --</option>
                                @foreach($jabatans as $jb)
                                    <option value="{{ $jb->id }}" {{ old('jabatan_id') == $jb->id ? 'selected' : '' }}>
                                        {{ $jb->nama_jabatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text small">Diambil dari submenu Jabatan pada modul Kepegawaian.</div>
                    </div>

                    <!-- Nomor HP -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                            <input type="tel" name="nomor_hp" class="form-control border-start-0 font-monospace" placeholder="08xxxxxxxxxx" value="{{ old('nomor_hp') }}" required>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="alamat" class="form-control rounded-3" rows="3" placeholder="Jl. Sudirman No. 12, Pekanbaru..." required>{{ old('alamat') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold">Simpan Pegawai</button>
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
        background-color: #ecfdf5 !important;
    }
    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
    }
    .stat-icon-circle {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
