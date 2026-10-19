@extends('template.index')

@section('title', 'Master Data Barang | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Master Data</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Barang</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Master Data Barang</h3>
            <p class="text-muted small mb-0">Kelola master nama barang keperluan cafe, studio, dan operasional.</p>
        </div>
        <div>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Tambah Barang Baru</span>
            </button>
        </div>
    </div>

    <!-- Error / Validation Alert Banner -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-x fs-4 text-danger"></i>
                <div>
                    <h6 class="fw-bold mb-1">Permintaan Ditolak Sistem!</h6>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.master.barang.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama barang..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="menu_cafe_id" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Menu Cafe</option>
                        @foreach($menuCafes as $menuCafe)
                            <option value="{{ $menuCafe->id }}" {{ request('menu_cafe_id') == $menuCafe->id ? 'selected' : '' }}>
                                {{ $menuCafe->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Filter</button>
                    @if(request()->anyFilled(['search', 'menu_cafe_id']))
                        <a href="{{ route('admin.master.barang.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-muted small fw-bold" style="width: 70px;">NO</th>
                        <th class="text-muted small fw-bold">NAMA BARANG</th>
                        <th class="text-muted small fw-bold">KATEGORI MENU CAFE</th>
                        <th class="text-muted small fw-bold">TANGGAL DIBUAT</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $items->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-heading">
                                    <i class="bi bi-box2 me-2 text-emerald"></i>{{ $item->nama_barang }}
                                </div>
                            </td>
                            <td>
                                @if($item->menuCafe)
                                    <span class="badge bg-light text-dark border px-2.5 py-1">
                                        {{ $item->menuCafe->nama }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapus{{ $item->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Barang -->
                        <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.master.barang.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-heading">Edit Master Barang</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_barang" class="form-control rounded-3" value="{{ $item->nama_barang }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Menu Cafe</label>
                                                <select name="menu_cafe_id" class="form-select rounded-3">
                                                    <option value="">-- Pilih Kategori (Opsional) --</option>
                                                    @foreach($menuCafes as $menuCafe)
                                                        <option value="{{ $menuCafe->id }}" {{ $item->menu_cafe_id == $menuCafe->id ? 'selected' : '' }}>
                                                            {{ $menuCafe->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    Pilih kategori menu cafe untuk barang ini.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-emerald rounded-3 px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Hapus Barang -->
                        <div class="modal fade" id="modalHapus{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-body text-center p-4">
                                        <div class="p-3 bg-danger-subtle text-danger rounded-circle d-inline-flex mb-3">
                                            <i class="bi bi-exclamation-triangle fs-3"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Hapus Barang?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus data barang <strong>{{ $item->nama_barang }}</strong>?</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.master.barang.destroy', $item->id) }}" method="POST" class="flex-fill">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100 rounded-3">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-box2 fs-1"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada master barang</h6>
                                <p class="text-muted small">Tambahkan nama barang seperti Kopi Arabika, Teh Botol, Gula Pasir, atau Mie Instan.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                                    Tambah Barang Sekarang
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

<!-- Modal Tambah Barang -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.master.barang.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-heading">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" class="form-control rounded-3" placeholder="Contoh: Kopi Bubuk Robusta, Susu Kental Manis" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Menu Cafe</label>
                        <select name="menu_cafe_id" class="form-select rounded-3">
                            <option value="">-- Pilih Kategori (Opsional) --</option>
                            @foreach($menuCafes as $menuCafe)
                                <option value="{{ $menuCafe->id }}">{{ $menuCafe->nama }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Pilih kategori menu cafe untuk barang ini.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4">Simpan Barang</button>
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
    .text-emerald {
        color: #059669 !important;
    }
</style>
@endsection
