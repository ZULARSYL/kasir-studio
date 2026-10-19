@extends('template.index')

@section('title', 'Menu Kasir POS | Pressent Studio & Cafe')

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

    {{-- ========================================================================= --}}
    {{-- KONDISI 1: KASIR BELUM BUKA SHIFT / BELUM MEMASUKKAN MODAL AWAL           --}}
    {{-- ========================================================================= --}}
    @if(! $hasActiveShift)
        <div class="row justify-content-center py-4">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="bg-gradient-emerald p-4 text-white text-center">
                        <div class="stat-icon-circle bg-white text-emerald mx-auto mb-3 shadow-sm">
                            <i class="bi bi-cash-coin fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Buka Shift Kasir Hari Ini</h4>
                        <p class="small text-white-50 mb-0">
                            Masukkan modal awal kas dan pilih outlet sebelum memulai transaksi penjualan.
                        </p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('kasir.shift.buka') }}" method="POST">
                            @csrf

                            <!-- Info Kasir -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Petugas Kasir</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0 fw-semibold" value="{{ auth()->user()->name }} ({{ auth()->user()->username }})" readonly>
                                </div>
                            </div>

                            <!-- Pilih Outlet Cabang -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Pilih Outlet Cabang <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop"></i></span>
                                    <select name="toko_cabang_id" class="form-select border-start-0" required>
                                        <option value="" disabled selected>-- Pilih Cabang Penugasan --</option>
                                        @foreach($cabangs as $cabang)
                                            <option value="{{ $cabang->id }}" {{ old('toko_cabang_id') == $cabang->id ? 'selected' : '' }}>
                                                {{ $cabang->nama_cabang }} - {{ $cabang->alamat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Modal Awal Kas -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Nominal Modal Awal Kas (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-light border-end-0 fw-bold font-monospace">Rp</span>
                                    <input type="number" step="any" min="0" name="modal_awal" id="inputModalAwal" 
                                           class="form-control border-start-0 font-monospace fs-5 fw-bold" 
                                           placeholder="0" value="{{ old('modal_awal', 200000) }}" required>
                                </div>
                                <!-- Quick Suggestion Chips -->
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 small btn-quick-modal" data-val="100000">Rp 100.000</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 small btn-quick-modal" data-val="200000">Rp 200.000</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 small btn-quick-modal" data-val="300000">Rp 300.000</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 small btn-quick-modal" data-val="500000">Rp 500.000</button>
                                </div>
                            </div>

                            <!-- Catatan Pembukaan -->
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-muted">Catatan Pembukaan (Opsional)</label>
                                <textarea name="catatan_buka" class="form-control rounded-3" rows="2" placeholder="Kondisi laci kasir, uang kembalian pecahan, dll...">{{ old('catatan_buka') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-emerald w-100 py-3 rounded-3 shadow-sm fw-bold fs-6 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-box-arrow-in-right fs-5"></i>
                                <span>Buka Kasir & Mulai Transaksi</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- KONDISI 2: SHIFT KASIR SUDAH AKTIF (POS TRANSAKSI PENJUALAN)              --}}
    {{-- ========================================================================= --}}
    @else
        <!-- Shift Top Bar Status -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-emerald-subtle text-emerald rounded-4 d-flex align-items-center justify-content-center">
                            <i class="bi bi-calculator fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="fw-bold mb-0 text-heading">Menu Kasir (POS)</h4>
                                <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-3 py-1">
                                    <i class="bi bi-record-fill me-1"></i>Shift Aktif
                                </span>
                            </div>
                            <div class="text-muted small">
                                Kasir: <strong>{{ auth()->user()->name }}</strong> &bull; 
                                Outlet: <strong class="text-dark">{{ $activeShift->tokoCabang->nama_cabang }}</strong> &bull; 
                                Buka: {{ $activeShift->waktu_buka->isoFormat('D MMM Y, HH:mm') }} WIB
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-none d-lg-flex flex-column text-end pe-3 border-end">
                            <span class="text-muted small">Modal Awal Kas</span>
                            <strong class="font-monospace text-emerald">Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-none d-lg-flex flex-column text-end pe-3 border-end">
                            <span class="text-muted small">Penjualan Shift Ini</span>
                            <strong class="font-monospace text-dark">Rp {{ number_format($activeShift->total_penjualan, 0, ',', '.') }}</strong>
                        </div>
                        <button type="button" class="btn btn-outline-secondary rounded-3 px-3 py-2 btn-reset-pos" title="Reset Pilihan Form">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Transaksi Baru
                        </button>
                        <button type="button" class="btn btn-outline-danger rounded-3 px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTutupShift">
                            <i class="bi bi-door-closed me-1"></i> Tutup Kasir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- POS Workspace: 2 Kolom (Kiri: Katalog Film, Room, Cafe. Kanan: Sticky Cart & Checkout) -->
        <div class="row g-4">
            
            <!-- KOLOM KIRI: WORKFLOW PEMILIHAN (FILM -> ROOM -> CAFE) -->
            <div class="col-12 col-xl-8">

                <!-- STEP 1: PEMILIHAN FILM (REFERENSI SIDEBAR/MOVIE.PNG) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 cinema-dark-panel">
                    <div class="card-header border-0 bg-transparent p-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-bold">STEP 1</span>
                                <h5 class="fw-bold text-white mb-0">
                                    <i class="bi bi-film me-2 text-danger"></i>Pilih Film Studio
                                </h5>
                            </div>
                            <p class="text-white-50 small mb-0">Klik film yang ingin ditonton oleh pelanggan.</p>
                        </div>
                        <!-- Search Movie -->
                        <div style="min-width: 240px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-dark-input text-white-50 border-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchMovieInput" class="form-control bg-dark-input text-white border-0" placeholder="Cari judul film...">
                            </div>
                        </div>
                    </div>

                    <!-- Genre Pills Filter -->
                    <div class="px-4 py-2 border-bottom border-dark-subtle d-flex flex-nowrap gap-2 overflow-x-auto cinema-filter-scroll">
                        <button type="button" class="btn btn-sm btn-cinema-pill active" data-genre="all">Semua Film</button>
                        @foreach($genres as $genre)
                            <button type="button" class="btn btn-sm btn-cinema-pill" data-genre="{{ $genre->nama_genre }}">
                                {{ $genre->nama_genre }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Movie Cards Grid (Mirip movie.png) -->
                    <div class="card-body p-4">
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3" id="movieGrid">
                            @forelse($films as $film)
                                @php
                                    $genreNames = $film->genres->pluck('nama_genre')->implode(' ');
                                    $price = (float) $film->total_harga > 0 ? (float) $film->total_harga : ((float) $film->harga_per_menit * (int) $film->durasi_menit);
                                @endphp
                                <div class="col movie-card-wrapper" data-genre="{{ $genreNames }}" data-title="{{ strtolower($film->judul) }}">
                                    <div class="movie-card rounded-4 position-relative overflow-hidden h-100" 
                                         data-id="{{ $film->id }}"
                                         data-judul="{{ $film->judul }}"
                                         data-durasi="{{ $film->durasi_menit }}"
                                         data-harga="{{ $price }}"
                                         data-poster="{{ $film->poster_url }}">
                                        
                                        <!-- Top Rank / ID Badge (Mirip movie.png #1, #2, etc) -->
                                        <div class="movie-rank-badge">
                                            #{{ $loop->iteration }}
                                        </div>

                                        <!-- Poster Image -->
                                        <img src="{{ $film->poster_url }}" alt="{{ $film->judul }}" class="movie-poster-img" loading="lazy">

                                        <!-- Overlay Gradient & Content -->
                                        <div class="movie-card-overlay p-3 d-flex flex-column justify-content-end">
                                            <!-- Red Play Icon Circle -->
                                            <div class="movie-play-circle mb-2">
                                                <i class="bi bi-play-fill fs-4"></i>
                                            </div>
                                            <h6 class="fw-bold text-white mb-1 movie-card-title text-truncate" title="{{ $film->judul }}">
                                                {{ $film->judul }}
                                            </h6>
                                            <div class="d-flex align-items-center justify-content-between text-white-50 small mb-2">
                                                <span><i class="bi bi-clock me-1"></i>{{ $film->durasi_menit }}m</span>
                                                <span class="text-warning"><i class="bi bi-star-fill me-1"></i>4.8</span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="badge bg-dark-subtle text-white border border-secondary px-2 py-1 font-monospace small">
                                                    Rp {{ number_format($price, 0, ',', '.') }}
                                                </span>
                                                <span class="badge bg-danger-subtle text-danger px-1.5 py-0.5 rounded font-monospace" style="font-size: 0.65rem;">
                                                    {{ $film->usia_rating }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Selection Indicator Checkmark -->
                                        <div class="movie-selected-check">
                                            <i class="bi bi-check-circle-fill fs-3 text-emerald"></i>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-5 text-center text-white-50">
                                    <i class="bi bi-film fs-1 mb-2 d-block"></i>
                                    Belum ada film yang tersedia di database.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PEMILIHAN ROOM STUDIO YANG TERSEDIA -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom p-4 pb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-emerald text-white rounded-pill px-2.5 py-1 small fw-bold">STEP 2</span>
                                    <h5 class="fw-bold text-heading mb-0">
                                        <i class="bi bi-door-open-fill text-emerald me-2"></i>Pilih Room Studio Tersedia
                                    </h5>
                                </div>
                                <p class="text-muted small mb-0">Pilih ruangan studio yang kosong dan siap digunakan di cabang ini.</p>
                            </div>
                            <span class="badge bg-light text-muted border px-3 py-1.5 rounded-3" id="selectedFilmBadgeIndicator">
                                Film: <strong class="text-dark">Belum dipilih</strong>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row row-cols-1 row-cols-md-3 g-3" id="roomGrid">
                            @forelse($rooms as $room)
                                @php
                                    $isOccupied = in_array($room->id, $occupiedRoomIds);
                                @endphp
                                <div class="col">
                                    <div class="room-card card border rounded-4 p-3 h-100 position-relative transition-all {{ $isOccupied ? 'room-occupied bg-light opacity-75' : 'room-available cursor-pointer' }}"
                                         data-id="{{ $room->id }}"
                                         data-nama="{{ $room->nama }}"
                                         data-kapasitas="{{ $room->kapasitas }}"
                                         data-occupied="{{ $isOccupied ? '1' : '0' }}">
                                        
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="stat-icon-wrapper rounded-3 {{ $isOccupied ? 'bg-secondary text-white' : 'bg-emerald-subtle text-emerald' }} d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                <i class="bi bi-tv fs-4"></i>
                                            </div>
                                            @if($isOccupied)
                                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 rounded-pill small">
                                                    <i class="bi bi-x-circle me-1"></i>Terpakai
                                                </span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill small">
                                                    <i class="bi bi-check-circle me-1"></i>Tersedia
                                                </span>
                                            @endif
                                        </div>

                                        <h6 class="fw-bold text-dark mb-1">{{ $room->nama }}</h6>
                                        <div class="text-muted small mb-3">
                                            <i class="bi bi-people me-1"></i>Kapasitas: <strong>{{ $room->kapasitas }} Orang</strong>
                                        </div>

                                        <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                            <span class="small text-muted">Status:</span>
                                            <strong class="{{ $isOccupied ? 'text-danger' : 'text-emerald' }} small">
                                                {{ $isOccupied ? 'Sedang Digunakan' : 'Siap Digunakan' }}
                                            </strong>
                                        </div>

                                        <!-- Selection Indicator -->
                                        <div class="room-selected-badge position-absolute top-0 end-0 m-2" style="display: none;">
                                            <i class="bi bi-check-circle-fill fs-4 text-emerald"></i>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-4 text-center text-muted">
                                    <i class="bi bi-door-closed fs-1 mb-2 d-block"></i>
                                    Belum ada master room studio yang terdaftar pada cabang ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- STEP 3: PESAN MAKANAN / MINUMAN CAFE (OPSIONAL) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom p-4 pb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-bold">STEP 3</span>
                                <h5 class="fw-bold text-heading mb-0">
                                    <i class="bi bi-cup-hot-fill text-warning me-2"></i>Pesan Makanan & Minuman Cafe (Opsional)
                                </h5>
                            </div>
                            <p class="text-muted small mb-0">Tambah makanan, minuman, atau snack jika pelanggan memesan.</p>
                        </div>
                        <div style="min-width: 220px;">
                            <input type="text" id="searchCafeInput" class="form-control form-control-sm rounded-3" placeholder="Cari menu cafe...">
                        </div>
                    </div>

                    <!-- Category Pills -->
                    <div class="px-4 py-2 border-bottom d-flex gap-2 overflow-x-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill active btn-cafe-cat" data-category="all">Semua Menu</button>
                        @foreach($kategoriCafes as $kategori)
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-cafe-cat" data-category="{{ $kategori->nama }}">
                                {{ $kategori->nama }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Cafe Items Grid -->
                    <div class="card-body p-4">
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3" id="cafeGrid">
                            @forelse($cafeItems as $cItem)
                                @php
                                    $stokTersedia = (float) $cItem->stok;
                                    $hargaJual = (float) $cItem->harga_jual;
                                @endphp
                                <div class="col cafe-item-wrapper" data-category="{{ $cItem->nama_kategori }}" data-name="{{ strtolower($cItem->nama_barang) }}">
                                    <div class="card border rounded-4 p-3 h-100 d-flex flex-column cafe-card transition-all">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-light text-muted border small">{{ $cItem->nama_kategori ?? 'Umum' }}</span>
                                            <span class="badge bg-emerald-subtle text-emerald border border-emerald small font-monospace">
                                                Stok: {{ (int)$stokTersedia == $stokTersedia ? (int)$stokTersedia : $stokTersedia }} {{ $cItem->nama_satuan }}
                                            </span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $cItem->nama_barang }}">{{ $cItem->nama_barang }}</h6>
                                        <div class="fw-bold text-emerald font-monospace mb-3">
                                            Rp {{ number_format($hargaJual, 0, ',', '.') }}
                                        </div>

                                        <div class="mt-auto">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-emerald w-100 rounded-3 btn-add-cafe"
                                                    data-id="{{ $cItem->barang_id }}"
                                                    data-nama="{{ $cItem->nama_barang }}"
                                                    data-satuan-id="{{ $cItem->satuan_barang_id }}"
                                                    data-satuan="{{ $cItem->nama_satuan }}"
                                                    data-stok="{{ $stokTersedia }}"
                                                    data-harga="{{ $hargaJual }}">
                                                <i class="bi bi-plus-circle me-1"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-4 text-center text-muted">
                                    <i class="bi bi-cup-straw fs-1 mb-2 d-block"></i>
                                    Belum ada stok menu cafe di cabang ini. Silakan input faktur barang terlebih dahulu.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: STICKY ORDER CART & CHECKOUT PANEL -->
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top checkout-panel" style="top: 85px;">
                    <div class="card-header bg-white border-bottom p-4 pb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-heading mb-0">
                                <i class="bi bi-receipt text-emerald me-2"></i>Rincian Pesanan
                            </h5>
                            <span class="badge bg-light text-muted border font-monospace" id="labelNoPesanan">Draft POS</span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Input Nama Pelanggan -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nama Pelanggan (Opsional)</label>
                            <input type="text" id="inputNamaPelanggan" class="form-control rounded-3" placeholder="Pelanggan Umum" value="Pelanggan Umum">
                        </div>

                        <!-- Rincian Item yang Dipilih -->
                        <div class="border rounded-3 p-3 mb-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                            <!-- Item Film -->
                            <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom" id="cartItemFilm" style="display: none !important;">
                                <div class="me-2 overflow-hidden">
                                    <span class="badge bg-danger text-white small" style="font-size: 0.65rem;">TIKET FILM</span>
                                    <div class="fw-bold text-dark text-truncate" id="cartFilmJudul">-</div>
                                    <div class="small text-muted" id="cartFilmDurasi">-</div>
                                </div>
                                <div class="fw-bold font-monospace text-emerald text-end" id="cartFilmHarga">Rp 0</div>
                            </div>

                            <!-- Item Room -->
                            <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom" id="cartItemRoom" style="display: none !important;">
                                <div class="me-2 overflow-hidden">
                                    <span class="badge bg-emerald text-white small" style="font-size: 0.65rem;">ROOM STUDIO</span>
                                    <div class="fw-bold text-dark text-truncate" id="cartRoomNama">-</div>
                                    <div class="small text-muted" id="cartRoomKapasitas">-</div>
                                </div>
                                <div class="fw-semibold font-monospace text-muted text-end small">Included</div>
                            </div>

                            <!-- List Cafe Items -->
                            <div id="cartCafeContainer">
                                <!-- Injected dynamically by JS -->
                            </div>

                            <!-- Placeholder kosong jika belum ada pilihan -->
                            <div id="cartEmptyPlaceholder" class="text-center py-4 text-muted">
                                <i class="bi bi-cart3 fs-2 mb-1 d-block"></i>
                                <span class="small">Belum ada item yang dipilih.<br>Silakan pilih film & room di sebelah kiri.</span>
                            </div>
                        </div>

                        <!-- Subtotal Details -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Subtotal Tiket Film:</span>
                                <span class="font-monospace text-dark fw-semibold" id="labelSubtotalFilm">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Subtotal Cafe & Snack:</span>
                                <span class="font-monospace text-dark fw-semibold" id="labelSubtotalCafe">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small text-success mb-1" id="rowDiskonPromo" style="display: none;">
                                <span>Diskon Promo:</span>
                                <span class="font-monospace fw-bold" id="labelDiskonPromo">- Rp 0</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">TOTAL TAGIHAN:</span>
                                <span class="fs-4 fw-bold text-emerald font-monospace" id="labelTotalTagihan">Rp 0</span>
                            </div>
                        </div>

                        <!-- Voucher Promo Section -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Kode Voucher Promo (Opsional)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="inputKodePromo" class="form-control text-uppercase font-monospace" placeholder="MASUKKAN VOUCHER">
                                <button type="button" class="btn btn-outline-secondary" id="btnTerapkanPromo">Terapkan</button>
                            </div>
                            <div class="small text-emerald mt-1 fw-semibold" id="promoFeedbackMsg"></div>
                        </div>

                        <!-- Metode Pembayaran (Data Master) -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">
                                Metode Pembayaran (Data Master) <span class="text-danger">*</span>
                            </label>
                            <select id="selectTipePembayaran" class="form-select rounded-3 fw-semibold" required>
                                @foreach($tipePembayarans as $tp)
                                    <option value="{{ $tp->id }}" data-metode="{{ strtolower($tp->metode_pembayaran) }}">
                                        {{ $tp->metode_pembayaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Input Uang Diterima & Kembalian (Khusus Tunai) -->
                        <div id="sectionCashPayment" class="mb-4 p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-semibold text-muted mb-1">Uang Tunai Diterima (Rp)</label>
                            <input type="number" step="any" min="0" id="inputUangDiterima" class="form-control font-monospace fs-5 fw-bold text-end mb-2" placeholder="0">
                            
                            <!-- Quick Chips Uang Pas & Pecahan -->
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <button type="button" class="btn btn-sm btn-white border py-0 px-2 small btn-chip-cash" data-type="exact">Uang Pas</button>
                                <button type="button" class="btn btn-sm btn-white border py-0 px-2 small btn-chip-cash" data-val="50000">50rb</button>
                                <button type="button" class="btn btn-sm btn-white border py-0 px-2 small btn-chip-cash" data-val="100000">100rb</button>
                                <button type="button" class="btn btn-sm btn-white border py-0 px-2 small btn-chip-cash" data-val="200000">200rb</button>
                                <button type="button" class="btn btn-sm btn-white border py-0 px-2 small btn-chip-cash" data-val="500000">500rb</button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="small fw-semibold text-muted">Kembalian:</span>
                                <span class="fs-5 fw-bold font-monospace text-dark" id="labelKembalian">Rp 0</span>
                            </div>
                        </div>

                        <!-- Tombol Checkout / Bayar & Cetak Struk -->
                        <button type="button" class="btn btn-emerald w-100 py-3 rounded-3 shadow-sm fw-bold fs-6 d-flex align-items-center justify-content-center gap-2" id="btnCheckoutBayar">
                            <i class="bi bi-printer-fill fs-5"></i>
                            <span>Bayar & Cetak Struk</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- MODAL TUTUP KASIR (TUTUP TOKO / SHIFT END)                                 -->
        <!-- ========================================================================= -->
        <div class="modal fade" id="modalTutupShift" tabindex="-1" aria-labelledby="modalTutupShiftLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <form action="{{ route('kasir.shift.tutup') }}" method="POST">
                        @csrf
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold text-danger" id="modalTutupShiftLabel">
                                <i class="bi bi-door-closed-fill me-2"></i>Tutup Shift Kasir (Tutup Toko)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Rekap Shift -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Modal Awal Kas:</span>
                                    <strong class="font-monospace text-dark">Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Penjualan Tunai:</span>
                                    <strong class="font-monospace text-emerald">Rp {{ number_format($activeShift->total_tunai, 0, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Penjualan Non-Tunai:</span>
                                    <strong class="font-monospace text-primary">Rp {{ number_format($activeShift->total_non_tunai, 0, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Total Transaksi Selesai:</span>
                                    <strong class="font-monospace text-dark">{{ $activeShift->total_transaksi }} Transaksi</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between small fw-bold text-dark">
                                    <span>Ekspektasi Kas Laci (Modal Awal + Tunai):</span>
                                    <span class="font-monospace text-emerald fs-6">
                                        Rp {{ number_format($activeShift->modal_awal + $activeShift->total_tunai, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Input Modal Akhir Fisik -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Jumlah Uang Fisik Aktual di Laci (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="any" min="0" name="modal_akhir" class="form-control font-monospace fs-5 fw-bold text-end" value="{{ $activeShift->modal_awal + $activeShift->total_tunai }}" required>
                                <div class="form-text small">Hitung dan masukkan total uang fisik yang ada di laci kasir saat ini.</div>
                            </div>

                            <!-- Catatan Penutupan -->
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-muted">Catatan Penutupan Shift (Opsional)</label>
                                <textarea name="catatan_tutup" rows="2" class="form-control rounded-3" placeholder="Keterangan selisih kas, serah terima shift, dll..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-2 d-flex justify-content-between">
                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Konfirmasi Tutup Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

</div>
@endsection

@section('styles')
<style>
    /* Emerald Theme Palette */
    .bg-gradient-emerald {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }
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
    .stat-icon-wrapper {
        width: 50px;
        height: 50px;
        flex-shrink: 0;
    }
    .stat-icon-circle {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Cinema Dark Panel Style (Sesuai movie.png) */
    .cinema-dark-panel {
        background-color: #0b0f19;
        color: #f8fafc;
    }
    .bg-dark-input {
        background-color: #1e293b !important;
        color: #ffffff !important;
    }
    .btn-cinema-pill {
        background: transparent;
        color: #94a3b8;
        border: 1px solid #334155;
        border-radius: 50rem;
        padding: 4px 14px;
        font-size: 0.78rem;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .btn-cinema-pill:hover,
    .btn-cinema-pill.active {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    /* Movie Card (Sesuai Referensi sidebar/movie.png) */
    .movie-card {
        background: #111827;
        border: 2px solid transparent;
        cursor: pointer;
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        aspect-ratio: 2 / 3;
    }
    .movie-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.25);
        border-color: #dc2626;
    }
    .movie-card.selected {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4) !important;
    }
    .movie-poster-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .movie-card:hover .movie-poster-img {
        transform: scale(1.04);
    }
    .movie-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(15,23,42,0.92) 85%);
        z-index: 2;
    }
    .movie-rank-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #dc2626;
        color: #ffffff;
        font-weight: bold;
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 4px;
        z-index: 3;
        box-shadow: 0 2px 5px rgba(0,0,0,0.4);
    }
    .movie-play-circle {
        width: 34px;
        height: 34px;
        background: #dc2626;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(220,38,38,0.5);
    }
    .movie-selected-check {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 4;
        display: none;
    }
    .movie-card.selected .movie-selected-check {
        display: block;
    }

    /* Room Cards */
    .room-available:hover {
        border-color: #059669 !important;
        background-color: #f0fdf4 !important;
        transform: translateY(-2px);
    }
    .room-card.selected {
        border-color: #059669 !important;
        background-color: #ecfdf5 !important;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.3) !important;
    }
    .room-card.selected .room-selected-badge {
        display: block !important;
    }

    /* Cafe Cards */
    .cafe-card:hover {
        border-color: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .cursor-pointer { cursor: pointer; }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------------------
    // FORM BUKA KASIR: QUICK MODAL CHIPS
    // -------------------------------------------------------------
    document.querySelectorAll('.btn-quick-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById('inputModalAwal');
            if (input) {
                input.value = this.getAttribute('data-val');
            }
        });
    });

    // -------------------------------------------------------------
    // POS ORDER STATE
    // -------------------------------------------------------------
    const orderState = {
        film: null,       // { id, judul, durasi, harga, poster }
        room: null,       // { id, nama, kapasitas }
        cafeItems: {},    // key: barang_id, value: { barang_id, nama, satuan_id, satuan, qty, harga_satuan, stok }
        promo: null,      // { id, nama, diskon }
        metodeBayarId: null,
        uangDiterima: 0
    };

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    // -------------------------------------------------------------
    // STEP 1: PILIH FILM (CINEMA GRID)
    // -------------------------------------------------------------
    const searchMovieInput = document.getElementById('searchMovieInput');
    if (searchMovieInput) {
        searchMovieInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.movie-card-wrapper').forEach(item => {
                const title = item.getAttribute('data-title') || '';
                item.style.display = title.includes(q) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.btn-cinema-pill').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-cinema-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const genre = this.getAttribute('data-genre');
            document.querySelectorAll('.movie-card-wrapper').forEach(card => {
                if (genre === 'all') {
                    card.style.display = '';
                } else {
                    const cardGenres = card.getAttribute('data-genre') || '';
                    card.style.display = cardGenres.includes(genre) ? '' : 'none';
                }
            });
        });
    });

    document.querySelectorAll('.movie-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.movie-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            orderState.film = {
                id: this.getAttribute('data-id'),
                judul: this.getAttribute('data-judul'),
                durasi: this.getAttribute('data-durasi'),
                harga: Number(this.getAttribute('data-harga') || 0),
                poster: this.getAttribute('data-poster')
            };

            const indicator = document.getElementById('selectedFilmBadgeIndicator');
            if (indicator) {
                indicator.innerHTML = `Film: <strong class="text-emerald">${orderState.film.judul} (${orderState.film.durasi}m)</strong>`;
            }

            renderCart();

            // Smooth scroll ke Step 2
            const step2El = document.getElementById('roomGrid');
            if (step2El) {
                step2El.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // -------------------------------------------------------------
    // STEP 2: PILIH ROOM STUDIO
    // -------------------------------------------------------------
    document.querySelectorAll('.room-available').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.room-available').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            orderState.room = {
                id: this.getAttribute('data-id'),
                nama: this.getAttribute('data-nama'),
                kapasitas: this.getAttribute('data-kapasitas')
            };

            renderCart();
        });
    });

    // -------------------------------------------------------------
    // STEP 3: PESAN CAFE & SNACK
    // -------------------------------------------------------------
    const searchCafeInput = document.getElementById('searchCafeInput');
    if (searchCafeInput) {
        searchCafeInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.cafe-item-wrapper').forEach(item => {
                const name = item.getAttribute('data-name') || '';
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.btn-cafe-cat').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-cafe-cat').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const cat = this.getAttribute('data-category');
            document.querySelectorAll('.cafe-item-wrapper').forEach(item => {
                if (cat === 'all') {
                    item.style.display = '';
                } else {
                    const itemCat = item.getAttribute('data-category') || '';
                    item.style.display = itemCat.toLowerCase().includes(cat.toLowerCase()) ? '' : 'none';
                }
            });
        });
    });

    document.querySelectorAll('.btn-add-cafe').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const satuanId = this.getAttribute('data-satuan-id');
            const satuan = this.getAttribute('data-satuan');
            const stok = Number(this.getAttribute('data-stok') || 0);
            const harga = Number(this.getAttribute('data-harga') || 0);

            if (orderState.cafeItems[id]) {
                if (orderState.cafeItems[id].qty + 1 > stok) {
                    alert(`Stok tidak mencukupi! Maksimal stok tersedia adalah ${stok} ${satuan}.`);
                    return;
                }
                orderState.cafeItems[id].qty += 1;
            } else {
                if (stok < 1) {
                    alert('Stok barang ini telah habis.');
                    return;
                }
                orderState.cafeItems[id] = {
                    barang_id: id,
                    nama: nama,
                    satuan_barang_id: satuanId,
                    satuan: satuan,
                    qty: 1,
                    harga_satuan: harga,
                    stok: stok
                };
            }

            renderCart();
        });
    });

    // -------------------------------------------------------------
    // RENDER ORDER CART & CALCULATE TOTALS
    // -------------------------------------------------------------
    function renderCart() {
        const cartEmpty = document.getElementById('cartEmptyPlaceholder');
        const cartFilm = document.getElementById('cartItemFilm');
        const cartRoom = document.getElementById('cartItemRoom');
        const cafeContainer = document.getElementById('cartCafeContainer');

        let subtotalFilm = 0;
        let subtotalCafe = 0;

        // Film
        if (orderState.film) {
            cartFilm.style.setProperty('display', 'flex', 'important');
            document.getElementById('cartFilmJudul').textContent = orderState.film.judul;
            document.getElementById('cartFilmDurasi').textContent = `${orderState.film.durasi} Menit`;
            document.getElementById('cartFilmHarga').textContent = formatRupiah(orderState.film.harga);
            subtotalFilm = orderState.film.harga;
        } else {
            cartFilm.style.setProperty('display', 'none', 'important');
        }

        // Room
        if (orderState.room) {
            cartRoom.style.setProperty('display', 'flex', 'important');
            document.getElementById('cartRoomNama').textContent = orderState.room.nama;
            document.getElementById('cartRoomKapasitas').textContent = `Kapasitas: ${orderState.room.kapasitas} Orang`;
        } else {
            cartRoom.style.setProperty('display', 'none', 'important');
        }

        // Cafe items
        if (cafeContainer) {
            cafeContainer.innerHTML = '';
            const items = Object.values(orderState.cafeItems);
            items.forEach(item => {
                const lineTotal = item.qty * item.harga_satuan;
                subtotalCafe += lineTotal;

                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center mb-2 pb-2 border-bottom';
                div.innerHTML = `
                    <div class="me-2 overflow-hidden" style="max-width: 60%;">
                        <div class="fw-bold text-dark text-truncate small">${item.nama}</div>
                        <div class="small text-muted font-monospace">${item.qty} ${item.satuan} x ${formatRupiah(item.harga_satuan)}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="fw-bold font-monospace text-emerald small">${formatRupiah(lineTotal)}</div>
                        <button type="button" class="btn btn-sm text-danger p-0 btn-remove-cart-item" data-id="${item.barang_id}" title="Hapus">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                `;
                cafeContainer.appendChild(div);
            });

            // Bind remove buttons
            cafeContainer.querySelectorAll('.btn-remove-cart-item').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    delete orderState.cafeItems[id];
                    renderCart();
                });
            });
        }

        const hasAnyItem = orderState.film || orderState.room || Object.keys(orderState.cafeItems).length > 0;
        if (cartEmpty) {
            cartEmpty.style.display = hasAnyItem ? 'none' : 'block';
        }

        // Hitung total
        const kotor = subtotalFilm + subtotalCafe;
        let diskon = 0;
        if (orderState.promo) {
            diskon = orderState.promo.diskon || 0;
            document.getElementById('rowDiskonPromo').style.display = 'flex';
            document.getElementById('labelDiskonPromo').textContent = `- ${formatRupiah(diskon)}`;
        } else {
            document.getElementById('rowDiskonPromo').style.display = 'none';
        }

        const totalBersih = Math.max(0, kotor - diskon);

        document.getElementById('labelSubtotalFilm').textContent = formatRupiah(subtotalFilm);
        document.getElementById('labelSubtotalCafe').textContent = formatRupiah(subtotalCafe);
        document.getElementById('labelTotalTagihan').textContent = formatRupiah(totalBersih);

        // Update Kembalian
        updateKembalian(totalBersih);
    }

    // -------------------------------------------------------------
    // PROMO VOUCHER CODE
    // -------------------------------------------------------------
    const btnTerapkanPromo = document.getElementById('btnTerapkanPromo');
    const inputKodePromo = document.getElementById('inputKodePromo');
    const promoFeedbackMsg = document.getElementById('promoFeedbackMsg');

    if (btnTerapkanPromo) {
        btnTerapkanPromo.addEventListener('click', function () {
            const kode = inputKodePromo.value.trim();
            if (!kode) {
                alert('Silakan masukkan kode voucher terlebih dahulu.');
                return;
            }

            const subtotalKotor = (orderState.film ? orderState.film.harga : 0) + 
                Object.values(orderState.cafeItems).reduce((acc, curr) => acc + (curr.qty * curr.harga_satuan), 0);

            if (subtotalKotor <= 0) {
                alert('Silakan pilih film atau menu cafe terlebih dahulu sebelum menggunakan voucher.');
                return;
            }

            fetch('{{ route("kasir.cek-promo") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kode_voucher: kode, subtotal: subtotalKotor })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    orderState.promo = {
                        id: data.promo_id,
                        nama: data.nama_promo,
                        diskon: data.diskon
                    };
                    promoFeedbackMsg.className = 'small text-success mt-1 fw-semibold';
                    promoFeedbackMsg.textContent = `Promo aktif: ${data.nama_promo} (Hemat ${formatRupiah(data.diskon)})`;
                    renderCart();
                } else {
                    orderState.promo = null;
                    promoFeedbackMsg.className = 'small text-danger mt-1 fw-semibold';
                    promoFeedbackMsg.textContent = data.message || 'Voucher tidak valid.';
                    renderCart();
                }
            })
            .catch(() => {
                alert('Gagal memverifikasi kode promo. Silakan coba lagi.');
            });
        });
    }

    // -------------------------------------------------------------
    // CASH & KEMBALIAN CALCULATION
    // -------------------------------------------------------------
    const inputUangDiterima = document.getElementById('inputUangDiterima');
    const labelKembalian = document.getElementById('labelKembalian');
    const selectTipePembayaran = document.getElementById('selectTipePembayaran');
    const sectionCashPayment = document.getElementById('sectionCashPayment');

    function getTotalTagihanAngka() {
        const subtotalFilm = orderState.film ? orderState.film.harga : 0;
        const subtotalCafe = Object.values(orderState.cafeItems).reduce((acc, curr) => acc + (curr.qty * curr.harga_satuan), 0);
        const diskon = orderState.promo ? orderState.promo.diskon : 0;
        return Math.max(0, subtotalFilm + subtotalCafe - diskon);
    }

    function updateKembalian(totalTagihan) {
        if (!inputUangDiterima || !labelKembalian) return;
        const diterima = Number(inputUangDiterima.value || 0);
        const tagihan = typeof totalTagihan !== 'undefined' ? totalTagihan : getTotalTagihanAngka();
        const sisa = diterima - tagihan;

        if (sisa >= 0) {
            labelKembalian.className = 'fs-5 fw-bold font-monospace text-emerald';
            labelKembalian.textContent = formatRupiah(sisa);
        } else {
            labelKembalian.className = 'fs-5 fw-bold font-monospace text-danger';
            labelKembalian.textContent = 'Kurang ' + formatRupiah(Math.abs(sisa));
        }
    }

    if (inputUangDiterima) {
        inputUangDiterima.addEventListener('input', function () {
            updateKembalian();
        });
    }

    document.querySelectorAll('.btn-chip-cash').forEach(btn => {
        btn.addEventListener('click', function () {
            const type = this.getAttribute('data-type');
            const total = getTotalTagihanAngka();
            if (type === 'exact') {
                inputUangDiterima.value = total;
            } else {
                inputUangDiterima.value = this.getAttribute('data-val');
            }
            updateKembalian(total);
        });
    });

    if (selectTipePembayaran) {
        selectTipePembayaran.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            const metode = selectedOpt ? selectedOpt.getAttribute('data-metode') : '';
            const isCash = metode.includes('tunai') || metode.includes('cash');
            if (sectionCashPayment) {
                sectionCashPayment.style.display = isCash ? 'block' : 'none';
            }
        });
    }

    // -------------------------------------------------------------
    // RESET TRANSAKSI
    // -------------------------------------------------------------
    function resetPOS() {
        orderState.film = null;
        orderState.room = null;
        orderState.cafeItems = {};
        orderState.promo = null;
        if (inputUangDiterima) inputUangDiterima.value = '';
        if (inputKodePromo) inputKodePromo.value = '';
        if (promoFeedbackMsg) promoFeedbackMsg.textContent = '';
        document.querySelectorAll('.movie-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.room-available').forEach(c => c.classList.remove('selected'));
        const indicator = document.getElementById('selectedFilmBadgeIndicator');
        if (indicator) indicator.innerHTML = 'Film: <strong class="text-dark">Belum dipilih</strong>';
        renderCart();
    }

    document.querySelectorAll('.btn-reset-pos').forEach(btn => {
        btn.addEventListener('click', function () {
            if (confirm('Reset formulir transaksi untuk memulai pesanan baru?')) {
                resetPOS();
            }
        });
    });

    // -------------------------------------------------------------
    // CHECKOUT & BAYAR & CETAK STRUK
    // -------------------------------------------------------------
    const btnCheckoutBayar = document.getElementById('btnCheckoutBayar');
    if (btnCheckoutBayar) {
        btnCheckoutBayar.addEventListener('click', function () {
            if (!orderState.film) {
                alert('Silakan pilih Film studio terlebih dahulu pada Step 1!');
                return;
            }
            if (!orderState.room) {
                alert('Silakan pilih Room studio yang tersedia pada Step 2!');
                return;
            }

            const totalTagihan = getTotalTagihanAngka();
            const selectedOpt = selectTipePembayaran.options[selectTipePembayaran.selectedIndex];
            const tipePembayaranId = selectedOpt ? selectedOpt.value : null;
            const metode = selectedOpt ? selectedOpt.getAttribute('data-metode') : '';
            const isCash = metode.includes('tunai') || metode.includes('cash');

            let uangDiterima = Number(inputUangDiterima.value || 0);
            if (isCash) {
                if (uangDiterima < totalTagihan) {
                    alert(`Uang tunai yang diterima kurang! Total tagihan adalah ${formatRupiah(totalTagihan)}.`);
                    return;
                }
            } else {
                uangDiterima = totalTagihan;
            }

            const payload = {
                film_id: orderState.film.id,
                room_id: orderState.room.id,
                tipe_pembayaran_id: tipePembayaranId,
                nama_pelanggan: document.getElementById('inputNamaPelanggan')?.value || 'Pelanggan Umum',
                promo_id: orderState.promo ? orderState.promo.id : null,
                uang_diterima: uangDiterima,
                cafe_items: Object.values(orderState.cafeItems).map(item => ({
                    barang_id: item.barang_id,
                    satuan_barang_id: item.satuan_barang_id,
                    qty: item.qty,
                    harga_satuan: item.harga_satuan
                }))
            };

            btnCheckoutBayar.disabled = true;
            btnCheckoutBayar.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses Pembayaran...`;

            fetch('{{ route("kasir.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btnCheckoutBayar.disabled = false;
                btnCheckoutBayar.innerHTML = `<i class="bi bi-printer-fill fs-5"></i><span>Bayar & Cetak Struk</span>`;

                if (data.status === 'success') {
                    // Buka struk thermal dalam popup window untuk langsung cetak
                    const strukUrl = `{{ url('/kasir/struk') }}/${data.transaksi_id}`;
                    const printWindow = window.open(strukUrl, '_blank', 'width=420,height=600,scrollbars=yes');
                    if (!printWindow) {
                        window.location.href = strukUrl;
                    }

                    alert(`Transaksi berhasil! Nomor Nota: ${data.nomor_transaksi}. Struk sedang dicetak.`);
                    resetPOS();
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan saat memproses transaksi.');
                }
            })
            .catch(err => {
                btnCheckoutBayar.disabled = false;
                btnCheckoutBayar.innerHTML = `<i class="bi bi-printer-fill fs-5"></i><span>Bayar & Cetak Struk</span>`;
                alert('Gagal memproses checkout pembayaran. Silakan periksa koneksi atau coba lagi.');
            });
        });
    }

});
</script>
@endpush
