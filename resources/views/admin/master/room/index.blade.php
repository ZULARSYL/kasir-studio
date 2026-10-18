@extends('template.index')

@section('title', 'Master Room Studio | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Master Data</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Room Studio</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Master Room Studio</h3>
            <p class="text-muted small mb-0">Kelola unit room studio bioskop, tipe room, kapasitas penonton, dan relasi toko cabang.</p>
        </div>
        <div>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Tambah Room Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.master.room.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama room atau tipe..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="toko_cabang_id" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Semua Cabang</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ request('toko_cabang_id') == $cabang->id ? 'selected' : '' }}>
                                {{ $cabang->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Filter</button>
                    @if(request()->anyFilled(['search', 'toko_cabang_id']))
                        <a href="{{ route('admin.master.room.index') }}" class="btn btn-outline-secondary" title="Reset">
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
                        <th class="text-muted small fw-bold">NAMA ROOM</th>
                        <th class="text-muted small fw-bold">KAPASITAS</th>
                        <th class="text-muted small fw-bold">CABANG (TOKO CABANG)</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $items->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-heading fs-6">
                                    <i class="bi bi-door-open me-2 text-emerald"></i> {{ $item->nama }}
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">
                                    <i class="bi bi-people me-1 text-muted"></i> {{ $item->kapasitas }} Orang
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill fs-7">
                                    <i class="bi bi-buildings me-1 text-emerald"></i> {{ $item->tokoCabang->nama_cabang ?? '-' }}
                                </span>
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

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.master.room.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-heading">Edit Room Studio</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Room <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" class="form-control rounded-3" value="{{ $item->nama }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Toko Cabang <span class="text-danger">*</span></label>
                                                <select name="toko_cabang_id" class="form-select rounded-3" required>
                                                    <option value="">Pilih Cabang</option>
                                                    @foreach($cabangs as $cabang)
                                                        <option value="{{ $cabang->id }}" {{ $item->toko_cabang_id == $cabang->id ? 'selected' : '' }}>
                                                            {{ $cabang->nama_cabang }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text small">Mengambil dari id toko cabang dan menampilkan nama cabang.</div>
                                            </div>
                                            <div class="cmb-3">
                                                <label class="form-label small fw-semibold">Kapasitas (Orang) <span class="text-danger">*</span></label>
                                                <input type="number" name="kapasitas" class="form-control rounded-3" value="{{ $item->kapasitas }}" min="1" required>
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

                        <!-- Modal Hapus -->
                        <div class="modal fade" id="modalHapus{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-body text-center p-4">
                                        <div class="p-3 bg-danger-subtle text-danger rounded-circle d-inline-flex mb-3">
                                            <i class="bi bi-exclamation-triangle fs-3"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Hapus Room Studio?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus <strong>{{ $item->nama }}</strong>?</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.master.room.destroy', $item->id) }}" method="POST" class="flex-fill">
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
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-door-open fs-1"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada unit room studio</h6>
                                <p class="text-muted small">Tambahkan room studio dengan mengaitkan cabang toko.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    Tambah Room Sekarang
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.master.room.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-heading">Tambah Room Studio Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Room <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control rounded-3" placeholder="Contoh: Studio Deluxe 01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cabang (Toko Cabang) <span class="text-danger">*</span></label>
                        <select name="toko_cabang_id" class="form-select rounded-3" required>
                            <option value="">Pilih Cabang</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Mengambil dari id toko cabang dan menampilkan nama cabang.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kapasitas (Orang) <span class="text-danger">*</span></label>
                        <input type="number" name="kapasitas" class="form-control rounded-3" value="4" min="1" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4">Simpan Room</button>
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
