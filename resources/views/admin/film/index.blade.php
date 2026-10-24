@extends('template.index')

@section('title', 'Katalog & List Film | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Layanan Studio</li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">List Film</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Katalog & List Film Studio</h3>
            <p class="text-muted small mb-0">Kelola film bioskop studio, poster cover autokompres, kalkulasi harga durasi per menit, dan multi-genre.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahFilm">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span class="fw-semibold">Tambah Film Baru</span>
            </button>
        </div>
    </div>

    <!-- Error / Validation Alert Banner -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-shield-x fs-4 text-danger mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Permintaan Ditolak Sistem Keamanan!</h6>
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
            <form action="{{ route('admin.film.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari judul film, sutradara, sinopsis..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="genre_id" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Semua Genre</option>
                        @foreach($genres as $g)
                            <option value="{{ $g->id }}" {{ request('genre_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->nama_genre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="tidak_tersedia" {{ request('status') == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="usia_rating" class="form-select text-muted" onchange="this.form.submit()">
                        <option value="">Semua Usia</option>
                        <option value="SU" {{ request('usia_rating') == 'SU' ? 'selected' : '' }}>SU (Semua Umur)</option>
                        <option value="13+" {{ request('usia_rating') == '13+' ? 'selected' : '' }}>13+ (Remaja)</option>
                        <option value="17+" {{ request('usia_rating') == '17+' ? 'selected' : '' }}>17+ (Dewasa)</option>
                        <option value="21+" {{ request('usia_rating') == '21+' ? 'selected' : '' }}>21+ (Dewasa Khusus)</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-emerald w-100" title="Cari">
                        <i class="bi bi-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'genre_id', 'status', 'usia_rating']))
                        <a href="{{ route('admin.film.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
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
                        <th class="ps-4 text-muted small fw-bold" style="width: 50px;">NO</th>
                        <th class="text-muted small fw-bold" style="width: 90px;">COVER</th>
                        <th class="text-muted small fw-bold">JUDUL</th>
                        <th class="text-muted small fw-bold">GENRE</th>
                        <th class="text-muted small fw-bold">DURASI</th>
                        <th class="text-muted small fw-bold">TARIF / MENIT</th>
                        <th class="text-muted small fw-bold">TOTAL HARGA</th>
                        <th class="text-muted small fw-bold">STATUS</th>
                        <th class="text-muted small fw-bold" style="min-width: 170px;">STOK PER OUTLET</th>
                        <th class="text-end pe-4 text-muted small fw-bold" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($films as $index => $film)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">{{ $films->firstItem() + $index }}</td>
                            <td>
                                <div class="film-poster-container">
                                    <img src="{{ $film->poster_url }}" alt="{{ $film->judul }}" class="film-poster-img rounded-3 shadow-sm">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-heading fs-6">{{ $film->judul }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 200px;">
                                    @forelse($film->genres as $genre)
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-2 small">
                                            <i class="bi bi-tag-fill me-1 text-emerald"></i>{{ $genre->nama_genre }}
                                        </span>
                                    @empty
                                        <span class="text-muted small fst-italic">Tanpa Genre</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $film->durasi_menit }} Menit
                                </div>
                                <span class="text-muted small">
                                    ({{ floor($film->durasi_menit / 60) }}j {{ $film->durasi_menit % 60 }}m)
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-muted font-monospace">
                                    {{ $film->harga_per_menit_format }} / menit
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-emerald fs-6 font-monospace">
                                    {{ $film->total_harga_format }}
                                </div>
                                <span class="text-muted small" style="font-size: 0.72rem;">
                                    ({{ $film->durasi_menit }} × {{ number_format($film->harga_per_menit, 0, ',', '.') }})
                                </span>
                            </td>
                            <td>
                                {!! $film->status_badge !!}
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @forelse($cabangs as $cabang)
                                        @php
                                            $stokVal = $film->getLimitPenggunaanForCabang($cabang->id);
                                            $activeVal = $film->getActiveRoomsCount($cabang->id);
                                            $sisaVal = max(0, $stokVal - $activeVal);
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between gap-2 p-1 rounded bg-light border" style="font-size: 0.72rem;">
                                            <span class="text-truncate fw-semibold text-dark" style="max-width: 90px;" title="{{ $cabang->nama_cabang }}">
                                                <i class="bi bi-shop text-emerald me-0.5"></i>{{ $cabang->nama_cabang }}
                                            </span>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-white text-dark border font-monospace fw-bold">{{ $stokVal }} Room</span>
                                                @if($sisaVal > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="Sisa {{ $sisaVal }} room">Sisa {{ $sisaVal }}</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Slot penuh">Penuh</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-bold">
                                            Max {{ $film->limit_penggunaan ?? 1 }} Room
                                        </span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalDetailFilm{{ $film->id }}" title="Detail Film" aria-label="Lihat detail film {{ $film->judul }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditFilm{{ $film->id }}" title="Edit Film">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusFilm{{ $film->id }}" title="Hapus Film">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Film -->
                        <div class="modal fade" id="modalEditFilm{{ $film->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('admin.film.update', $film->id) }}" method="POST" enctype="multipart/form-data" class="film-modal-form">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <div>
                                                <h5 class="modal-title fw-bold text-heading">Edit Film: {{ $film->judul }}</h5>
                                                <span class="text-muted small">Perbarui data film, cover autokompres, durasi & multi-genre.</span>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-3">
                                            <div class="row g-3">
                                                <!-- Judul -->
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-semibold">Judul Film <span class="text-danger">*</span></label>
                                                    <input type="text" name="judul" class="form-control rounded-3" value="{{ $film->judul }}" required>
                                                </div>

                                                <!-- Rating Usia -->
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Rating Usia <span class="text-danger">*</span></label>
                                                    <select name="usia_rating" class="form-select rounded-3" required>
                                                        <option value="SU" {{ $film->usia_rating == 'SU' ? 'selected' : '' }}>SU (Semua Umur)</option>
                                                        <option value="13+" {{ $film->usia_rating == '13+' ? 'selected' : '' }}>13+ (Remaja)</option>
                                                        <option value="17+" {{ $film->usia_rating == '17+' ? 'selected' : '' }}>17+ (Dewasa)</option>
                                                        <option value="21+" {{ $film->usia_rating == '21+' ? 'selected' : '' }}>21+ (Dewasa Khusus)</option>
                                                    </select>
                                                </div>

                                                <!-- Sutradara & Tahun Rilis -->
                                                <div class="col-md-7">
                                                    <label class="form-label small fw-semibold">Sutradara</label>
                                                    <input type="text" name="sutradara" class="form-control rounded-3" value="{{ $film->sutradara }}" placeholder="Contoh: Christopher Nolan">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small fw-semibold">Tahun Rilis</label>
                                                    <input type="number" name="tahun_rilis" class="form-control rounded-3" value="{{ $film->tahun_rilis }}" min="1900" max="2099" placeholder="2024">
                                                </div>

                                                <!-- Stok / Limit Room Bersamaan Terpisah Per Outlet -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold text-muted d-flex justify-content-between align-items-center mb-1">
                                                        <span>Stok / Limit Room Bersamaan Per Outlet <span class="text-danger">*</span></span>
                                                        <span class="badge bg-emerald-subtle text-emerald" style="font-size: 0.68rem;">Independen Tiap Outlet</span>
                                                    </label>
                                                    <div class="p-3 bg-light rounded-3 border">
                                                        <div class="row g-2">
                                                            @foreach($cabangs as $cabang)
                                                                <div class="col-md-6">
                                                                    <label class="form-label small text-dark fw-semibold mb-1">
                                                                        <i class="bi bi-shop me-1 text-emerald"></i>{{ $cabang->nama_cabang }}
                                                                    </label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" name="stok_cabang[{{ $cabang->id }}]" class="form-control text-end fw-bold font-monospace" min="0" max="100" value="{{ $film->getStokCabang($cabang->id, $film->limit_penggunaan ?? 5) }}" required>
                                                                        <span class="input-group-text bg-white text-muted">Room</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                                                            <i class="bi bi-info-circle me-1"></i>Stok dikelola terpisah per outlet. Jika pemakaian di Outlet A berkurang/bertambah, stok Outlet B tidak terpengaruh.
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- Durasi & Harga per Menit (Auto-Calculated) -->
                                                <div class="col-12">
                                                    <div class="card border rounded-3 p-3 bg-light">
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            <i class="bi bi-calculator-fill text-emerald"></i>
                                                            <span class="fw-bold text-dark small">Kalkulasi Tarif Durasi Film Real-Time</span>
                                                        </div>
                                                        <div class="row g-2 align-items-center">
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">Durasi Film (Menit) <span class="text-danger">*</span></label>
                                                                <input type="number" name="durasi_menit" id="edit_durasi_{{ $film->id }}" class="form-control rounded-3 input-calc-durasi" min="1" value="{{ $film->durasi_menit }}" data-id="{{ $film->id }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">Tarif per Menit (Rp) <span class="text-danger">*</span></label>
                                                                <input type="number" step="0.01" name="harga_per_menit" id="edit_harga_{{ $film->id }}" class="form-control rounded-3 input-calc-harga" min="0" value="{{ $film->harga_per_menit }}" data-id="{{ $film->id }}" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-semibold">Total Harga Terkalkulasi</label>
                                                                <div class="form-control bg-white fw-bold text-emerald border-emerald" id="edit_preview_total_{{ $film->id }}">
                                                                    {{ $film->total_harga_format }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Multi-Genre with Add Button -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                                        <span>Genre Film (Dapat memilih lebih dari 1 genre) <span class="text-danger">*</span></span>
                                                    </label>
                                                    <div class="genre-wrapper-edit" id="genreWrapperEdit{{ $film->id }}">
                                                        @php
                                                            $currentGenreIds = $film->genres->pluck('id')->toArray();
                                                            if (empty($currentGenreIds)) {
                                                                $currentGenreIds = [$genres->first()->id ?? ''];
                                                            }
                                                        @endphp
                                                        @foreach($currentGenreIds as $idx => $selectedGenreId)
                                                            <div class="input-group mb-2 genre-row-edit">
                                                                <span class="input-group-text bg-light text-muted"><i class="bi bi-tag"></i></span>
                                                                <select name="genres[]" class="form-select rounded-end select-genre-edit" required>
                                                                    <option value="" disabled>-- Pilih Genre --</option>
                                                                    @foreach($genres as $g)
                                                                        <option value="{{ $g->id }}" {{ $g->id == $selectedGenreId ? 'selected' : '' }}>
                                                                            {{ $g->nama_genre }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-outline-danger btn-remove-genre-edit ms-2 rounded-3" {{ count($currentGenreIds) <= 1 ? 'disabled' : '' }}>
                                                                    <i class="bi bi-x-lg"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-outline-emerald btn-sm rounded-3 mt-1 btn-add-genre-edit" data-target="genreWrapperEdit{{ $film->id }}">
                                                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Genre Lain
                                                    </button>
                                                </div>

                                                <!-- Upload Cover Film with Autocompress -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold">Cover / Poster Film</label>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="{{ $film->poster_url }}" alt="Preview" class="rounded-3 border shadow-sm" style="width: 70px; height: 95px; object-fit: cover;">
                                                        <div class="flex-fill">
                                                            <input type="file" name="poster" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp">
                                                            <span class="text-muted small" style="font-size: 0.75rem;">
                                                                Format: JPG, JPEG, PNG, WEBP. Otomatis dikompres ke &le; 500 KB. Biarkan kosong jika tidak ingin mengubah cover.
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Status -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold">Status Ketersediaan <span class="text-danger">*</span></label>
                                                    <select name="status" class="form-select rounded-3" required>
                                                        <option value="tersedia" {{ $film->status == 'tersedia' ? 'selected' : '' }}>Tersedia (Bisa Ditonton)</option>
                                                        <option value="tidak_tersedia" {{ $film->status == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                                                    </select>
                                                </div>

                                                <!-- Sinopsis -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold">Sinopsis / Deskripsi Alur Cerita</label>
                                                    <textarea name="sinopsis" rows="3" class="form-control rounded-3" placeholder="Ringkasan cerita film...">{{ $film->sinopsis }}</textarea>
                                                </div>
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

                        <!-- Modal Hapus Film -->
                        <div class="modal fade" id="modalHapusFilm{{ $film->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-body text-center p-4">
                                        <div class="p-3 bg-danger-subtle text-danger rounded-circle d-inline-flex mb-3">
                                            <i class="bi bi-trash3-fill fs-3"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Hapus Film?</h6>
                                        <p class="text-muted small mb-4">Apakah Anda yakin ingin menghapus film <strong>{{ $film->judul }}</strong>? Berkas cover poster juga akan dihapus dari server.</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.film.destroy', $film->id) }}" method="POST" class="flex-fill">
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
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="bi bi-camera-reels fs-1"></i></div>
                                <h6 class="fw-bold text-dark">Belum ada koleksi film</h6>
                                <p class="text-muted small">Tambahkan film bioskop studio dengan durasi, cover, dan multi-genre sekarang.</p>
                                <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahFilm">
                                    Tambah Film Baru Sekarang
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($films->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $films->links() }}
            </div>
        @endif
    </div>
</div>

@foreach ($films as $film)
    <div class="modal fade" id="modalDetailFilm{{ $film->id }}" tabindex="-1" aria-labelledby="modalDetailFilmLabel{{ $film->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-heading" id="modalDetailFilmLabel{{ $film->id }}">Detail Film</h5>
                        <span class="text-muted small">Informasi lengkap film di katalog studio.</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <img src="{{ $film->poster_url }}" alt="Poster {{ $film->judul }}" class="img-fluid rounded-3 shadow-sm" style="max-height: 360px; object-fit: contain;">
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                <h4 class="fw-bold text-heading mb-0">{{ $film->judul }}</h4>
                                {!! $film->usia_rating_badge !!}
                            </div>
                            <div class="mb-3">{!! $film->status_badge !!}</div>

                            <dl class="row mb-3">
                                <dt class="col-sm-5 text-muted">Sutradara</dt>
                                <dd class="col-sm-7">{{ $film->sutradara ?: '-' }}</dd>
                                <dt class="col-sm-5 text-muted">Tahun Rilis</dt>
                                <dd class="col-sm-7">{{ $film->tahun_rilis ?: '-' }}</dd>
                                <dt class="col-sm-5 text-muted">Durasi</dt>
                                <dd class="col-sm-7">{{ $film->durasi_formatted }}</dd>
                                <dt class="col-sm-5 text-muted">Stok Per Outlet</dt>
                                <dd class="col-sm-7">
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($cabangs as $cabang)
                                            @php
                                                $stokVal = $film->getLimitPenggunaanForCabang($cabang->id);
                                                $activeVal = $film->getActiveRoomsCount($cabang->id);
                                                $sisaVal = max(0, $stokVal - $activeVal);
                                            @endphp
                                            <div class="d-flex justify-content-between align-items-center bg-light p-1 px-2 rounded border small" style="font-size: 0.75rem;">
                                                <span class="fw-semibold text-dark"><i class="bi bi-shop text-emerald me-1"></i>{{ $cabang->nama_cabang }}</span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge bg-white text-dark border font-monospace fw-bold">{{ $stokVal }} Room</span>
                                                    @if($sisaVal > 0)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Sisa {{ $sisaVal }}</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Penuh</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </dd>
                                <dt class="col-sm-5 text-muted">Tarif per Menit</dt>
                                <dd class="col-sm-7">{{ $film->harga_per_menit_format }}</dd>
                                <dt class="col-sm-5 text-muted">Total Harga</dt>
                                <dd class="col-sm-7 fw-bold text-emerald">{{ $film->total_harga_format }}</dd>
                            </dl>

                            <div class="mb-3">
                                <div class="fw-semibold small mb-2">Genre</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($film->genres as $genre)
                                        <span class="badge bg-light text-dark border px-2 py-1">{{ $genre->nama_genre }}</span>
                                    @empty
                                        <span class="text-muted small fst-italic">Tanpa Genre</span>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <div class="fw-semibold small mb-1">Sinopsis</div>
                                <p class="text-muted mb-0" style="white-space: pre-line;">{{ $film->sinopsis ?: 'Belum ada sinopsis.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal Tambah Film Baru -->
<div class="modal fade" id="modalTambahFilm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.film.store') }}" method="POST" enctype="multipart/form-data" id="formTambahFilm" class="film-modal-form">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-heading">Tambah Film Bioskop Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-3">
                        <!-- Judul Film -->
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Judul Film <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control rounded-3" placeholder="Contoh: Interstellar, Pengabdi Setan" required>
                        </div>

                        <!-- Rating Usia -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Rating Usia <span class="text-danger">*</span></label>
                            <select name="usia_rating" class="form-select rounded-3" required>
                                <option value="SU" selected>SU (Semua Umur)</option>
                                <option value="13+">13+ (Remaja)</option>
                                <option value="17+">17+ (Dewasa)</option>
                                <option value="21+">21+ (Dewasa Khusus)</option>
                            </select>
                        </div>

                        <!-- Sutradara & Tahun Rilis -->
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Sutradara</label>
                            <input type="text" name="sutradara" class="form-control rounded-3" placeholder="Nama sutradara">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Tahun Rilis</label>
                            <input type="number" name="tahun_rilis" class="form-control rounded-3" min="1900" max="2099" value="{{ date('Y') }}" placeholder="2024">
                        </div>

                        <!-- Stok / Limit Room Bersamaan Terpisah Per Outlet -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted d-flex justify-content-between align-items-center mb-1">
                                <span>Stok / Limit Room Bersamaan Per Outlet <span class="text-danger">*</span></span>
                            </label>
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="row g-2">
                                    @foreach($cabangs as $cabang)
                                        <div class="col-md-6">
                                            <label class="form-label small text-dark fw-semibold mb-1">
                                                <i class="bi bi-shop me-1 text-emerald"></i>{{ $cabang->nama_cabang }}
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="stok_cabang[{{ $cabang->id }}]" class="form-control text-end fw-bold font-monospace" min="0" max="100" value="{{ old('stok_cabang.' . $cabang->id, 5) }}" required>
                                                <span class="input-group-text bg-white text-muted">Room</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Durasi & Harga per Menit (Auto-Calculated) -->
                        <div class="col-12">
                            <div class="card border rounded-3 p-3 bg-light">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-calculator-fill text-emerald fs-5"></i>
                                    <div>
                                        <span class="fw-bold text-dark small">Kalkulasi Tarif Durasi Film Real-Time</span>
                                        <span class="text-muted d-block" style="font-size: 0.75rem;">Total harga dihitung otomatis dari durasi dikalikan tarif per menit.</span>
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Durasi Film (Menit) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="durasi_menit" id="tambah_durasi_menit" class="form-control rounded-start" min="1" placeholder="120" value="120" required>
                                            <span class="input-group-text bg-white small text-muted">Mnt</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Tarif per Menit (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white small text-muted">Rp</span>
                                            <input type="number" step="0.01" name="harga_per_menit" id="tambah_harga_per_menit" class="form-control" min="0" placeholder="500" value="500" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Total Harga Film</label>
                                        <div class="form-control bg-white fw-bold text-emerald border-emerald d-flex align-items-center justify-content-between" id="tambah_preview_total">
                                            <span>Rp 60.000</span>
                                            <span class="badge bg-emerald-subtle text-emerald small" style="font-size: 0.7rem;">Otomatis</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multi-Genre with Add Button -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                <span>Pilih Genre Film (Bisa memilih lebih dari 1 genre) <span class="text-danger">*</span></span>
                            </label>
                            <div id="genreWrapperTambah">
                                <div class="input-group mb-2 genre-row-tambah">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-tag"></i></span>
                                    <select name="genres[]" class="form-select rounded-end select-genre" required>
                                        <option value="" disabled selected>-- Pilih Genre Film --</option>
                                        @foreach($genres as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama_genre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-danger btn-remove-genre ms-2 rounded-3" disabled>
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-emerald btn-sm rounded-3 mt-1" id="btnTambahGenreBaris">
                                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Genre Lain
                            </button>
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                Klik tombol <strong>+ Tambah Genre Lain</strong> jika film memiliki 2 genre atau lebih (misal Horror & Comedy).
                            </small>
                        </div>

                        <!-- Upload Cover Film with Autocompress -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Unggah Cover / Poster Film</label>
                            <div class="upload-poster-card p-3 border rounded-3 bg-light text-center">
                                <i class="bi bi-cloud-arrow-up-fill text-emerald fs-2"></i>
                                <div class="fw-semibold small text-dark mt-1">Pilih berkas cover poster film</div>
                                <div class="text-muted small mb-2" style="font-size: 0.75rem;">
                                    Format: JPG, JPEG, PNG, WEBP (Batas maksimal 20 MB, otomatis dikompres ke &le; 500 KB).
                                </div>
                                <input type="file" name="poster" id="inputCoverTambah" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp">
                                <div id="previewCoverContainer" class="mt-2 d-none">
                                    <img id="previewCoverImg" src="" alt="Preview Poster" class="rounded-3 shadow-sm border mt-2" style="max-height: 160px; object-fit: contain;">
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Status Ketersediaan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select rounded-3" required>
                                <option value="tersedia" selected>Tersedia (Bisa Ditonton)</option>
                                <option value="tidak_tersedia">Tidak Tersedia</option>
                            </select>
                        </div>

                        <!-- Sinopsis -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Sinopsis / Ringkasan Cerita</label>
                            <textarea name="sinopsis" rows="3" class="form-control rounded-3" placeholder="Tuliskan alur singkat atau sinopsis film..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4">Simpan Film</button>
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
        background-color: #d1fae5 !important;
    }
    .film-poster-container {
        width: 58px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .film-poster-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .film-modal-form {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    .film-modal-form .modal-body {
        overflow-y: auto;
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -----------------------------------------------------------
    // 1. REAL-TIME KALKULASI HARGA DURASI (FORM TAMBAH)
    // -----------------------------------------------------------
    const durasiInputTambah = document.getElementById('tambah_durasi_menit');
    const hargaInputTambah = document.getElementById('tambah_harga_per_menit');
    const previewTotalTambah = document.getElementById('tambah_preview_total');

    function hitungTotalTambah() {
        const durasi = parseFloat(durasiInputTambah?.value) || 0;
        const harga = parseFloat(hargaInputTambah?.value) || 0;
        const total = Math.round(durasi * harga);

        if (previewTotalTambah) {
            const formatRupiah = 'Rp ' + total.toLocaleString('id-ID');
            previewTotalTambah.innerHTML = `<span>${formatRupiah}</span><span class="badge bg-emerald-subtle text-emerald small" style="font-size: 0.7rem;">Otomatis</span>`;
        }
    }

    if (durasiInputTambah && hargaInputTambah) {
        durasiInputTambah.addEventListener('input', hitungTotalTambah);
        hargaInputTambah.addEventListener('input', hitungTotalTambah);
        hitungTotalTambah();
    }

    // -----------------------------------------------------------
    // 2. REAL-TIME KALKULASI HARGA DURASI (FORM EDIT)
    // -----------------------------------------------------------
    document.querySelectorAll('.input-calc-durasi, .input-calc-harga').forEach(input => {
        input.addEventListener('input', function () {
            const id = this.getAttribute('data-id');
            const durasiEl = document.getElementById(`edit_durasi_${id}`);
            const hargaEl = document.getElementById(`edit_harga_${id}`);
            const previewEl = document.getElementById(`edit_preview_total_${id}`);

            if (durasiEl && hargaEl && previewEl) {
                const durasi = parseFloat(durasiEl.value) || 0;
                const harga = parseFloat(hargaEl.value) || 0;
                const total = Math.round(durasi * harga);
                previewEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }
        });
    });

    // -----------------------------------------------------------
    // 3. MULTI-GENRE DENGAN TOMBOL TAMBAH (FORM TAMBAH)
    // -----------------------------------------------------------
    const btnTambahGenreBaris = document.getElementById('btnTambahGenreBaris');
    const genreWrapperTambah = document.getElementById('genreWrapperTambah');

    // Template options genre dari baris pertama
    const genreOptionsHTML = `@foreach($genres as $g)<option value="{{ $g->id }}">{{ $g->nama_genre }}</option>@endforeach`;

    if (btnTambahGenreBaris && genreWrapperTambah) {
        btnTambahGenreBaris.addEventListener('click', function () {
            const newRow = document.createElement('div');
            newRow.className = 'input-group mb-2 genre-row-tambah';
            newRow.innerHTML = `
                <span class="input-group-text bg-light text-muted"><i class="bi bi-tag"></i></span>
                <select name="genres[]" class="form-select rounded-end select-genre" required>
                    <option value="" disabled selected>-- Pilih Genre Tambahan --</option>
                    ${genreOptionsHTML}
                </select>
                <button type="button" class="btn btn-outline-danger btn-remove-genre ms-2 rounded-3">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;
            genreWrapperTambah.appendChild(newRow);
            updateRemoveButtonStateTambah();
        });

        genreWrapperTambah.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-genre');
            if (removeBtn) {
                const row = removeBtn.closest('.genre-row-tambah');
                const allRows = genreWrapperTambah.querySelectorAll('.genre-row-tambah');
                if (allRows.length > 1) {
                    row.remove();
                    updateRemoveButtonStateTambah();
                }
            }
        });

        function updateRemoveButtonStateTambah() {
            const allRows = genreWrapperTambah.querySelectorAll('.genre-row-tambah');
            allRows.forEach(row => {
                const btn = row.querySelector('.btn-remove-genre');
                if (btn) {
                    btn.disabled = (allRows.length <= 1);
                }
            });
        }
    }

    // -----------------------------------------------------------
    // 4. MULTI-GENRE DENGAN TOMBOL TAMBAH (FORM EDIT)
    // -----------------------------------------------------------
    document.querySelectorAll('.btn-add-genre-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetWrapperId = this.getAttribute('data-target');
            const wrapper = document.getElementById(targetWrapperId);
            if (wrapper) {
                const newRow = document.createElement('div');
                newRow.className = 'input-group mb-2 genre-row-edit';
                newRow.innerHTML = `
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-tag"></i></span>
                    <select name="genres[]" class="form-select rounded-end select-genre-edit" required>
                        <option value="" disabled selected>-- Pilih Genre Tambahan --</option>
                        ${genreOptionsHTML}
                    </select>
                    <button type="button" class="btn btn-outline-danger btn-remove-genre-edit ms-2 rounded-3">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;
                wrapper.appendChild(newRow);
                updateRemoveButtonsEdit(wrapper);
            }
        });
    });

    document.querySelectorAll('.genre-wrapper-edit').forEach(wrapper => {
        wrapper.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.btn-remove-genre-edit');
            if (removeBtn) {
                const row = removeBtn.closest('.genre-row-edit');
                const allRows = wrapper.querySelectorAll('.genre-row-edit');
                if (allRows.length > 1) {
                    row.remove();
                    updateRemoveButtonsEdit(wrapper);
                }
            }
        });
    });

    function updateRemoveButtonsEdit(wrapper) {
        const allRows = wrapper.querySelectorAll('.genre-row-edit');
        allRows.forEach(row => {
            const btn = row.querySelector('.btn-remove-genre-edit');
            if (btn) {
                btn.disabled = (allRows.length <= 1);
            }
        });
    }

    // -----------------------------------------------------------
    // 5. LIVE IMAGE PREVIEW UNTUK COVER POSTER
    // -----------------------------------------------------------
    const inputCoverTambah = document.getElementById('inputCoverTambah');
    const previewCoverContainer = document.getElementById('previewCoverContainer');
    const previewCoverImg = document.getElementById('previewCoverImg');

    if (inputCoverTambah && previewCoverImg && previewCoverContainer) {
        inputCoverTambah.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewCoverImg.src = e.target.result;
                    previewCoverContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                previewCoverContainer.classList.add('d-none');
            }
        });
    }
});
</script>
@endpush
