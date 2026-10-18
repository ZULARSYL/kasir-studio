@extends('template.index')

@section('title', 'Master Promo & Diskon | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Master Data</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Promo</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Master Promo & Diskon</h3>
            <p class="text-muted small mb-0">Kelola kode voucher promo, nama promo, nilai diskon, periode berlaku, dan status promo.</p>
        </div>
        <div>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Tambah Promo Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.master.promo.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari kode voucher, nama promo, nilai, periode..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-emerald flex-fill">Filter</button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.master.promo.index') }}" class="btn btn-outline-secondary" title="Reset">
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
                        <th class="text-muted small fw-bold">KODE VOUCHER</th>
                        <th class="text-muted small fw-bold">NAMA PROMO</th>
                        <th class="text-muted small fw-bold">NILAI</th>
                        <th class="text-muted small fw-bold">PERIODE BERLAKU</th>
                        <th class="text-muted small fw-bold">STATUS</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $items->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-emerald-subtle text-emerald border border-emerald-subtle px-3 py-1.5 font-monospace fw-bold fs-7">
                                    {{ $item->kode_voucher }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-heading fs-6">{{ $item->nama_promo }}</div>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-semibold fs-7">
                                    {{ $item->nilai }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar-event me-1"></i> {{ $item->periode_berlaku }}
                                </span>
                            </td>
                            <td>{!! $item->status_badge !!}</td>
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
                                    @php
                                        $periodeDates = explode(' - ', $item->periode_berlaku, 2);
                                        $jenisDiskon = str_ends_with((string) $item->nilai, '%') ? 'persentase' : 'potongan';
                                        $jumlahDiskon = $jenisDiskon === 'persentase'
                                            ? preg_replace('/[^0-9.]/', '', (string) $item->nilai)
                                            : preg_replace('/\D/', '', (string) $item->nilai);
                                    @endphp
                                    <form action="{{ route('admin.master.promo.update', $item->id) }}" method="POST" class="promo-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-heading">Edit Promo Voucher</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Kode Voucher <span class="text-danger">*</span></label>
                                                <input type="text" name="kode_voucher" class="form-control rounded-3 font-monospace text-uppercase" value="{{ $item->kode_voucher }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Promo <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_promo" class="form-control rounded-3" value="{{ $item->nama_promo }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Jenis Diskon <span class="text-danger">*</span></label>
                                                <select name="jenis_diskon" class="form-select rounded-3 js-jenis-diskon" required>
                                                    <option value="">Pilih potongan harga atau persentase</option>
                                                    <option value="potongan" {{ $jenisDiskon === 'potongan' ? 'selected' : '' }}>Potongan Harga (Rp)</option>
                                                    <option value="persentase" {{ $jenisDiskon === 'persentase' ? 'selected' : '' }}>Persentase (%)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nilai Diskon <span class="text-danger">*</span></label>
                                                <input type="number" name="jumlah_diskon" class="form-control rounded-3 js-jumlah-diskon" value="{{ $jumlahDiskon }}" min="0.01" step="any" placeholder="Pilih jenis diskon terlebih dahulu" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                                <input type="date" name="periode_mulai" class="form-control rounded-3" value="{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodeDates[0] ?? '') ? $periodeDates[0] : '' }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                                                <input type="date" name="periode_selesai" class="form-control rounded-3" value="{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodeDates[1] ?? '') ? $periodeDates[1] : '' }}" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select rounded-3" required>
                                                    <option value="aktif" {{ $item->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                                    <option value="nonaktif" {{ $item->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
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
                                        <h6 class="fw-bold text-dark">Hapus Promo?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus kode voucher <strong>{{ $item->kode_voucher }}</strong>?</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.master.promo.destroy', $item->id) }}" method="POST" class="flex-fill">
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
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-percent fs-1"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada promo voucher</h6>
                                <p class="text-muted small">Buat promo diskon untuk studio dan cafe.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    Tambah Promo Sekarang
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
            <form action="{{ route('admin.master.promo.store') }}" method="POST" class="promo-form">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-heading">Tambah Promo Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any() && !old('_method'))
                        <div class="alert alert-danger rounded-3">
                            <div class="fw-semibold mb-1">Promo belum dapat disimpan:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Voucher <span class="text-danger">*</span></label>
                        <input type="text" name="kode_voucher" class="form-control rounded-3 font-monospace text-uppercase" value="{{ old('kode_voucher') }}" placeholder="CONTOH: PROMOHEMAT" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Promo <span class="text-danger">*</span></label>
                        <input type="text" name="nama_promo" class="form-control rounded-3" value="{{ old('nama_promo') }}" placeholder="Contoh: Diskon Nonton Pelajar" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jenis Diskon <span class="text-danger">*</span></label>
                        <select name="jenis_diskon" class="form-select rounded-3 js-jenis-diskon" required>
                            <option value="" {{ old('jenis_diskon') ? '' : 'selected' }}>Pilih potongan harga atau persentase</option>
                            <option value="potongan" {{ old('jenis_diskon') === 'potongan' ? 'selected' : '' }}>Potongan Harga (Rp)</option>
                            <option value="persentase" {{ old('jenis_diskon') === 'persentase' ? 'selected' : '' }}>Persentase (%)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nilai Diskon <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_diskon" class="form-control rounded-3 js-jumlah-diskon" value="{{ old('jumlah_diskon') }}" min="0.01" step="any" placeholder="Pilih jenis diskon terlebih dahulu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="periode_mulai" class="form-control rounded-3" value="{{ old('periode_mulai') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="periode_selesai" class="form-control rounded-3" value="{{ old('periode_selesai') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.promo-form').forEach((form) => {
        const jenisDiskon = form.querySelector('.js-jenis-diskon');
        const jumlahDiskon = form.querySelector('.js-jumlah-diskon');

        const updateJenisDiskon = () => {
            const persentase = jenisDiskon.value === 'persentase';
            jumlahDiskon.min = jenisDiskon.value === 'potongan' ? '1' : '0.01';
            jumlahDiskon.max = persentase ? '100' : '';
            jumlahDiskon.step = persentase ? '0.01' : '1';
            jumlahDiskon.placeholder = !jenisDiskon.value
                ? 'Pilih jenis diskon terlebih dahulu'
                : (persentase ? 'Contoh: 20' : 'Contoh: 15000');
        };

        jenisDiskon.addEventListener('change', updateJenisDiskon);
        updateJenisDiskon();
    });

    @if($errors->any() && !old('_method'))
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambah')).show();
    @endif
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
