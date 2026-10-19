@extends('template.index')

@section('title', 'Faktur Barang (Pembelian / Grosir) | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Cafe & Pengadaan</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Faktur Barang</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Faktur Barang (Pembelian / Grosir)</h3>
            <p class="text-muted small mb-0">Input dan kelola barang masuk dari grosir, penetapan harga awal beli dan harga penjualan kasir.</p>
        </div>
        <div>
            <a href="{{ route('admin.cafe.faktur.create') }}" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Input Faktur Baru</span>
            </a>
        </div>
    </div>

    <!-- Error / Validation Alert Banner -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-x fs-4 text-danger"></i>
                <div>
                    <h6 class="fw-bold mb-1">Permintaan Ditolak!</h6>
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
            <form action="{{ route('admin.cafe.faktur.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Nomor faktur, pemasok..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="toko_cabang_id" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Semua Outlet Cabang</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ request('toko_cabang_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_mulai" class="form-control text-muted" title="Tanggal Mulai" value="{{ request('tanggal_mulai') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_selesai" class="form-control text-muted" title="Tanggal Selesai" value="{{ request('tanggal_selesai') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Filter</button>
                    @if(request()->anyFilled(['search', 'toko_cabang_id', 'tanggal_mulai', 'tanggal_selesai']))
                        <a href="{{ route('admin.cafe.faktur.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
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
                        <th class="ps-4 text-muted small fw-bold" style="width: 60px;">NO</th>
                        <th class="text-muted small fw-bold">NOMOR FAKTUR</th>
                        <th class="text-muted small fw-bold">OUTLET CABANG</th>
                        <th class="text-muted small fw-bold">TANGGAL</th>
                        <th class="text-muted small fw-bold">PEMASOK / GROSIR</th>
                        <th class="text-muted small fw-bold text-center">TOTAL ITEM</th>
                        <th class="text-muted small fw-bold">TOTAL NOMINAL</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fakturs as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $fakturs->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-heading font-monospace">
                                    <i class="bi bi-receipt me-1 text-emerald"></i>{{ $item->nomor_faktur }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                    <i class="bi bi-buildings me-1 text-primary"></i>{{ $item->tokoCabang?->nama_cabang ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $item->tanggal_faktur ? $item->tanggal_faktur->format('d M Y') : '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $item->pemasok ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-emerald-subtle text-emerald border border-emerald px-2.5 py-1">
                                    {{ $item->details->count() }} Macam
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-emerald font-monospace fs-6">
                                    {{ $item->total_nominal_format }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.cafe.faktur.show', $item->id) }}" class="btn btn-outline-primary" title="Lihat Rincian Faktur">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusFaktur{{ $item->id }}" title="Hapus Faktur">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Hapus Faktur -->
                        <div class="modal fade" id="modalHapusFaktur{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-body text-center p-4">
                                        <div class="p-3 bg-danger-subtle text-danger rounded-circle d-inline-flex mb-3">
                                            <i class="bi bi-exclamation-triangle fs-3"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Hapus Faktur Barang?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus faktur <strong>{{ $item->nomor_faktur }}</strong>? Rincian item barang di dalamnya akan ikut terhapus.</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.cafe.faktur.destroy', $item->id) }}" method="POST" class="flex-fill">
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
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-receipt-cutoff fs-1"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada faktur pembelian barang</h6>
                                <p class="text-muted small">Input faktur pembelian barang grosir untuk mengisi stok dan harga penjualan kasir.</p>
                                <a href="{{ route('admin.cafe.faktur.create') }}" class="btn btn-sm btn-emerald rounded-3">
                                    Input Faktur Baru Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fakturs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $fakturs->links() }}
            </div>
        @endif
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
    .border-emerald {
        border-color: #059669 !important;
    }
    .bg-emerald-subtle {
        background-color: #d1fae5 !important;
    }
</style>
@endsection
