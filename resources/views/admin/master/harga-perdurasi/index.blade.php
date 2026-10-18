@extends('template.index')

@section('title', 'Master Data Durasi | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Master Data</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Durasi</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Master Data Durasi</h3>
            <p class="text-muted small mb-0">Kelola nama paket durasi, waktu durasi, dan kalkulasi harga sewa room studio.</p>
        </div>
        <div>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Tambah Durasi</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.master.harga-perdurasi.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama durasi atau lama waktu..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Cari</button>
                    @if(request()->filled('search'))
                        <a href="{{ route('admin.master.harga-perdurasi.index') }}" class="btn btn-outline-secondary" title="Reset">
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
                        <th class="text-muted small fw-bold">NAMA</th>
                        <th class="text-muted small fw-bold">DURASI</th>
                        <th class="text-muted small fw-bold">HARGA PAKET</th>
                        <th class="text-muted small fw-bold">HARGA PER MENIT</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $items->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-heading fs-6">{{ $item->nama }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-clock me-1"></i> {{ $item->durasi }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-emerald fs-6">{{ $item->kalkulasi_harga_format }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary fs-6">{{ $item->harga_per_menit_format }}</span>
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
                                    <form action="{{ route('admin.master.harga-perdurasi.update', $item->id) }}" method="POST" class="harga-durasi-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-heading">Edit Data Durasi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" class="form-control rounded-3" value="{{ $item->nama }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Durasi (Menit) <span class="text-danger">*</span></label>
                                                <input type="number" name="durasi_menit" class="form-control rounded-3 js-durasi-menit" value="{{ $item->durasi_menit }}" min="1" step="1" placeholder="Contoh: 60" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold">Harga Paket (Rp) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="kalkulasi_harga" class="form-control rounded-3 js-harga-paket" value="{{ $item->kalkulasi_harga }}" min="0" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold">Harga Per Menit</label>
                                                <input type="text" class="form-control rounded-3 js-harga-per-menit" value="{{ $item->harga_per_menit_format }}" readonly>
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
                                        <h6 class="fw-bold text-dark">Hapus Data Durasi?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus <strong>{{ $item->nama }}</strong>?</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.master.harga-perdurasi.destroy', $item->id) }}" method="POST" class="flex-fill">
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
                                <div class="text-muted mb-2"><i class="bi bi-clock-history fs-1"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada data durasi</h6>
                                <p class="text-muted small">Tambahkan paket durasi sewa room dan kalkulasi harganya.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    Tambah Durasi Sekarang
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
            <form action="{{ route('admin.master.harga-perdurasi.store') }}" method="POST" class="harga-durasi-form">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-heading">Tambah Data Durasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control rounded-3" placeholder="Contoh: Paket 1 Jam Reguler" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Durasi (Menit) <span class="text-danger">*</span></label>
                        <input type="number" name="durasi_menit" class="form-control rounded-3 js-durasi-menit" min="1" step="1" placeholder="Contoh: 60" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Harga Paket (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="kalkulasi_harga" class="form-control rounded-3 js-harga-paket" min="0" placeholder="Contoh: 50000" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Harga Per Menit</label>
                        <input type="text" class="form-control rounded-3 js-harga-per-menit" placeholder="Otomatis dihitung" readonly>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4">Simpan Durasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.harga-durasi-form').forEach((form) => {
        const durasi = form.querySelector('.js-durasi-menit');
        const hargaPaket = form.querySelector('.js-harga-paket');
        const hargaPerMenit = form.querySelector('.js-harga-per-menit');
        const formatRupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        const hitungHargaPerMenit = () => {
            const menit = Number(durasi.value);
            const harga = Number(hargaPaket.value);
            hargaPerMenit.value = menit > 0 && harga >= 0 && hargaPaket.value !== ''
                ? formatRupiah.format(harga / menit)
                : '';
        };

        durasi.addEventListener('input', hitungHargaPerMenit);
        hargaPaket.addEventListener('input', hitungHargaPerMenit);
        hitungHargaPerMenit();
    });
</script>
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
