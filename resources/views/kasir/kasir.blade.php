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
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
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

                            @if(auth()->user()->toko_cabang_id && auth()->user()->tokoCabang)
                                <!-- Outlet Cabang Terkunci Sesuai Penugasan Akun User -->
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Outlet Penugasan Kasir</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-success"><i class="bi bi-shop"></i></span>
                                        <input type="text" class="form-control bg-light border-start-0 fw-bold text-dark" 
                                               value="{{ auth()->user()->tokoCabang->nama_cabang }} - {{ auth()->user()->tokoCabang->alamat }}" readonly>
                                        <input type="hidden" name="toko_cabang_id" value="{{ auth()->user()->toko_cabang_id }}">
                                    </div>
                                    <div class="form-text text-muted small mt-1">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>Outlet otomatis ditetapkan sesuai penempatan akun kasir Anda.
                                    </div>
                                </div>
                            @else
                                <!-- Pilih Outlet Cabang (Jika belum ditentukan di pengaturan user) -->
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
                            @endif

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
        <!-- Shift Top Bar Status (Invenza Clean Style) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white pos-header-bar">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-emerald-subtle text-emerald rounded-4 d-flex align-items-center justify-content-center">
                            <i class="bi bi-calculator fs-2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
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

                    <div class="d-flex flex-wrap align-items-start gap-2">
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

        <!-- POS Nav Tabs / Stepper (Responsif & Cepat Beralih) -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white p-2">
            <div class="nav nav-pills pos-nav-pills gap-2 flex-nowrap overflow-x-auto" id="posStepTabs" role="tablist">
                <button class="nav-link active rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 text-nowrap" id="tab-film-btn" data-bs-toggle="pill" data-bs-target="#tab-film-pane" type="button" role="tab">
                    <i class="bi bi-film"></i>
                    <span>Pilih Film Studio</span>
                    <span class="badge bg-emerald text-white rounded-circle ms-1 d-none" id="filmStepChecked"><i class="bi bi-check"></i></span>
                </button>
                <button class="nav-link rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 text-nowrap" id="tab-room-btn" data-bs-toggle="pill" data-bs-target="#tab-room-pane" type="button" role="tab">
                    <i class="bi bi-door-open-fill"></i>
                    <span>Pilih Room Studio Tersedia</span>
                    <span class="badge bg-emerald text-white rounded-circle ms-1 d-none" id="roomStepChecked"><i class="bi bi-check"></i></span>
                </button>
                <button class="nav-link rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 text-nowrap" id="tab-cafe-btn" data-bs-toggle="pill" data-bs-target="#tab-cafe-pane" type="button" role="tab">
                    <i class="bi bi-cup-hot-fill"></i>
                    <span>Pesan Makanan & Minuman Cafe</span>
                    <span class="badge bg-warning text-dark rounded-pill ms-1 d-none font-monospace" id="cafeCountBadge">0</span>
                </button>
            </div>
        </div>

        <!-- POS Workspace: Split Responsif (Katalog Kiri & Current Cart Kanan Sesuai sidebar/kasir.png) -->
        <div class="row g-4">
            
            <!-- KOLOM KIRI: WORKFLOW KATALOG (FILM, ROOM, CAFE) -->
            <div class="col-12 col-lg-7 col-xl-8">
                <div class="tab-content" id="posTabContent">

                    <!-- =================================================================== -->
                    <!-- TAB 1: PILIH FILM STUDIO (MIRIP PERSIS SIDEBAR/KASIR.PNG)           -->
                    <!-- =================================================================== -->
                    <div class="tab-pane fade show active" id="tab-film-pane" role="tabpanel" tabindex="0">
                        
                        <!-- Search & Category Filter Bar (Mirip Bar Atas di kasir.png) -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 pos-filter-bar">
                            <div class="row g-2 align-items-center">
                                <!-- Search Input Barcode / Name / SKU -->
                                <div class="col-12 col-md-7 col-xl-8">
                                    <div class="input-group pos-search-input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchMovieInput" 
                                               class="form-control border-start-0 border-end-0 py-2.5 ps-2 text-dark" 
                                               placeholder="Scan barcode or type product name / SKU..." 
                                               autocomplete="off">
                                        <span class="input-group-text bg-white border-start-0 pe-3">
                                            <kbd class="bg-light text-muted border px-2 py-0.5 small rounded font-monospace" title="Tekan '/' untuk fokus pencarian">/</kbd>
                                        </span>
                                    </div>
                                </div>
                                <!-- All Categories Dropdown (Sesuai kasir.png) -->
                                <div class="col-12 col-md-5 col-xl-4">
                                    <div class="pos-category-select-wrapper">
                                        <select id="selectGenreFilter" class="form-select py-2.5 rounded-3 fw-semibold text-dark border-light-subtle">
                                            <option value="all">All Categories</option>
                                            @foreach($genres as $genre)
                                                <option value="{{ $genre->nama_genre }}">{{ $genre->nama_genre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grid Card Film (Mirip Persis Kartu Produk Invenza di sidebar/kasir.png) -->
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Daftar Film</h5>
                                    <small class="text-muted">Klik salah satu film untuk memilih dan menambahkan ke keranjang pesanan.</small>
                                </div>
                                <span class="badge bg-light text-muted border font-monospace" id="movieCounterBadge">{{ $films->count() }} Film</span>
                            </div>

                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-3 g-3" id="movieGrid">
                                @forelse($films as $film)
                                    @php
                                        $genreNames = $film->genres->pluck('nama_genre')->implode(' ');
                                        $price = (float) $film->total_harga > 0 ? (float) $film->total_harga : ((float) $film->harga_per_menit * (int) $film->durasi_menit);
                                        $sku = 'FM-' . sprintf('%03d', $film->id);
                                    @endphp
                                    <div class="col movie-card-wrapper" data-genre="{{ $genreNames }}" data-title="{{ strtolower($film->judul) }}" data-sku="{{ strtolower($sku) }}">
                                        <div class="pos-product-card card h-100 bg-white rounded-4 p-3 transition-all cursor-pointer movie-card position-relative" 
                                             data-id="{{ $film->id }}"
                                             data-judul="{{ $film->judul }}"
                                             data-durasi="{{ $film->durasi_menit }}"
                                             data-harga="{{ $price }}"
                                             data-poster="{{ $film->poster_url }}"
                                             data-sku="{{ $sku }}">
                                            
                                            <!-- Thumbnail Container (Square dengan background halus mirip kasir.png) -->
                                            <div class="pos-thumb-box rounded-3 overflow-hidden position-relative mb-2">
                                                <img src="{{ $film->poster_url }}" alt="{{ $film->judul }}" class="pos-thumb-img" loading="lazy">
                                                
                                                <!-- Selected Badge Checkmark (Muncul bila kartu terpilih) -->
                                                <div class="pos-selected-badge position-absolute top-0 end-0 m-2 bg-emerald text-white rounded-circle shadow-sm">
                                                    <i class="bi bi-check-lg"></i>
                                                </div>

                                                <!-- Rating Usia Tag -->
                                                <span class="badge bg-dark text-white position-absolute bottom-0 start-0 m-2 small font-monospace py-0.5 px-1.5" style="font-size: 0.65rem; opacity: 0.85;">
                                                    {{ $film->usia_rating }}
                                                </span>
                                            </div>

                                            <!-- Product Title (Tebal & Rapi seperti Mechanical Keyboard di kasir.png) -->
                                            <h6 class="fw-bold text-dark text-center mb-1 pos-product-title text-truncate" title="{{ $film->judul }}">
                                                {{ $film->judul }}
                                            </h6>

                                            <!-- Subtitle SKU & Durasi (Teks abu-abu kecil di tengah) -->
                                            <div class="text-muted text-center small mb-2 font-monospace pos-product-sku">
                                                {{ $sku }}
                                            </div>

                                            <!-- Price & Status Row (Harga hijau emerald di kiri, Durasi di kanan) -->
                                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                                <span class="fw-bold text-emerald font-monospace fs-6">
                                                    Rp {{ number_format($price, 0, ',', '.') }}
                                                </span>
                                                <span class="text-muted small" style="font-size: 0.78rem;">
                                                    <i class="bi bi-clock me-0.5"></i>{{ $film->durasi_menit }}m
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-5 text-center text-muted">
                                        <i class="bi bi-film fs-1 mb-2 d-block opacity-50"></i>
                                        Belum ada film yang tersedia di database.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <!-- =================================================================== -->
                    <!-- TAB 2: PILIH ROOM STUDIO TERSEDIA                                   -->
                    <!-- =================================================================== -->
                    <div class="tab-pane fade" id="tab-room-pane" role="tabpanel" tabindex="0">
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold text-heading mb-0">
                                            Pilih Room
                                        </h5>
                                    </div>
                                    <p class="text-muted small mb-0">Pilih studio room yang kosong dan siap digunakan pada cabang ini.</p>
                                </div>
                                <span class="badge bg-light text-muted border px-3 py-1.5 rounded-3" id="selectedFilmBadgeIndicator">
                                    Film: <strong class="text-dark">Belum dipilih</strong>
                                </span>
                            </div>

                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3" id="roomGrid">
                                @forelse($rooms as $room)
                                    @php
                                        $isOccupied = in_array($room->id, $occupiedRoomIds);
                                    @endphp
                                    <div class="col">
                                        <div class="pos-room-card card border rounded-4 p-3 h-100 position-relative transition-all {{ $isOccupied ? 'room-occupied bg-light opacity-75' : 'room-available cursor-pointer bg-white' }}"
                                             data-id="{{ $room->id }}"
                                             data-nama="{{ $room->nama }}"
                                             data-kapasitas="{{ $room->kapasitas }}"
                                             data-occupied="{{ $isOccupied ? '1' : '0' }}">
                                            
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="stat-icon-wrapper rounded-3 {{ $isOccupied ? 'bg-secondary-subtle text-secondary' : 'bg-emerald-subtle text-emerald' }} d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
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

                                            <!-- Selection Checkmark -->
                                            <div class="pos-selected-badge position-absolute top-0 end-0 m-2 bg-emerald text-white rounded-circle shadow-sm">
                                                <i class="bi bi-check-lg"></i>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-4 text-center text-muted">
                                        <i class="bi bi-door-closed fs-1 mb-2 d-block opacity-50"></i>
                                        Belum ada master room studio yang terdaftar pada cabang ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- =================================================================== -->
                    <!-- TAB 3: PESAN MAKANAN & MINUMAN CAFE (OPSIONAL)                      -->
                    <!-- =================================================================== -->
                    <div class="tab-pane fade" id="tab-cafe-pane" role="tabpanel" tabindex="0">
                        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold text-heading mb-0">
                                            Pesan Makanan & Minuman
                                        </h5>
                                    </div>
                                    <p class="text-muted small mb-0">Pilih menu cafe atau snack jika pelanggan memesan.</p>
                                </div>
                                <div style="min-width: 220px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchCafeInput" class="form-control border-start-0 rounded-end-3" placeholder="Cari menu cafe...">
                                    </div>
                                </div>
                            </div>

                            <!-- Category Pills -->
                            <div class="py-2 border-top border-bottom mb-3 d-flex gap-2 overflow-x-auto">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill active btn-cafe-cat" data-category="all">Semua Menu</button>
                                @foreach($kategoriCafes as $kategori)
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-cafe-cat text-nowrap" data-category="{{ $kategori->nama }}">
                                        {{ $kategori->nama }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Cafe Items Grid (Mirip Kartu Invenza) -->
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-4 g-3" id="cafeGrid">
                                @forelse($cafeItems as $cItem)
                                    @php
                                        $stokTersedia = (float) $cItem->stok;
                                        $hargaJual = (float) $cItem->harga_jual;
                                    @endphp
                                    <div class="col cafe-item-wrapper" data-category="{{ $cItem->nama_kategori }}" data-name="{{ strtolower($cItem->nama_barang) }}">
                                        <div class="pos-product-card card h-100 bg-white rounded-4 p-3 transition-all cafe-card border position-relative">
                                            <div class="pos-thumb-box rounded-3 overflow-hidden position-relative mb-2 bg-light d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                                                <i class="bi bi-cup-straw fs-1 text-emerald opacity-75"></i>
                                                <span class="badge bg-light text-muted border position-absolute top-0 start-0 m-2 small">
                                                    {{ $cItem->nama_kategori ?? 'Umum' }}
                                                </span>
                                            </div>

                                            <h6 class="fw-bold text-dark text-center mb-1 text-truncate" title="{{ $cItem->nama_barang }}">
                                                {{ $cItem->nama_barang }}
                                            </h6>
                                            <div class="text-muted text-center small mb-2 font-monospace">
                                                Qty: {{ (int)$stokTersedia == $stokTersedia ? (int)$stokTersedia : $stokTersedia }} {{ $cItem->nama_satuan }}
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                                <span class="fw-bold text-emerald font-monospace fs-6">
                                                    Rp {{ number_format($hargaJual, 0, ',', '.') }}
                                                </span>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-emerald rounded-3 py-1 px-2.5 btn-add-cafe"
                                                        data-id="{{ $cItem->barang_id }}"
                                                        data-nama="{{ $cItem->nama_barang }}"
                                                        data-satuan-id="{{ $cItem->satuan_barang_id }}"
                                                        data-satuan="{{ $cItem->nama_satuan }}"
                                                        data-stok="{{ $stokTersedia }}"
                                                        data-harga="{{ $hargaJual }}">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-4 text-center text-muted">
                                        <i class="bi bi-cup-straw fs-1 mb-2 d-block opacity-50"></i>
                                        Belum ada stok menu cafe di cabang ini. Silakan input faktur barang terlebih dahulu.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- =================================================================== -->
            <!-- KOLOM KANAN: CURRENT CART & CHECKOUT (MIRIP PERSIS SIDEBAR/KASIR.PNG)-->
            <!-- =================================================================== -->
            <div class="col-12 col-lg-5 col-xl-4" id="checkoutPanelContainer">
                <div class="card border-0 shadow-sm rounded-4 sticky-top pos-cart-panel bg-white p-4" style="top: 85px;">

                    <!-- Input Nama Pelanggan -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Pelanggan (Opsional)</label>
                        <input type="text" id="inputNamaPelanggan" class="form-control rounded-3" placeholder="Pelanggan Umum" value="Pelanggan Umum">
                    </div>

                    <!-- Input Jumlah Penonton dalam Room -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-muted mb-0">
                                <i class="bi bi-people-fill text-primary me-1"></i>Jumlah Penonton dalam Room <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">
                                Standar: 2 Org
                            </span>
                        </div>
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-secondary px-3" id="btnMinusPenonton" title="Kurangi Penonton">-</button>
                            <input type="number" id="inputJumlahPenonton" class="form-control text-center fw-bold font-monospace fs-6" value="2" min="1" max="50">
                            <button type="button" class="btn btn-outline-secondary px-3" id="btnPlusPenonton" title="Tambah Penonton">+</button>
                        </div>
                        <div class="small mt-1 text-muted" id="infoDendaPenonton" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle text-primary me-1"></i>Maksimal 2 orang gratis denda. Lebih dari 2 orang dikenakan biaya tambahan Rp 5.000 / orang.
                        </div>
                    </div>

                    <!-- CURRENT CART HEADER (Sesuai sidebar/kasir.png) -->
                    <div class="d-flex justify-content-between align-items-center mb-3 pt-2 border-top">
                        <h6 class="fw-bold text-dark mb-0 fs-6">Current Cart</h6>
                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none small fw-semibold btn-reset-pos" title="Kosongkan Keranjang">
                            Clear All
                        </button>
                    </div>

                    <!-- CART ITEMS LIST / EMPTY STATE (Sesuai sidebar/kasir.png) -->
                    <div class="pos-cart-items-box border rounded-3 p-3 mb-3 bg-light-subtle">
                        
                        <!-- Item 1: Tiket Film -->
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" id="cartItemFilm" style="display: none !important;">
                            <div class="me-2 overflow-hidden" style="max-width: 60%;">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-danger text-white py-0.5 px-1.5 font-monospace" style="font-size: 0.6rem;">FILM</span>
                                    <div class="fw-bold text-dark text-truncate small" id="cartFilmJudul">-</div>
                                </div>
                                <div class="small text-muted font-monospace" id="cartFilmDurasi">-</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="fw-bold font-monospace text-emerald text-end small" id="cartFilmHarga">Rp 0</div>
                                <button type="button" class="btn btn-sm text-danger p-0" id="btnRemoveCartFilm" title="Hapus Film">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Item 2: Room Studio -->
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" id="cartItemRoom" style="display: none !important;">
                            <div class="me-2 overflow-hidden" style="max-width: 60%;">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-emerald text-white py-0.5 px-1.5 font-monospace" style="font-size: 0.6rem;">ROOM</span>
                                    <div class="fw-bold text-dark text-truncate small" id="cartRoomNama">-</div>
                                </div>
                                <div class="small text-muted font-monospace" id="cartRoomKapasitas">-</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="fw-semibold font-monospace text-muted text-end small">Included</div>
                                <button type="button" class="btn btn-sm text-danger p-0" id="btnRemoveCartRoom" title="Hapus Room">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Item 3: Cafe & Snack Container -->
                        <div id="cartCafeContainer">
                            <!-- Injected dynamically by JS -->
                        </div>

                        <!-- Placeholder Kosong (Ikon Keranjang Minimalist Mirip kasir.png) -->
                        <div id="cartEmptyPlaceholder" class="text-center py-4">
                            <div class="pos-cart-empty-icon mx-auto mb-2 text-muted">
                                <i class="bi bi-cart3 fs-2 opacity-50"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1 small">Cart is empty</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Click on products to add them</p>
                        </div>
                    </div>

                    <!-- RINGKASAN HARGA (Subtotal, Discount, Tax, Payable Total Sesuai kasir.png) -->
                    <div class="pos-cart-summary mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-medium small">Subtotal</span>
                            <span class="fw-bold text-dark font-monospace" id="labelSubtotal">Rp 0</span>
                        </div>

                        <!-- Biaya Tambahan Penonton (>2 orang) -->
                        <div class="d-flex justify-content-between align-items-center mb-2 text-danger small" id="rowDendaPenonton" style="display: none;">
                            <span>
                                <i class="bi bi-person-plus-fill me-1"></i>Tambahan Penonton (<span id="labelExtraPenonton">0</span> org x Rp 5.000)
                            </span>
                            <span class="font-monospace fw-bold" id="labelDendaPenonton">+ Rp 0</span>
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

                        <div class="d-flex justify-content-between align-items-center mb-1 text-danger small" id="rowDiscountAmount" style="display: none;">
                            <span>Diskon</span>
                            <span class="font-monospace fw-bold" id="labelDiscountAmount">-Rp 0</span>
                        </div>

                        <!-- Hidden label for backwards compatibility with tests -->
                        <span id="labelSubtotalFilm" style="display: none;">Rp 0</span>
                        <span id="labelSubtotalCafe" style="display: none;">Rp 0</span>
                        <span id="labelDiskonPromo" style="display: none;">- Rp 0</span>
                        <span id="labelTotalTagihan" style="display: none;">Rp 0</span>

                        <!-- PAYABLE TOTAL (Tulisan Besar Hijau Emerald Sesuai kasir.png) -->
                        <div class="d-flex justify-content-between align-items-center py-2.5 border-top border-bottom mb-3">
                            <span class="fw-bold text-dark fs-6">Total</span>
                            <span class="fw-bold text-emerald font-monospace fs-3" id="labelPayableTotal">Rp 0</span>
                        </div>
                    </div>

                    <!-- QUICK CASH DENOMINATIONS ($10, $20, $50, $100 -> Rp 50k, 100k, 200k, Pas Sesuai kasir.png) -->
                    <div class="pos-quick-cash-chips d-flex gap-2 mb-2.5">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill rounded-3 py-1 font-monospace small btn-chip-cash" data-type="exact">Pas</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill rounded-3 py-1 font-monospace small btn-chip-cash" data-val="50000">Rp 50rb</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill rounded-3 py-1 font-monospace small btn-chip-cash" data-val="100000">Rp 100rb</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill rounded-3 py-1 font-monospace small btn-chip-cash" data-val="200000">Rp 200rb</button>
                    </div>

                    <!-- TENDERED CASH & CHANGE DUE (Side-by-side Sesuai kasir.png) -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="text-uppercase text-muted fw-bold mb-1 pos-micro-label">Pembayaran</label>
                            <input type="text" inputmode="numeric" id="inputUangDiterima" class="form-control font-monospace fw-bold fs-6 text-end" placeholder="0" autocomplete="off">
                        </div>
                        <div class="col-5">
                            <label class="text-uppercase text-muted fw-bold mb-1 pos-micro-label">Kembalian</label>
                            <div class="pos-change-box rounded-3 p-2 text-center bg-emerald-subtle border border-emerald">
                                <span class="fw-bold text-emerald font-monospace fs-6" id="labelKembalian">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- METODE PEMBAYARAN (DATA MASTER) -->
                    <div class="mb-3">
                        <label class="form-label text-uppercase text-muted fw-bold small mb-1 pos-section-label">
                            Metode Pembayaran (Data Master) <span class="text-danger">*</span>
                        </label>
                        <select id="selectTipePembayaran" class="form-select rounded-3 fw-semibold text-dark" required>
                            @foreach($tipePembayarans as $tp)
                                <option value="{{ $tp->id }}" data-metode="{{ strtolower($tp->metode_pembayaran) }}">
                                    {{ $tp->metode_pembayaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- TOMBOL CHECKOUT / BAYAR & CETAK STRUK -->
                    <button type="button" class="btn btn-emerald w-100 py-3 rounded-3 shadow-sm fw-bold fs-6 d-flex align-items-center justify-content-center gap-2" id="btnCheckoutBayar">
                        <i class="bi bi-printer-fill fs-5"></i>
                        <span>Bayar & Cetak Struk</span>
                    </button>

                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- FLOATING BOTTOM SUMMARY BAR (KHUSUS MOBILE & TABLET KASIR < 992PX)         -->
        <!-- ========================================================================= -->
        <div class="d-lg-none fixed-bottom bg-white border-top shadow-lg p-3 pos-mobile-bottom-bar z-3">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="text-muted small d-block" id="mobileCartItemCount">0 Item Terpilih</span>
                    <strong class="text-emerald font-monospace fs-5" id="mobilePayableTotal">Rp 0</strong>
                </div>
                <button type="button" class="btn btn-emerald px-4 py-2.5 rounded-3 fw-bold d-flex align-items-center gap-2 shadow-sm" id="btnScrollToCart">
                    <i class="bi bi-cart3"></i>
                    <span>Lihat Pesanan & Bayar</span>
                </button>
            </div>
        </div>

        <div class="modal fade" id="modalKonfirmasiPembayaran" tabindex="-1" aria-labelledby="modalKonfirmasiPembayaranLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="modalKonfirmasiPembayaranLabel">
                            <i class="bi bi-receipt-cutoff text-emerald me-2"></i>Konfirmasi Pesanan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-between gap-3 mb-3 small">
                            <span class="text-muted">Nama pelanggan</span>
                            <strong class="text-dark text-end" id="konfirmasiNamaPelanggan">-</strong>
                        </div>
                        <div class="d-flex justify-content-between gap-3 mb-3 small">
                            <span class="text-muted">Jumlah penonton</span>
                            <strong class="text-dark text-end"><span id="konfirmasiJumlahPenonton">2</span> orang</strong>
                        </div>
                        <div class="table-responsive border rounded-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Pesanan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end pe-3">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody id="konfirmasiDaftarPesanan"></tbody>
                            </table>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Subtotal item</span>
                                <span class="font-monospace" id="konfirmasiSubtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2 text-danger d-none" id="konfirmasiBarisDenda">
                                <span>Tambahan penonton (<span id="konfirmasiJumlahDenda">0</span> orang)</span>
                                <span class="font-monospace" id="konfirmasiDenda">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2 text-success d-none" id="konfirmasiBarisDiskon">
                                <span>Diskon promo <span id="konfirmasiNamaPromo"></span></span>
                                <span class="font-monospace" id="konfirmasiDiskon">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 pt-2 mt-2 border-top">
                                <span>Total</span>
                                <span class="font-monospace text-emerald" id="konfirmasiTotal">Rp 0</span>
                            </div>
                        </div>
                        <div class="bg-light rounded-3 p-3 mt-3 small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Metode pembayaran</span>
                                <strong id="konfirmasiMetodePembayaran">-</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Uang diterima</span>
                                <strong class="font-monospace" id="konfirmasiUangDiterima">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Kembalian</span>
                                <strong class="font-monospace" id="konfirmasiKembalian">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Periksa Kembali</button>
                        <button type="button" class="btn btn-emerald rounded-3 px-4 fw-bold" id="btnKonfirmasiPembayaran">
                            <i class="bi bi-printer-fill me-1"></i>Konfirmasi Bayar & Cetak
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
                                <label class="form-label small fw-semibold text-muted">Jumlah Uang Fisik (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="any" min="0" name="modal_akhir" class="form-control font-monospace fs-5 fw-bold text-end" value="{{ $activeShift->modal_awal + $activeShift->total_tunai }}" required>
                                <div class="form-text small">Hitung dan masukkan total uang fisik yang ada di laci kasir saat ini.</div>
                            </div>

                            <!-- Catatan Penutupan -->
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-muted">Catatan (Opsional)</label>
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
    /* Emerald Theme Palette & Typography */
    .bg-gradient-emerald {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .btn-emerald {
        background: #10b981;
        color: #ffffff;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-emerald:hover, .btn-emerald:focus {
        background: #059669;
        color: #ffffff;
    }
    .btn-outline-emerald {
        border: 1px solid #10b981;
        color: #10b981;
        background: transparent;
    }
    .btn-outline-emerald:hover {
        background: #10b981;
        color: #ffffff;
    }
    .text-emerald {
        color: #10b981 !important;
    }
    .border-emerald {
        border-color: #10b981 !important;
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
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* POS Nav Stepper Pills */
    .pos-nav-pills .nav-link {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .pos-nav-pills .nav-link:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .pos-nav-pills .nav-link.active {
        background: #ffffff;
        color: #0f172a;
        border-color: #10b981;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.15);
    }

    /* POS Filter & Search Bar (Sesuai kasir.png) */
    .pos-search-input-group {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pos-search-input-group:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
    }
    .pos-genre-pill-scroll {
        scrollbar-width: thin;
    }
    .btn-genre-pill.active {
        background: #10b981 !important;
        border-color: #10b981 !important;
        color: #ffffff !important;
    }

    /* =========================================================================
       PRODUCT CARD (SESUAI PERSIS DENGAN SIDEBAR/KASIR.PNG)
       - Background putih bersih
       - Thumbnail aspect ratio 1:1 rounded
       - Judul tebal & SKU kecil di tengah
       - Harga hijau emerald tebal di kiri bawah
       - State Selected: Border hijau 2px (seperti 'Mechanical Keyboard' di kasir.png)
       ========================================================================= */
    .pos-product-card {
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pos-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }
    /* Active Selected State (Mirip persis kartu 'Mechanical Keyboard' pada kasir.png) */
    .pos-product-card.selected,
    .movie-card.selected {
        border: 2px solid #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18) !important;
    }
    .pos-thumb-box {
        aspect-ratio: 1 / 1;
        width: 100%;
        background-color: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pos-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .pos-product-card:hover .pos-thumb-img {
        transform: scale(1.04);
    }
    .pos-product-title {
        font-size: 0.92rem;
        line-height: 1.3;
    }
    .pos-product-sku {
        font-size: 0.76rem;
    }
    .pos-selected-badge {
        width: 24px;
        height: 24px;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    .pos-product-card.selected .pos-selected-badge,
    .pos-room-card.selected .pos-selected-badge {
        display: flex !important;
    }

    /* Room Cards */
    .pos-room-card {
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    .pos-room-card.room-available:hover {
        border-color: #10b981;
        background-color: #f0fdf4 !important;
        transform: translateY(-2px);
    }
    .pos-room-card.selected {
        border: 2px solid #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18) !important;
        background-color: #ecfdf5 !important;
    }

    /* POS Cart Panel (Right Column Sesuai sidebar/kasir.png) */
    .pos-section-label {
        font-size: 0.72rem;
        letter-spacing: 0.5px;
    }
    .pos-micro-label {
        font-size: 0.68rem;
        letter-spacing: 0.4px;
    }
    .pos-cart-items-box {
        max-height: 260px;
        overflow-y: auto;
    }
    .pos-cart-empty-icon {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background-color: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pos-change-box {
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cursor-pointer { cursor: pointer; }

    /* Responsiveness Tweaks Across Devices */
    @media (max-width: 991.98px) {
        body {
            padding-bottom: 80px; /* Space for mobile sticky bottom bar */
        }
        .pos-cart-panel {
            position: static !important;
        }
    }
    @media (max-width: 575.98px) {
        .pos-product-card {
            padding: 10px !important;
        }
        .pos-product-title {
            font-size: 0.82rem;
        }
        .pos-product-sku {
            font-size: 0.7rem;
        }
    }
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
        film: null,       // { id, judul, durasi, harga, poster, sku }
        room: null,       // { id, nama, kapasitas }
        jumlahPenonton: 2,
        dendaPenonton: 0,
        cafeItems: {},    // key: barang_id, value: { barang_id, nama, satuan_id, satuan, qty, harga_satuan, stok }
        promo: null,      // { id, nama, diskon }
        metodeBayarId: null,
        uangDiterima: 0
    };

    function formatRupiah(num, withPrefix = true) {
        const formatted = Number(num || 0).toLocaleString('id-ID');
        return withPrefix ? 'Rp ' + formatted : formatted;
    }

    function parseRupiah(value) {
        return Number(String(value || '').replace(/\D/g, '')) || 0;
    }

    // -------------------------------------------------------------
    // INPUT JUMLAH PENONTON & HITUNG DENDA
    // -------------------------------------------------------------
    const inputJumlahPenonton = document.getElementById('inputJumlahPenonton');
    const btnMinusPenonton = document.getElementById('btnMinusPenonton');
    const btnPlusPenonton = document.getElementById('btnPlusPenonton');

    function updatePenonton() {
        let val = parseInt(inputJumlahPenonton ? inputJumlahPenonton.value : 2, 10);
        if (isNaN(val) || val < 1) val = 1;
        if (inputJumlahPenonton) inputJumlahPenonton.value = val;
        orderState.jumlahPenonton = val;

        // Aturan: Penonton > 2 dikenakan denda Rp 5.000 / orang lebih
        if (val > 2) {
            orderState.dendaPenonton = (val - 2) * 5000;
        } else {
            orderState.dendaPenonton = 0;
        }

        renderCart();
    }

    if (btnMinusPenonton) {
        btnMinusPenonton.addEventListener('click', function () {
            let val = parseInt(inputJumlahPenonton ? inputJumlahPenonton.value : 2, 10) || 1;
            if (val > 1) {
                inputJumlahPenonton.value = val - 1;
                updatePenonton();
            }
        });
    }

    if (btnPlusPenonton) {
        btnPlusPenonton.addEventListener('click', function () {
            let val = parseInt(inputJumlahPenonton ? inputJumlahPenonton.value : 2, 10) || 1;
            inputJumlahPenonton.value = val + 1;
            updatePenonton();
        });
    }

    if (inputJumlahPenonton) {
        inputJumlahPenonton.addEventListener('input', updatePenonton);
        inputJumlahPenonton.addEventListener('change', updatePenonton);
    }

    // -------------------------------------------------------------
    // KEYBOARD SHORTCUT: TEKAN '/' UNTUK FOKUS SEARCH
    // -------------------------------------------------------------
    const searchMovieInput = document.getElementById('searchMovieInput');
    window.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement !== searchMovieInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            if (searchMovieInput) {
                searchMovieInput.focus();
                searchMovieInput.select();
            }
        }
    });

    // -------------------------------------------------------------
    // STEP 1: PENCARIAN & FILTER KATEGORI FILM (MIRIP KASIR.PNG)
    // -------------------------------------------------------------
    const selectGenreFilter = document.getElementById('selectGenreFilter');

    function applyMovieFilters() {
        const query = (searchMovieInput ? searchMovieInput.value : '').toLowerCase().trim();
        const genre = selectGenreFilter ? selectGenreFilter.value : 'all';

        let count = 0;
        document.querySelectorAll('.movie-card-wrapper').forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const sku = item.getAttribute('data-sku') || '';
            const genres = item.getAttribute('data-genre') || '';

            const matchQuery = !query || title.includes(query) || sku.includes(query);
            const matchGenre = genre === 'all' || genres.includes(genre);

            if (matchQuery && matchGenre) {
                item.style.display = '';
                count++;
            } else {
                item.style.display = 'none';
            }
        });

        const counterBadge = document.getElementById('movieCounterBadge');
        if (counterBadge) counterBadge.textContent = `${count} Film`;
    }

    if (searchMovieInput) {
        searchMovieInput.addEventListener('input', applyMovieFilters);
    }

    if (selectGenreFilter) {
        selectGenreFilter.addEventListener('change', function () {
            // Sinkronkan pill aktif
            document.querySelectorAll('.btn-genre-pill').forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-genre') === this.value);
            });
            applyMovieFilters();
        });
    }

    document.querySelectorAll('.btn-genre-pill').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-genre-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const genre = this.getAttribute('data-genre');
            if (selectGenreFilter) {
                selectGenreFilter.value = genre;
            }
            applyMovieFilters();
        });
    });

    // PILIH KARTU FILM
    document.querySelectorAll('.movie-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.movie-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            orderState.film = {
                id: this.getAttribute('data-id'),
                judul: this.getAttribute('data-judul'),
                durasi: this.getAttribute('data-durasi'),
                harga: Number(this.getAttribute('data-harga') || 0),
                poster: this.getAttribute('data-poster'),
                sku: this.getAttribute('data-sku')
            };

            const indicator = document.getElementById('selectedFilmBadgeIndicator');
            if (indicator) {
                indicator.innerHTML = `Film: <strong class="text-emerald">${orderState.film.judul} (${orderState.film.durasi}m)</strong>`;
            }

            const checkIcon = document.getElementById('filmStepChecked');
            if (checkIcon) checkIcon.classList.remove('d-none');

            renderCart();

            // Pindah tab secara cerdas ke Step 2 jika kasir belum memilih room
            if (!orderState.room) {
                const roomTabBtn = document.getElementById('tab-room-btn');
                if (roomTabBtn && typeof bootstrap !== 'undefined') {
                    const tab = new bootstrap.Tab(roomTabBtn);
                    tab.show();
                }
            }
        });
    });

    // -------------------------------------------------------------
    // STEP 2: PILIH ROOM STUDIO TERSEDIA
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

            const roomCheck = document.getElementById('roomStepChecked');
            if (roomCheck) roomCheck.classList.remove('d-none');

            renderCart();
        });
    });

    // -------------------------------------------------------------
    // STEP 3: PESAN MENU CAFE & SNACK
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
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
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



    // REMOVE FILM & ROOM DARI CART
    const btnRemoveCartFilm = document.getElementById('btnRemoveCartFilm');
    if (btnRemoveCartFilm) {
        btnRemoveCartFilm.addEventListener('click', function () {
            orderState.film = null;
            document.querySelectorAll('.movie-card').forEach(c => c.classList.remove('selected'));
            const checkIcon = document.getElementById('filmStepChecked');
            if (checkIcon) checkIcon.classList.add('d-none');
            const indicator = document.getElementById('selectedFilmBadgeIndicator');
            if (indicator) indicator.innerHTML = 'Film: <strong class="text-dark">Belum dipilih</strong>';
            renderCart();
        });
    }

    const btnRemoveCartRoom = document.getElementById('btnRemoveCartRoom');
    if (btnRemoveCartRoom) {
        btnRemoveCartRoom.addEventListener('click', function () {
            orderState.room = null;
            document.querySelectorAll('.pos-room-card').forEach(c => c.classList.remove('selected'));
            const roomCheck = document.getElementById('roomStepChecked');
            if (roomCheck) roomCheck.classList.add('d-none');
            renderCart();
        });
    }

    // -------------------------------------------------------------
    // RENDER KERANJANG BELANJA (CURRENT CART)
    // -------------------------------------------------------------
    function renderCart() {
        const cartEmpty = document.getElementById('cartEmptyPlaceholder');
        const cartFilm = document.getElementById('cartItemFilm');
        const cartRoom = document.getElementById('cartItemRoom');
        const cafeContainer = document.getElementById('cartCafeContainer');
        const cafeBadge = document.getElementById('cafeCountBadge');

        let subtotalFilm = 0;
        let subtotalCafe = 0;
        let totalItemsCount = 0;

        // Film
        if (orderState.film) {
            cartFilm.style.setProperty('display', 'flex', 'important');
            document.getElementById('cartFilmJudul').textContent = orderState.film.judul;
            document.getElementById('cartFilmDurasi').textContent = `${orderState.film.sku} • ${orderState.film.durasi}m`;
            document.getElementById('cartFilmHarga').textContent = formatRupiah(orderState.film.harga);
            subtotalFilm = orderState.film.harga;
            totalItemsCount += 1;
        } else {
            cartFilm.style.setProperty('display', 'none', 'important');
        }

        // Room
        if (orderState.room) {
            cartRoom.style.setProperty('display', 'flex', 'important');
            document.getElementById('cartRoomNama').textContent = orderState.room.nama;
            document.getElementById('cartRoomKapasitas').textContent = `Kapasitas: ${orderState.room.kapasitas} Org • ${orderState.jumlahPenonton} Penonton`;
            totalItemsCount += 1;
        } else {
            cartRoom.style.setProperty('display', 'none', 'important');
        }

        // Cafe items
        if (cafeContainer) {
            cafeContainer.innerHTML = '';
            const items = Object.values(orderState.cafeItems);
            let cafeQtyTotal = 0;

            items.forEach(item => {
                const lineTotal = item.qty * item.harga_satuan;
                subtotalCafe += lineTotal;
                cafeQtyTotal += item.qty;
                totalItemsCount += item.qty;

                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center mb-2 pb-2 border-bottom';
                div.innerHTML = `
                    <div class="me-2 overflow-hidden" style="max-width: 55%;">
                        <div class="fw-bold text-dark text-truncate small">${item.nama}</div>
                        <div class="small text-muted font-monospace">${formatRupiah(item.harga_satuan)} / ${item.satuan}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center border rounded-2 bg-white px-1">
                            <button type="button" class="btn btn-sm text-secondary p-0 px-1 btn-cart-qty-minus" data-id="${item.barang_id}">-</button>
                            <span class="small font-monospace fw-bold px-1.5">${item.qty}</span>
                            <button type="button" class="btn btn-sm text-secondary p-0 px-1 btn-cart-qty-plus" data-id="${item.barang_id}">+</button>
                        </div>
                        <div class="fw-bold font-monospace text-emerald small text-end" style="min-width: 65px;">${formatRupiah(lineTotal)}</div>
                        <button type="button" class="btn btn-sm text-danger p-0 btn-remove-cart-item" data-id="${item.barang_id}" title="Hapus">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                `;
                cafeContainer.appendChild(div);
            });

            if (cafeBadge) {
                if (cafeQtyTotal > 0) {
                    cafeBadge.textContent = cafeQtyTotal;
                    cafeBadge.classList.remove('d-none');
                } else {
                    cafeBadge.classList.add('d-none');
                }
            }

            // Qty plus / minus in cart
            cafeContainer.querySelectorAll('.btn-cart-qty-minus').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (orderState.cafeItems[id]) {
                        orderState.cafeItems[id].qty -= 1;
                        if (orderState.cafeItems[id].qty <= 0) {
                            delete orderState.cafeItems[id];
                        }
                        renderCart();
                    }
                });
            });

            cafeContainer.querySelectorAll('.btn-cart-qty-plus').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (orderState.cafeItems[id]) {
                        if (orderState.cafeItems[id].qty + 1 > orderState.cafeItems[id].stok) {
                            alert(`Maksimal stok tersedia adalah ${orderState.cafeItems[id].stok}`);
                            return;
                        }
                        orderState.cafeItems[id].qty += 1;
                        renderCart();
                    }
                });
            });

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

        // Biaya Tambahan Penonton (Denda jika > 2 orang)
        const dendaPenonton = orderState.dendaPenonton || 0;
        const rowDenda = document.getElementById('rowDendaPenonton');
        const labelDenda = document.getElementById('labelDendaPenonton');
        const labelExtra = document.getElementById('labelExtraPenonton');
        const infoDenda = document.getElementById('infoDendaPenonton');

        if (orderState.jumlahPenonton > 2) {
            const extra = orderState.jumlahPenonton - 2;
            if (rowDenda) rowDenda.style.setProperty('display', 'flex', 'important');
            if (labelExtra) labelExtra.textContent = extra;
            if (labelDenda) labelDenda.textContent = `+ ${formatRupiah(dendaPenonton)}`;
            if (infoDenda) {
                infoDenda.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Dikenakan tambahan ${extra} orang x Rp 5.000 = ${formatRupiah(dendaPenonton)}</span>`;
            }
        } else {
            if (rowDenda) rowDenda.style.setProperty('display', 'none', 'important');
            if (infoDenda) {
                infoDenda.innerHTML = `<i class="bi bi-info-circle text-primary me-1"></i>Maksimal 2 orang gratis denda. Lebih dari 2 orang dikenakan biaya tambahan Rp 5.000 / orang.`;
            }
        }

        // Kalkulasi Total
        const kotor = subtotalFilm + dendaPenonton + subtotalCafe;
        let diskon = 0;
        if (orderState.promo) {
            diskon = orderState.promo.diskon || 0;
            const rowDiscount = document.getElementById('rowDiscountAmount');
            if (rowDiscount) rowDiscount.style.display = 'flex';
            const labelDiscount = document.getElementById('labelDiscountAmount');
            if (labelDiscount) labelDiscount.textContent = `- ${formatRupiah(diskon)}`;
        } else {
            const rowDiscount = document.getElementById('rowDiscountAmount');
            if (rowDiscount) rowDiscount.style.display = 'none';
        }

        const totalBersih = Math.max(0, kotor - diskon);

        // Update labels
        const labelSubtotal = document.getElementById('labelSubtotal');
        if (labelSubtotal) labelSubtotal.textContent = formatRupiah(kotor);

        const labelPayableTotal = document.getElementById('labelPayableTotal');
        if (labelPayableTotal) labelPayableTotal.textContent = formatRupiah(totalBersih);

        // Update mobile bottom bar
        const mobileTotal = document.getElementById('mobilePayableTotal');
        if (mobileTotal) mobileTotal.textContent = formatRupiah(totalBersih);

        const mobileCount = document.getElementById('mobileCartItemCount');
        if (mobileCount) mobileCount.textContent = `${totalItemsCount} Item Terpilih`;

        // Update hidden compatibility labels
        const lblSubFilm = document.getElementById('labelSubtotalFilm');
        if (lblSubFilm) lblSubFilm.textContent = formatRupiah(subtotalFilm);
        const lblSubCafe = document.getElementById('labelSubtotalCafe');
        if (lblSubCafe) lblSubCafe.textContent = formatRupiah(subtotalCafe);
        const lblDiskon = document.getElementById('labelDiskonPromo');
        if (lblDiskon) lblDiskon.textContent = `- ${formatRupiah(diskon)}`;
        const lblTotalTagihan = document.getElementById('labelTotalTagihan');
        if (lblTotalTagihan) lblTotalTagihan.textContent = formatRupiah(totalBersih);

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
                (orderState.dendaPenonton || 0) +
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
    // PERHITUNGAN UANG DITERIMA & KEMBALIAN
    // -------------------------------------------------------------
    const inputUangDiterima = document.getElementById('inputUangDiterima');
    const labelKembalian = document.getElementById('labelKembalian');
    const selectTipePembayaran = document.getElementById('selectTipePembayaran');

    function getTotalTagihanAngka() {
        const subtotalFilm = orderState.film ? orderState.film.harga : 0;
        const dendaPenonton = orderState.dendaPenonton || 0;
        const subtotalCafe = Object.values(orderState.cafeItems).reduce((acc, curr) => acc + (curr.qty * curr.harga_satuan), 0);
        const diskon = orderState.promo ? orderState.promo.diskon : 0;
        return Math.max(0, subtotalFilm + dendaPenonton + subtotalCafe - diskon);
    }

    function updateKembalian(totalTagihan) {
        if (!inputUangDiterima || !labelKembalian) return;
        const diterima = parseRupiah(inputUangDiterima.value);
        const tagihan = typeof totalTagihan !== 'undefined' ? totalTagihan : getTotalTagihanAngka();
        const sisa = diterima - tagihan;

        if (sisa >= 0) {
            labelKembalian.className = 'fw-bold text-emerald font-monospace fs-6';
            labelKembalian.textContent = formatRupiah(sisa);
        } else {
            labelKembalian.className = 'fw-bold text-danger font-monospace fs-6';
            labelKembalian.textContent = '- ' + formatRupiah(Math.abs(sisa));
        }
    }

    if (inputUangDiterima) {
        inputUangDiterima.addEventListener('input', function () {
            const cursorPosition = this.selectionStart;
            const digitsBeforeCursor = this.value.slice(0, cursorPosition).replace(/\D/g, '').length;
            const digits = this.value.replace(/\D/g, '');
            this.value = digits ? formatRupiah(digits, false) : '';

            let nextCursorPosition = 0;
            let digitsAtCursor = 0;
            while (nextCursorPosition < this.value.length && digitsAtCursor < digitsBeforeCursor) {
                if (/\d/.test(this.value[nextCursorPosition])) digitsAtCursor++;
                nextCursorPosition++;
            }
            this.setSelectionRange(nextCursorPosition, nextCursorPosition);
            updateKembalian();
        });
    }

    document.querySelectorAll('.btn-chip-cash').forEach(btn => {
        btn.addEventListener('click', function () {
            const type = this.getAttribute('data-type');
            const total = getTotalTagihanAngka();
            if (type === 'exact') {
                inputUangDiterima.value = formatRupiah(total, false);
            } else {
                inputUangDiterima.value = formatRupiah(this.getAttribute('data-val'), false);
            }
            updateKembalian(total);
        });
    });

    // -------------------------------------------------------------
    // RESET POS TRANSAKSI BARU / CLEAR ALL
    // -------------------------------------------------------------
    function resetPOS() {
        orderState.film = null;
        orderState.room = null;
        orderState.cafeItems = {};
        orderState.promo = null;
        orderState.jumlahPenonton = 2;
        orderState.dendaPenonton = 0;
        if (inputJumlahPenonton) inputJumlahPenonton.value = 2;
        if (inputUangDiterima) inputUangDiterima.value = '';
        if (inputKodePromo) inputKodePromo.value = '';
        if (promoFeedbackMsg) promoFeedbackMsg.textContent = '';
        
        document.querySelectorAll('.movie-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.pos-room-card').forEach(c => c.classList.remove('selected'));
        
        const checkFilm = document.getElementById('filmStepChecked');
        if (checkFilm) checkFilm.classList.add('d-none');
        const checkRoom = document.getElementById('roomStepChecked');
        if (checkRoom) checkRoom.classList.add('d-none');
        
        const indicator = document.getElementById('selectedFilmBadgeIndicator');
        if (indicator) indicator.innerHTML = 'Film: <strong class="text-dark">Belum dipilih</strong>';
        
        renderCart();
    }

    document.querySelectorAll('.btn-reset-pos').forEach(btn => {
        btn.addEventListener('click', function () {
            if (confirm('Kosongkan keranjang dan mulai transaksi baru?')) {
                resetPOS();
            }
        });
    });

    // -------------------------------------------------------------
    // MOBILE SCROLL TO CART
    // -------------------------------------------------------------
    const btnScrollToCart = document.getElementById('btnScrollToCart');
    if (btnScrollToCart) {
        btnScrollToCart.addEventListener('click', function () {
            const panel = document.getElementById('checkoutPanelContainer');
            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // -------------------------------------------------------------
    // CHECKOUT & BAYAR & CETAK STRUK
    // -------------------------------------------------------------
    const btnCheckoutBayar = document.getElementById('btnCheckoutBayar');
    const modalKonfirmasiElement = document.getElementById('modalKonfirmasiPembayaran');
    const modalKonfirmasiPembayaran = modalKonfirmasiElement && typeof bootstrap !== 'undefined'
        ? bootstrap.Modal.getOrCreateInstance(modalKonfirmasiElement)
        : null;
    const btnKonfirmasiPembayaran = document.getElementById('btnKonfirmasiPembayaran');
    let pendingCheckoutPayload = null;

    function isiRingkasanKonfirmasi(totalTagihan, uangDiterima, namaMetode) {
        const daftarPesanan = document.getElementById('konfirmasiDaftarPesanan');
        daftarPesanan.replaceChildren();

        function tambahBaris(nama, qty, jumlah) {
            const row = document.createElement('tr');
            const namaCell = document.createElement('td');
            const qtyCell = document.createElement('td');
            const jumlahCell = document.createElement('td');

            namaCell.className = 'ps-3';
            namaCell.textContent = nama;
            qtyCell.className = 'text-center font-monospace';
            qtyCell.textContent = qty;
            jumlahCell.className = 'text-end pe-3 font-monospace';
            jumlahCell.textContent = jumlah;
            row.append(namaCell, qtyCell, jumlahCell);
            daftarPesanan.appendChild(row);
        }

        const subtotalCafe = Object.values(orderState.cafeItems)
            .reduce((subtotal, item) => subtotal + (item.qty * item.harga_satuan), 0);
        const subtotalItem = (orderState.film ? orderState.film.harga : 0) + subtotalCafe;

        tambahBaris(`Film: ${orderState.film.judul} (${orderState.film.durasi} menit)`, 1, formatRupiah(orderState.film.harga));
        tambahBaris(`Room: ${orderState.room.nama}`, 1, 'Termasuk');
        Object.values(orderState.cafeItems).forEach(item => {
            tambahBaris(item.nama, `${item.qty} ${item.satuan}`, formatRupiah(item.qty * item.harga_satuan));
        });

        document.getElementById('konfirmasiNamaPelanggan').textContent =
            document.getElementById('inputNamaPelanggan')?.value.trim() || 'Walk-in Customer (Guest)';
        document.getElementById('konfirmasiJumlahPenonton').textContent = orderState.jumlahPenonton;
        document.getElementById('konfirmasiSubtotal').textContent = formatRupiah(subtotalItem);
        document.getElementById('konfirmasiTotal').textContent = formatRupiah(totalTagihan);
        document.getElementById('konfirmasiMetodePembayaran').textContent = namaMetode;
        document.getElementById('konfirmasiUangDiterima').textContent = formatRupiah(uangDiterima);
        document.getElementById('konfirmasiKembalian').textContent = formatRupiah(Math.max(0, uangDiterima - totalTagihan));

        const barisDenda = document.getElementById('konfirmasiBarisDenda');
        const jumlahDenda = Math.max(0, orderState.jumlahPenonton - 2);
        barisDenda.classList.toggle('d-none', orderState.dendaPenonton <= 0);
        document.getElementById('konfirmasiJumlahDenda').textContent = jumlahDenda;
        document.getElementById('konfirmasiDenda').textContent = formatRupiah(orderState.dendaPenonton);

        const barisDiskon = document.getElementById('konfirmasiBarisDiskon');
        const diskon = orderState.promo ? orderState.promo.diskon : 0;
        barisDiskon.classList.toggle('d-none', diskon <= 0);
        document.getElementById('konfirmasiNamaPromo').textContent = orderState.promo ? `(${orderState.promo.nama})` : '';
        document.getElementById('konfirmasiDiskon').textContent = `- ${formatRupiah(diskon)}`;
    }

    if (modalKonfirmasiElement) {
        modalKonfirmasiElement.addEventListener('hidden.bs.modal', function () {
            pendingCheckoutPayload = null;
        });
    }

    if (btnCheckoutBayar) {
        btnCheckoutBayar.addEventListener('click', function () {
            if (!orderState.film) {
                alert('Silakan pilih Film studio terlebih dahulu pada Step 1!');
                const filmTab = document.getElementById('tab-film-btn');
                if (filmTab && typeof bootstrap !== 'undefined') new bootstrap.Tab(filmTab).show();
                return;
            }
            if (!orderState.room) {
                alert('Silakan pilih Room studio yang tersedia pada Step 2!');
                const roomTab = document.getElementById('tab-room-btn');
                if (roomTab && typeof bootstrap !== 'undefined') new bootstrap.Tab(roomTab).show();
                return;
            }

            const totalTagihan = getTotalTagihanAngka();
            const selectedOpt = selectTipePembayaran.options[selectTipePembayaran.selectedIndex];
            const tipePembayaranId = selectedOpt ? selectedOpt.value : null;
            const metode = selectedOpt ? selectedOpt.getAttribute('data-metode') : '';
            const isCash = metode.includes('tunai') || metode.includes('cash');

            let uangDiterima = parseRupiah(inputUangDiterima.value);
            if (isCash) {
                if (uangDiterima < totalTagihan) {
                    alert(`Uang tunai yang diterima kurang! Total tagihan adalah ${formatRupiah(totalTagihan)}.`);
                    inputUangDiterima.focus();
                    return;
                }
            } else {
                uangDiterima = totalTagihan;
            }

            const payload = {
                film_id: orderState.film.id,
                room_id: orderState.room.id,
                jumlah_penonton: orderState.jumlahPenonton || 2,
                tipe_pembayaran_id: tipePembayaranId,
                nama_pelanggan: document.getElementById('inputNamaPelanggan')?.value || 'Walk-in Customer (Guest)',
                promo_id: orderState.promo ? orderState.promo.id : null,
                uang_diterima: uangDiterima,
                cafe_items: Object.values(orderState.cafeItems).map(item => ({
                    barang_id: item.barang_id,
                    satuan_barang_id: item.satuan_barang_id,
                    qty: item.qty,
                    harga_satuan: item.harga_satuan
                }))
            };

            if (!modalKonfirmasiPembayaran) {
                alert('Modal konfirmasi pembayaran tidak dapat dibuka. Silakan muat ulang halaman.');
                return;
            }

            pendingCheckoutPayload = payload;
            isiRingkasanKonfirmasi(totalTagihan, uangDiterima, selectedOpt ? selectedOpt.textContent.trim() : '-');
            modalKonfirmasiPembayaran.show();
        });
    }

    if (btnKonfirmasiPembayaran) {
        btnKonfirmasiPembayaran.addEventListener('click', function () {
            if (!pendingCheckoutPayload) return;

            const payload = pendingCheckoutPayload;
            pendingCheckoutPayload = null;
            btnKonfirmasiPembayaran.disabled = true;
            btnKonfirmasiPembayaran.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses Pembayaran...`;
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
                btnKonfirmasiPembayaran.disabled = false;
                btnKonfirmasiPembayaran.innerHTML = `<i class="bi bi-printer-fill me-1"></i>Konfirmasi Bayar & Cetak`;

                if (data.status === 'success') {
                    modalKonfirmasiPembayaran.hide();
                    // Buka popup cetak struk thermal
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
                btnKonfirmasiPembayaran.disabled = false;
                btnKonfirmasiPembayaran.innerHTML = `<i class="bi bi-printer-fill me-1"></i>Konfirmasi Bayar & Cetak`;
                alert('Gagal memproses checkout pembayaran. Silakan periksa koneksi atau coba lagi.');
            });
        });
    }

});
</script>
@endpush
