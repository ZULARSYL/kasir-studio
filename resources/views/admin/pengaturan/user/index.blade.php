@extends('template.index')

@section('title', 'Manajemen User | Pengaturan Pressent Studio & Cafe')

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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div>
                    <div class="fw-semibold">Gagal menyimpan data akun user:</div>
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
                    <li class="breadcrumb-item text-muted">Pengaturan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">User</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Submenu Pengaturan User</h3>
            <p class="text-muted small mb-0">Kelola akun login sistem, penempatan outlet cabang, relasi staf pegawai, dan jabatan.</p>
        </div>
        <div>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="bi bi-person-plus-fill fs-5"></i>
                <span class="fw-semibold">Tambah User Baru</span>
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
                        <span class="text-muted small d-block">Total Akun Pengguna</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ $totalUser }} Akun</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Akun Administrator</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ $totalAdmin }} Akun</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper rounded-4 bg-success-subtle text-success d-flex align-items-center justify-content-center">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Akun Kasir</span>
                        <h4 class="fw-bold text-dark mb-0 font-monospace">{{ $totalKasir }} Akun</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.pengaturan.user.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Text -->
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari username atau nama pegawai..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <!-- Filter Outlet Cabang -->
                <div class="col-12 col-md-3">
                    <select name="toko_cabang_id" class="form-select">
                        <option value="">-- Semua Outlet --</option>
                        @foreach($cabangs as $cb)
                            <option value="{{ $cb->id }}" {{ request('toko_cabang_id') == $cb->id ? 'selected' : '' }}>
                                {{ $cb->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Filter Jabatan / Role -->
                <div class="col-12 col-md-2">
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
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Cari</button>
                    @if(request()->filled('search') || request()->filled('toko_cabang_id') || request()->filled('jabatan_id'))
                        <a href="{{ route('admin.pengaturan.user.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
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
                        <th class="text-muted small fw-bold">USERNAME</th>
                        <th class="text-muted small fw-bold">JABATAN</th>
                        <th class="text-muted small fw-bold">OUTLET</th>
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
                                        <div class="fw-bold text-heading d-flex align-items-center gap-2">
                                            <span>{{ $item->name }}</span>
                                            @if($item->id === auth()->id())
                                                <span class="badge bg-primary text-white small" style="font-size: 0.65rem;">Akun Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <div class="fw-bold text-heading d-flex align-items-center gap-1.5">
                                            <span class="text-muted small font-monospace">@<span class="text-dark">{{ $item->username }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </td>    
                            
                            <td>
                                <div>
                                    @if($item->jabatan)
                                        <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-3 py-1 fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i>{{ $item->jabatan->nama_jabatan }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-muted rounded-pill px-2.5 py-1">
                                            {{ ucfirst($item->role) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($item->tokoCabang)
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-shop text-emerald fs-5"></i>
                                        <div>
                                            <strong class="text-dark small d-block">{{ $item->tokoCabang->nama_cabang }}</strong>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border px-2.5 py-1 small">
                                        <i class="bi bi-globe me-1"></i>Semua Cabang / Kantor Pusat
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small font-monospace">{{ $item->created_at ? $item->created_at->isoFormat('D MMM Y, HH:mm') : '-' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $item->id }}" title="Edit User">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @if($item->id !== auth()->id())
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusUser{{ $item->id }}" title="Hapus User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary opacity-50" disabled title="Akun Anda aktif">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit User -->
                        <div class="modal fade" id="modalEditUser{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.pengaturan.user.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom py-3">
                                            <h5 class="modal-title fw-bold text-heading">
                                                <i class="bi bi-pencil-square text-emerald me-2"></i>Edit Akun User
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <!-- Pilih Pegawai (Kepegawaian -> Pegawai) -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Nama Pegawai<span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                                    <select name="pegawai_id" class="form-select border-start-0" required>
                                                        <option value="" disabled>-- Pilih Pegawai --</option>
                                                        @foreach($pegawais as $pg)
                                                            <option value="{{ $pg->id }}" {{ $item->pegawai_id == $pg->id ? 'selected' : '' }}>
                                                                {{ $pg->nama_pegawai }} ({{ $pg->jabatan->nama_jabatan ?? 'Staf' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Penempatan Outlet (Master Toko Cabang) -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Outlet</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop"></i></span>
                                                    <select name="toko_cabang_id" class="form-select border-start-0">
                                                        <option value="">-- Semua Cabang / Kantor Pusat --</option>
                                                        @foreach($cabangs as $cb)
                                                            <option value="{{ $cb->id }}" {{ $item->toko_cabang_id == $cb->id ? 'selected' : '' }}>
                                                                {{ $cb->nama_cabang }} - {{ $cb->alamat }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Jabatan & Role (Kepegawaian -> Jabatan) -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Jabatan <span class="text-danger">*</span></label>
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
                                            </div>

                                            <!-- Username -->
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-muted">Username Login <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0 font-monospace">@</span>
                                                    <input type="text" name="username" class="form-control border-start-0 font-monospace" value="{{ $item->username }}" required>
                                                </div>
                                            </div>

                                            <!-- Password (Opsional saat Edit) -->
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted">Password Baru (Opsional)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                                    <input type="password" name="password" class="form-control border-start-0 font-monospace" placeholder="Kosongkan jika tidak ingin mengubah password" minlength="6">
                                                </div>
                                                <div class="form-text small">Hanya isi jika ingin mengganti kata sandi akun user ini.</div>
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

                        <!-- Modal Hapus User -->
                        @if($item->id !== auth()->id())
                            <div class="modal fade" id="modalHapusUser{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-body text-center p-4">
                                            <div class="stat-icon-circle bg-danger-subtle text-danger mx-auto mb-3">
                                                <i class="bi bi-exclamation-triangle fs-2"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark">Hapus Akun User?</h6>
                                            <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus akun <strong>@<span class="text-dark">{{ $item->username }}</span></strong> ({{ $item->name }})?</p>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('admin.pengaturan.user.destroy', $item->id) }}" method="POST" class="flex-fill">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger w-100 rounded-3 fw-semibold">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-people fs-1 opacity-50"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada akun user terdaftar</h6>
                                <p class="text-muted small">Tambahkan user baru dengan menghubungkan data pegawai, outlet cabang, dan jabatan.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                                    Tambah User Sekarang
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

<!-- Modal Tambah User Baru -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.pengaturan.user.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-heading">
                        <i class="bi bi-person-plus text-emerald me-2"></i>Tambah Akun User Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Pilih Pegawai (Kepegawaian -> Pegawai) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Pegawai<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                            <select name="pegawai_id" id="tambahPegawaiSelect" class="form-select border-start-0" required>
                                <option value="" disabled selected>-- Pilih Data Pegawai --</option>
                                @foreach($pegawais as $pg)
                                    <option value="{{ $pg->id }}" data-jabatan-id="{{ $pg->jabatan_id }}" {{ old('pegawai_id') == $pg->id ? 'selected' : '' }}>
                                        {{ $pg->nama_pegawai }} ({{ $pg->jabatan->nama_jabatan ?? 'Staf' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Penempatan Outlet (Master Toko Cabang) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted"> Outlet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop"></i></span>
                            <select name="toko_cabang_id" class="form-select border-start-0">
                                <option value="">-- Semua Cabang / Kantor Pusat --</option>
                                @foreach($cabangs as $cb)
                                    <option value="{{ $cb->id }}" {{ old('toko_cabang_id') == $cb->id ? 'selected' : '' }}>
                                        {{ $cb->nama_cabang }} - {{ $cb->alamat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Jabatan Pegawai (Kepegawaian -> Jabatan sebagai Role) -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Jabatan<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge"></i></span>
                            <select name="jabatan_id" id="tambahJabatanSelect" class="form-select border-start-0" required>
                                <option value="" disabled selected>-- Pilih Posisi Jabatan --</option>
                                @foreach($jabatans as $jb)
                                    <option value="{{ $jb->id }}" {{ old('jabatan_id') == $jb->id ? 'selected' : '' }}>
                                        {{ $jb->nama_jabatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Username Login <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 font-monospace">@</span>
                            <input type="text" name="username" class="form-control border-start-0 font-monospace" placeholder="contoh: ahmad_kasir" value="{{ old('username') }}" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Kata Sandi / Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 font-monospace" placeholder="Minimal 6 karakter" minlength="6" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold">Simpan Akun User</button>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sinkronisasi otomatis Jabatan saat memilih Pegawai di Modal Tambah User
    const pegawaiSelect = document.getElementById('tambahPegawaiSelect');
    const jabatanSelect = document.getElementById('tambahJabatanSelect');

    if (pegawaiSelect && jabatanSelect) {
        pegawaiSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const jabatanId = selectedOption.getAttribute('data-jabatan-id');
            if (jabatanId) {
                jabatanSelect.value = jabatanId;
            }
        });
    }
});
</script>
@endpush
