@extends('template.index')

@section('title', 'Template untuk Struk | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">

    {{-- ALERT NOTIFIKASI SUKSES / ERROR --}}
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
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terdapat kesalahan pada formulir:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- PAGE HEADER -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-emerald-subtle text-emerald rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: rgba(5, 150, 105, 0.12); color: #059669;">
                        <i class="bi bi-receipt-cutoff fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h4 class="fw-bold mb-0 text-heading">Pengaturan Template Struk</h4>
                            @if($selectedTemplate && $selectedTemplate->is_aktif)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>Template Aktif di Kasir
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-clock-history me-1"></i>Draf / Nonaktif
                                </span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0">
                            Atur format header, rincian transaksi, dan pesan footer struk cetak kasir thermal dengan tinjauan langsung (live preview).
                        </p>
                    </div>
                </div>

                <!-- Template Selector & Action Buttons -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3 fw-semibold d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-collection"></i>
                            <span>Pilih Template: <strong>{{ Str::limit($selectedTemplate->nama_template ?? 'Default', 20) }}</strong></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2" style="min-width: 260px;">
                            <li class="dropdown-header small text-uppercase text-muted fw-bold">Daftar Template Struk</li>
                            @foreach($templates as $tmpl)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center py-2 {{ $selectedTemplate && $selectedTemplate->id == $tmpl->id ? 'active bg-emerald text-white' : '' }}" 
                                       href="{{ route('admin.pengaturan.struk', ['template_id' => $tmpl->id]) }}">
                                        <span>
                                            <i class="bi bi-receipt me-1.5"></i>{{ $tmpl->nama_template }}
                                        </span>
                                        @if($tmpl->is_aktif)
                                            <span class="badge {{ $selectedTemplate && $selectedTemplate->id == $tmpl->id ? 'bg-white text-emerald' : 'bg-success' }} rounded-pill" style="font-size: 0.68rem;">Aktif</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-emerald fw-semibold py-2" data-bs-toggle="modal" data-bs-target="#modalTambahTemplate">
                                    <i class="bi bi-plus-circle-fill me-1.5"></i> Buat Template Baru
                                </button>
                            </li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-emerald rounded-3 px-3 d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalTambahTemplate">
                        <i class="bi bi-plus-lg"></i>
                        <span>Template Baru</span>
                    </button>

                    @if($selectedTemplate)
                        <div class="btn-group">
                            <form action="{{ route('admin.pengaturan.struk.duplicate', $selectedTemplate->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary rounded-start-3" title="Duplikat Template Ini">
                                    <i class="bi bi-copy me-1"></i> Duplikat
                                </button>
                            </form>
                            @if(!$selectedTemplate->is_aktif)
                                <form action="{{ route('admin.pengaturan.struk.set-active', $selectedTemplate->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Jadikan Sebagai Template Aktif di Kasir">
                                        <i class="bi bi-check-lg me-1"></i> Aktifkan
                                    </button>
                                </form>
                            @endif
                            @if($templates->count() > 1)
                                <button type="button" class="btn btn-outline-danger rounded-end-3" data-bs-toggle="modal" data-bs-target="#modalHapusTemplate" title="Hapus Template">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN SIDE-BY-SIDE CONTENT -->
    <div class="row g-4">
        
        <!-- ========================================== -->
        <!-- KOLOM KIRI: FORM PENGATURAN TEMPLATE STRUK -->
        <!-- ========================================== -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-emerald fs-5"></i>
                            <h5 class="fw-bold mb-0 text-dark">Form Pengaturan Template</h5>
                        </div>
                        <span class="badge bg-light text-dark border font-monospace">
                            ID: #{{ $selectedTemplate->id }}
                        </span>
                    </div>
                </div>

                <form id="formTemplateStruk" action="{{ route('admin.pengaturan.struk.update', $selectedTemplate->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-4">

                        <!-- NAV TABS PENGATURAN -->
                        <ul class="nav nav-pills nav-fill bg-light p-1.5 rounded-3 mb-4" id="templateTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold py-2 rounded-3" id="tab-toko-btn" data-bs-toggle="pill" data-bs-target="#tab-toko" type="button" role="tab">
                                    <i class="bi bi-shop me-1"></i> 1. Toko & Header
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold py-2 rounded-3" id="tab-kertas-btn" data-bs-toggle="pill" data-bs-target="#tab-kertas" type="button" role="tab">
                                    <i class="bi bi-aspect-ratio me-1"></i> 2. Format Kertas
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold py-2 rounded-3" id="tab-transaksi-btn" data-bs-toggle="pill" data-bs-target="#tab-transaksi" type="button" role="tab">
                                    <i class="bi bi-card-checklist me-1"></i> 3. Isi Transaksi
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold py-2 rounded-3" id="tab-footer-btn" data-bs-toggle="pill" data-bs-target="#tab-footer" type="button" role="tab">
                                    <i class="bi bi-chat-left-quote me-1"></i> 4. Footer & Catatan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="templateTabsContent">
                            
                            <!-- TAB 1: INFORMASI TOKO & HEADER -->
                            <div class="tab-pane fade show active" id="tab-toko" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12 col-md-8">
                                        <label class="form-label small fw-semibold text-muted">Nama Template <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_template" id="inputNamaTemplate" class="form-control rounded-3" 
                                               value="{{ old('nama_template', $selectedTemplate->nama_template) }}" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-semibold text-muted">Status Template</label>
                                        <div class="form-check form-switch pt-2">
                                            <input class="form-check-input" type="checkbox" name="is_aktif" id="switchIsAktif" value="1" 
                                                   {{ old('is_aktif', $selectedTemplate->is_aktif) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold small" for="switchIsAktif">Jadikan Aktif</label>
                                        </div>
                                    </div>

                                    <div class="col-12"><hr class="my-2 border-light-subtle"></div>

                                    <div class="col-12 col-md-7">
                                        <label class="form-label small fw-semibold text-muted">Nama Usaha / Brand Utama <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                                            <input type="text" name="nama_usaha" id="inputNamaUsaha" class="form-control border-start-0 rounded-end-3" 
                                                   value="{{ old('nama_usaha', $selectedTemplate->nama_usaha) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-semibold text-muted">Slogan / Sub Judul</label>
                                        <input type="text" name="sub_judul" id="inputSubJudul" class="form-control rounded-3" 
                                               value="{{ old('sub_judul', $selectedTemplate->sub_judul) }}" placeholder="Contoh: Cinema & Cozy Cafe">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Mode Nama Cabang</label>
                                        <select name="mode_cabang" id="selectModeCabang" class="form-select rounded-3">
                                            <option value="otomatis" {{ old('mode_cabang', $selectedTemplate->mode_cabang) === 'otomatis' ? 'selected' : '' }}>
                                                Otomatis (Sesuai Cabang Transaksi)
                                            </option>
                                            <option value="manual" {{ old('mode_cabang', $selectedTemplate->mode_cabang) === 'manual' ? 'selected' : '' }}>
                                                Manual (Gunakan Teks Khusus)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6" id="wrapperCabangManual" style="{{ old('mode_cabang', $selectedTemplate->mode_cabang) === 'manual' ? '' : 'display: none;' }}">
                                        <label class="form-label small fw-semibold text-muted">Nama Cabang Manual</label>
                                        <input type="text" name="nama_cabang_manual" id="inputCabangManual" class="form-control rounded-3" 
                                               value="{{ old('nama_cabang_manual', $selectedTemplate->nama_cabang_manual) }}" placeholder="Contoh: Cabang Utama Pekanbaru">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Alamat Toko (Header)</label>
                                        <textarea name="alamat" id="inputAlamat" rows="2" class="form-control rounded-3" placeholder="Jl. Arifin Ahmad No. 88, Pekanbaru">{{ old('alamat', $selectedTemplate->alamat) }}</textarea>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold text-muted">No. Telepon / WhatsApp</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                                            <input type="text" name="telepon" id="inputTelepon" class="form-control border-start-0 rounded-end-3" 
                                                   value="{{ old('telepon', $selectedTemplate->telepon) }}" placeholder="0812-3456-7890">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Website / Instagram / Medsos</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-instagram"></i></span>
                                            <input type="text" name="website_sosmed" id="inputSosmed" class="form-control border-start-0 rounded-end-3" 
                                                   value="{{ old('website_sosmed', $selectedTemplate->website_sosmed) }}" placeholder="IG: @pressentstudio">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Pesan Sambutan Header (Opsional)</label>
                                        <input type="text" name="pesan_header" id="inputPesanHeader" class="form-control rounded-3" 
                                               value="{{ old('pesan_header', $selectedTemplate->pesan_header) }}" placeholder="Contoh: Selamat Datang di Pressent Studio Cinema & Cafe">
                                    </div>

                                    <!-- Upload Logo -->
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label small fw-semibold text-dark mb-0">Logo Struk Thermal</label>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" name="tampilkan_logo" id="switchTampilkanLogo" value="1" 
                                                           {{ old('tampilkan_logo', $selectedTemplate->tampilkan_logo) ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-semibold" for="switchTampilkanLogo">Tampilkan Logo</label>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="file" name="logo" id="inputLogoFile" class="form-control rounded-3" accept="image/png, image/jpeg, image/jpg, image/webp">
                                                @if($selectedTemplate->logo_path)
                                                    <div class="border rounded p-1 bg-white" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                        <img src="{{ asset($selectedTemplate->logo_path) }}" alt="Logo" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-text small" style="font-size: 0.75rem;">
                                                Disarankan gambar rasio horizontal/monokrom kontras tinggi (PNG/JPG, maks 2MB).
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: FORMAT KERTAS & UKURAN -->
                            <div class="tab-pane fade" id="tab-kertas" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Pilih Lebar Kertas Printer Thermal <span class="text-danger">*</span></label>
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <div class="border rounded-4 p-3 h-100 paper-choice {{ old('lebar_kertas', $selectedTemplate->lebar_kertas) === '80mm' ? 'border-emerald bg-emerald-subtle' : 'border-light-subtle' }}" id="boxPaper80">
                                                    <div class="form-check">
                                                        <input class="form-check-input radio-paper" type="radio" name="lebar_kertas" id="paper80" value="80mm" 
                                                               {{ old('lebar_kertas', $selectedTemplate->lebar_kertas) === '80mm' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold text-dark d-block" for="paper80">
                                                            Lebar 80 mm (Standar Kasir POS)
                                                        </label>
                                                        <p class="text-muted small mb-0 mt-1">
                                                            Ukuran standar printer POS desktop (Epson TM-T82, Iware, dsb.). Muat banyak kolom informasi dengan rapi.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="border rounded-4 p-3 h-100 paper-choice {{ old('lebar_kertas', $selectedTemplate->lebar_kertas) === '58mm' ? 'border-emerald bg-emerald-subtle' : 'border-light-subtle' }}" id="boxPaper58">
                                                    <div class="form-check">
                                                        <input class="form-check-input radio-paper" type="radio" name="lebar_kertas" id="paper58" value="58mm" 
                                                               {{ old('lebar_kertas', $selectedTemplate->lebar_kertas) === '58mm' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold text-dark d-block" for="paper58">
                                                            Lebar 58 mm (Mini / Mobile Bluetooth)
                                                        </label>
                                                        <p class="text-muted small mb-0 mt-1">
                                                            Ukuran mini printer thermal portabel bluetooth / EDC. Tampilan ringkas dan padat.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <label class="form-label small fw-semibold text-muted">Ukuran Huruf Struk (Font Size)</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check radio-fontsize" name="font_size" id="fontSmall" value="small" 
                                                   {{ old('font_size', $selectedTemplate->font_size) === 'small' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-secondary py-2" for="fontSmall">
                                                <i class="bi bi-type me-1"></i> Kecil (11px)
                                            </label>

                                            <input type="radio" class="btn-check radio-fontsize" name="font_size" id="fontMedium" value="medium" 
                                                   {{ old('font_size', $selectedTemplate->font_size, 'medium') === 'medium' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-secondary py-2" for="fontMedium">
                                                <i class="bi bi-type me-1"></i> Sedang (12px - Standar)
                                            </label>

                                            <input type="radio" class="btn-check radio-fontsize" name="font_size" id="fontLarge" value="large" 
                                                   {{ old('font_size', $selectedTemplate->font_size) === 'large' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-secondary py-2" for="fontLarge">
                                                <i class="bi bi-type me-1"></i> Besar (13px)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: OPSI TAMPILAN ELEMEN TRANSAKSI -->
                            <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                                <p class="text-muted small mb-3">
                                    Pilih bagian atau informasi apa saja yang ingin dimunculkan pada struk kasir saat dicetak.
                                </p>

                                <div class="row g-2.5">
                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_cabang" id="swCabang" value="1" 
                                                       {{ old('tampilkan_cabang', $selectedTemplate->tampilkan_cabang) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swCabang">Nama Cabang Toko</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan cabang tempat transaksi berlangsung.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_alamat" id="swAlamat" value="1" 
                                                       {{ old('tampilkan_alamat', $selectedTemplate->tampilkan_alamat) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swAlamat">Alamat Lengkap Toko</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan alamat di bagian header atas struk.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_kontak" id="swKontak" value="1" 
                                                       {{ old('tampilkan_kontak', $selectedTemplate->tampilkan_kontak) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swKontak">Kontak & Media Sosial</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan nomor telepon/WA dan username IG toko.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_no_nota" id="swNoNota" value="1" 
                                                       {{ old('tampilkan_no_nota', $selectedTemplate->tampilkan_no_nota) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swNoNota">Nomor Nota Transaksi</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Kode unik transaksi untuk pelacakan struk.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_waktu" id="swWaktu" value="1" 
                                                       {{ old('tampilkan_waktu', $selectedTemplate->tampilkan_waktu) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swWaktu">Tanggal & Waktu Transaksi</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Format tanggal WIB (Asia/Jakarta).</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_kasir" id="swKasir" value="1" 
                                                       {{ old('tampilkan_kasir', $selectedTemplate->tampilkan_kasir) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swKasir">Nama Kasir Bertugas</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan nama akun user kasir yang melayani.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_pelanggan" id="swPelanggan" value="1" 
                                                       {{ old('tampilkan_pelanggan', $selectedTemplate->tampilkan_pelanggan) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swPelanggan">Nama Pelanggan (Guest/Nama)</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Nama pembeli tiket/tamu yang memesan.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_detail_room" id="swDetailRoom" value="1" 
                                                       {{ old('tampilkan_detail_room', $selectedTemplate->tampilkan_detail_room) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swDetailRoom">Detail Room & Durasi Menit</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan nama room studio dan durasi film.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_detail_penonton" id="swDetailPenonton" value="1" 
                                                       {{ old('tampilkan_detail_penonton', $selectedTemplate->tampilkan_detail_penonton) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swDetailPenonton">Penonton & Tambahan Biaya</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Jumlah penonton & denda jika lebih dari 2 orang.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_detail_cafe" id="swDetailCafe" value="1" 
                                                       {{ old('tampilkan_detail_cafe', $selectedTemplate->tampilkan_detail_cafe) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swDetailCafe">Pesanan Makanan & Cafe</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Rincian item snack, minuman, qty & subtotal.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_diskon" id="swDiskon" value="1" 
                                                       {{ old('tampilkan_diskon', $selectedTemplate->tampilkan_diskon) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swDiskon">Potongan Diskon Promo</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Baris pengurangan nominal voucher diskon jika ada.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_metode_bayar" id="swMetodeBayar" value="1" 
                                                       {{ old('tampilkan_metode_bayar', $selectedTemplate->tampilkan_metode_bayar) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swMetodeBayar">Metode Bayar & Uang Diterima</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan Tunai/QRIS dan nominal uang masuk.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_kembalian" id="swKembalian" value="1" 
                                                       {{ old('tampilkan_kembalian', $selectedTemplate->tampilkan_kembalian) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swKembalian">Nominal Uang Kembalian</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Menampilkan selisih uang diterima dikurangi total.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: PESAN FOOTER & CATATAN -->
                            <div class="tab-pane fade" id="tab-footer" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Pesan Penutup Utama (Header Footer)</label>
                                        <input type="text" name="pesan_footer_1" id="inputFooter1" class="form-control rounded-3" 
                                               value="{{ old('pesan_footer_1', $selectedTemplate->pesan_footer_1) }}" placeholder="TERIMA KASIH ATAS KUNJUNGAN ANDA">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Pesan Ucapan / Doa Penutup (Baris 2)</label>
                                        <textarea name="pesan_footer_2" id="inputFooter2" rows="2" class="form-control rounded-3" 
                                                  placeholder="Selamat menikmati pengalaman menonton & hidangan kami di Pressent Studio.">{{ old('pesan_footer_2', $selectedTemplate->pesan_footer_2) }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-muted">Catatan Kebijakan / Syarat & Ketentuan</label>
                                        <input type="text" name="catatan_kaki" id="inputCatatanKaki" class="form-control rounded-3" 
                                               value="{{ old('catatan_kaki', $selectedTemplate->catatan_kaki) }}" placeholder="Tiket & item cafe yang sudah dibeli tidak dapat ditukar/dikembalikan.">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_barcode" id="swBarcode" value="1" 
                                                       {{ old('tampilkan_barcode', $selectedTemplate->tampilkan_barcode) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swBarcode">Tampilkan Barcode Struk</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Simulasi barcode nota untuk kesan kasir profesional.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input switch-live" type="checkbox" name="tampilkan_waktu_cetak" id="swWaktuCetak" value="1" 
                                                       {{ old('tampilkan_waktu_cetak', $selectedTemplate->tampilkan_waktu_cetak) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="swWaktuCetak">Tampilkan Waktu Cetak</label>
                                            </div>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">Mencetak timestamp "Dicetak pada: dd-mm-yyyy hh:ii:ss".</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- CARD FOOTER DENGAN TOMBOL SIMPAN -->
                    <div class="card-footer bg-light border-top py-3 px-4 d-flex justify-content-between align-items-center">
                        <button type="reset" class="btn btn-light border rounded-3 px-3 fw-semibold text-muted" id="btnResetForm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Input
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-emerald rounded-3 px-3 d-none d-md-inline-flex align-items-center gap-1.5" id="btnTestPrint">
                                <i class="bi bi-printer"></i> Test Cetak Pratinjau
                            </button>
                            <button type="submit" class="btn btn-emerald rounded-3 px-4 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-floppy2-fill"></i> Simpan Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- KOLOM KANAN: REVIEW / PRATINJAU STRUK LIVE -->
        <!-- ========================================== -->
        <div class="col-12 col-xl-5">
            <div class="sticky-top" style="top: 20px; z-index: 10;">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-eye-fill text-warning fs-5"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Tampilan / Review Struk</h6>
                                <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">LIVE REAL-TIME PREVIEW</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5" id="btnTogglePaperQuick" title="Ganti cepat ukuran kertas preview">
                                <i class="bi bi-arrows-expand me-1"></i><span id="labelQuickPaper">80mm</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="window.printThermalPreview()" title="Cetak struk preview ini">
                                <i class="bi bi-printer-fill me-1"></i> Cetak
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-4 bg-light text-center" style="min-height: 520px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; overflow-x: auto;">

                        <!-- THERMAL RECEIPT PREVIEW WRAPPER -->
                        <div id="receiptPaper" class="thermal-receipt shadow-sm" style="width: {{ $selectedTemplate->lebar_kertas === '58mm' ? '280px' : '360px' }};">
                            
                            <!-- Jagged top tear edge -->
                            <div class="thermal-tear-top"></div>

                            <div class="thermal-receipt-content" id="thermalContent" style="font-size: {{ $selectedTemplate->font_size === 'small' ? '11px' : ($selectedTemplate->font_size === 'large' ? '13px' : '12px') }};">
                                
                                <!-- STORE HEADER -->
                                <div class="text-center pb-1">
                                    <div id="previewLogoWrapper" style="{{ $selectedTemplate->tampilkan_logo && $selectedTemplate->logo_path ? '' : 'display: none;' }} margin-bottom: 5px;">
                                        <img id="previewLogoImg" src="{{ $selectedTemplate->logo_path ? asset($selectedTemplate->logo_path) : '' }}" 
                                             alt="Logo" style="max-height: 42px; max-width: 120px; object-fit: contain;">
                                    </div>
                                    <div class="fw-bold fs-6 text-uppercase" id="previewNamaUsaha">{{ $selectedTemplate->nama_usaha }}</div>
                                    <div class="text-muted small" id="previewSubJudul" style="{{ $selectedTemplate->sub_judul ? '' : 'display: none;' }}; font-size: 9.5px;">
                                        {{ $selectedTemplate->sub_judul }}
                                    </div>
                                    <div class="fw-bold small" id="previewCabang" style="{{ $selectedTemplate->tampilkan_cabang ? '' : 'display: none;' }}; font-size: 10.5px;">
                                        {{ $selectedTemplate->mode_cabang === 'manual' && $selectedTemplate->nama_cabang_manual ? $selectedTemplate->nama_cabang_manual : $sampleData['nama_cabang'] }}
                                    </div>
                                    <div class="text-muted small" id="previewAlamat" style="{{ $selectedTemplate->tampilkan_alamat ? '' : 'display: none;' }}; font-size: 9.5px; line-height: 1.25;">
                                        {{ $selectedTemplate->alamat ?: $sampleData['alamat_cabang'] }}
                                    </div>
                                    <div class="text-muted small" id="previewKontak" style="{{ $selectedTemplate->tampilkan_kontak ? '' : 'display: none;' }}; font-size: 9.5px;">
                                        Telp/WA: <span id="previewTelepon">{{ $selectedTemplate->telepon ?: $sampleData['kontak_cabang'] }}</span>
                                    </div>
                                    <div class="text-muted small" id="previewSosmed" style="{{ $selectedTemplate->tampilkan_kontak && $selectedTemplate->website_sosmed ? '' : 'display: none;' }}; font-size: 9.5px;">
                                        {{ $selectedTemplate->website_sosmed }}
                                    </div>
                                    <div id="previewPesanHeader" style="{{ $selectedTemplate->pesan_header ? '' : 'display: none;' }}; font-size: 9.5px; font-style: italic; margin-top: 3px; padding: 2px 4px; background: #f8fafc; border-radius: 3px;">
                                        "{{ $selectedTemplate->pesan_header }}"
                                    </div>
                                </div>

                                <div class="thermal-divider"></div>

                                <!-- META TRANSAKSI -->
                                <div id="previewRowNoNota" class="thermal-row" style="{{ $selectedTemplate->tampilkan_no_nota ? '' : 'display: none;' }}">
                                    <span>NO NOTA:</span>
                                    <span class="fw-bold">{{ $sampleData['nomor_transaksi'] }}</span>
                                </div>
                                <div id="previewRowWaktu" class="thermal-row" style="{{ $selectedTemplate->tampilkan_waktu ? '' : 'display: none;' }}">
                                    <span>TANGGAL:</span>
                                    <span>{{ $sampleData['waktu_transaksi'] }}</span>
                                </div>
                                <div id="previewRowKasir" class="thermal-row" style="{{ $selectedTemplate->tampilkan_kasir ? '' : 'display: none;' }}">
                                    <span>KASIR:</span>
                                    <span>{{ $sampleData['nama_kasir'] }}</span>
                                </div>
                                <div id="previewRowPelanggan" class="thermal-row" style="{{ $selectedTemplate->tampilkan_pelanggan ? '' : 'display: none;' }}">
                                    <span>PELANGGAN:</span>
                                    <span>{{ $sampleData['nama_pelanggan'] }}</span>
                                </div>

                                <div class="thermal-double-divider"></div>

                                <!-- ITEM FILM & ROOM -->
                                <div class="fw-bold mb-1 text-start" style="font-size: 10.5px;">[ TIKET STUDIO & FILM ]</div>
                                <div class="thermal-row">
                                    <span style="max-width: 68%; font-weight: bold; text-align: left;">
                                        {{ $sampleData['film_judul'] }}
                                    </span>
                                    <span class="fw-bold">
                                        Rp {{ number_format($sampleData['subtotal_film'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-start" style="font-size: 9.5px; color: #475569; margin-left: 4px; margin-bottom: 5px;">
                                    <div id="previewRowDetailRoom" style="{{ $selectedTemplate->tampilkan_detail_room ? '' : 'display: none;' }}">
                                        • Room: <strong>{{ $sampleData['room_nama'] }}</strong> (Kap: {{ $sampleData['room_kapasitas'] }} org)<br>
                                        • Durasi: {{ $sampleData['durasi_menit'] }} Menit
                                    </div>
                                    <div id="previewRowDetailPenonton" style="{{ $selectedTemplate->tampilkan_detail_penonton ? '' : 'display: none;' }}">
                                        • Penonton: {{ $sampleData['jumlah_penonton'] }} Orang <span style="color: #dc2626;">(Extra +Rp {{ number_format($sampleData['denda_penonton'], 0, ',', '.') }})</span>
                                    </div>
                                </div>

                                <!-- ITEM CAFE & SNACK -->
                                <div id="previewSectionCafe" style="{{ $selectedTemplate->tampilkan_detail_cafe ? '' : 'display: none;' }}">
                                    <div class="thermal-divider"></div>
                                    <div class="fw-bold mb-1 text-start" style="font-size: 10.5px;">[ PESANAN CAFE & SNACK ]</div>
                                    @foreach($sampleData['cafe_items'] as $cItem)
                                        <div class="thermal-row">
                                            <span style="max-width: 65%; text-align: left;">{{ $cItem['nama_barang'] }}</span>
                                            <span class="fw-bold">Rp {{ number_format($cItem['subtotal'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="text-start text-muted" style="font-size: 9px; margin-left: 4px; margin-bottom: 3px;">
                                            {{ $cItem['qty'] }} {{ $cItem['satuan'] }} x Rp {{ number_format($cItem['harga_satuan'], 0, ',', '.') }}
                                        </div>
                                    @endforeach
                                </div>

                                <div class="thermal-divider"></div>

                                <!-- RINGKASAN TAGIHAN -->
                                <div class="thermal-row">
                                    <span>SUBTOTAL:</span>
                                    <span>Rp {{ number_format($sampleData['subtotal_film'] + $sampleData['subtotal_cafe'], 0, ',', '.') }}</span>
                                </div>
                                <div id="previewRowDiskon" class="thermal-row" style="{{ $selectedTemplate->tampilkan_diskon ? '' : 'display: none;' }}; color: #059669;">
                                    <span>DISKON PROMO:</span>
                                    <span>- Rp {{ number_format($sampleData['diskon'], 0, ',', '.') }}</span>
                                </div>
                                <div class="thermal-row fw-bold" style="font-size: 13px; margin: 4px 0;">
                                    <span>TOTAL BAYAR:</span>
                                    <span>Rp {{ number_format($sampleData['total_bayar'], 0, ',', '.') }}</span>
                                </div>

                                <div class="thermal-divider"></div>

                                <!-- PEMBAYARAN -->
                                <div id="previewRowMetode" class="thermal-row" style="{{ $selectedTemplate->tampilkan_metode_bayar ? '' : 'display: none;' }}">
                                    <span>METODE BAYAR:</span>
                                    <span class="fw-bold">{{ $sampleData['metode_bayar'] }}</span>
                                </div>
                                <div id="previewRowUangDiterima" class="thermal-row" style="{{ $selectedTemplate->tampilkan_metode_bayar ? '' : 'display: none;' }}">
                                    <span>UANG DITERIMA:</span>
                                    <span>Rp {{ number_format($sampleData['uang_diterima'], 0, ',', '.') }}</span>
                                </div>
                                <div id="previewRowKembalian" class="thermal-row" style="{{ $selectedTemplate->tampilkan_kembalian ? '' : 'display: none;' }}">
                                    <span>KEMBALIAN:</span>
                                    <span class="fw-bold">Rp {{ number_format($sampleData['kembalian'], 0, ',', '.') }}</span>
                                </div>

                                <div class="thermal-double-divider"></div>

                                <!-- FOOTER & BARCODE -->
                                <div class="text-center pt-1">
                                    <div class="fw-bold" id="previewFooter1" style="font-size: 10.5px;">
                                        {{ $selectedTemplate->pesan_footer_1 ?: 'TERIMA KASIH ATAS KUNJUNGAN ANDA' }}
                                    </div>
                                    <div class="text-muted" id="previewFooter2" style="font-size: 9.5px; margin-top: 2px;">
                                        {{ $selectedTemplate->pesan_footer_2 ?: 'Selamat menikmati pengalaman menonton & hidangan kami di Pressent Studio.' }}
                                    </div>
                                    <div id="previewCatatanKaki" style="{{ $selectedTemplate->catatan_kaki ? '' : 'display: none;' }}; font-size: 8.5px; color: #64748b; font-style: italic; margin-top: 3px;">
                                        * {{ $selectedTemplate->catatan_kaki }}
                                    </div>
                                    <div id="previewBarcode" style="{{ $selectedTemplate->tampilkan_barcode ? '' : 'display: none;' }}; font-family: monospace; font-size: 16px; letter-spacing: 3px; font-weight: bold; margin-top: 5px;">
                                        ||| | |||| ||| |||||
                                    </div>
                                    <div id="previewWaktuCetak" style="{{ $selectedTemplate->tampilkan_waktu_cetak ? '' : 'display: none;' }}; font-size: 8.5px; color: #94a3b8; margin-top: 4px;">
                                        Dicetak pada: {{ $sampleData['waktu_cetak'] }}
                                    </div>
                                </div>

                            </div>

                            <!-- Jagged bottom tear edge -->
                            <div class="thermal-tear-bottom"></div>
                        </div>

                        <div class="mt-3 text-muted small" style="font-size: 0.78rem;">
                            <i class="bi bi-info-circle me-1"></i> Tampilan di atas merepresentasikan lembar fisik kertas printer thermal kasir POS.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TAMBAH TEMPLATE STRUK BARU           -->
<!-- ========================================== -->
<div class="modal fade" id="modalTambahTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.pengaturan.struk.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-heading d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-plus text-emerald"></i> Buat Template Struk Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="nama_template" class="form-control rounded-3" placeholder="Contoh: Template Promo Weekend 80mm" required autofocus>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Lebar Kertas</label>
                            <select name="lebar_kertas" class="form-select rounded-3">
                                <option value="80mm">80 mm (Standar POS)</option>
                                <option value="58mm">58 mm (Mini Portable)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Ukuran Huruf</label>
                            <select name="font_size" class="form-select rounded-3">
                                <option value="small">Kecil (11px)</option>
                                <option value="medium" selected>Sedang (12px)</option>
                                <option value="large">Besar (13px)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Usaha / Brand</label>
                        <input type="text" name="nama_usaha" class="form-control rounded-3" value="{{ $selectedTemplate->nama_usaha ?? 'PRESSENT STUDIO & CAFE' }}" required>
                    </div>

                    <input type="hidden" name="mode_cabang" value="otomatis">
                    <input type="hidden" name="alamat" value="{{ $selectedTemplate->alamat ?? 'Jl. Arifin Ahmad No. 88, Pekanbaru' }}">
                    <input type="hidden" name="telepon" value="{{ $selectedTemplate->telepon ?? '0812-3456-7890' }}">
                    <input type="hidden" name="website_sosmed" value="{{ $selectedTemplate->website_sosmed ?? 'IG: @pressentstudio' }}">
                    <input type="hidden" name="pesan_footer_1" value="TERIMA KASIH ATAS KUNJUNGAN ANDA">
                    <input type="hidden" name="pesan_footer_2" value="Selamat menikmati pengalaman menonton & hidangan kami di Pressent Studio.">
                    <input type="hidden" name="catatan_kaki" value="Tiket & item cafe yang sudah dibeli tidak dapat ditukar/dikembalikan.">

                    <!-- Default checked booleans -->
                    <input type="hidden" name="tampilkan_cabang" value="1">
                    <input type="hidden" name="tampilkan_alamat" value="1">
                    <input type="hidden" name="tampilkan_kontak" value="1">
                    <input type="hidden" name="tampilkan_no_nota" value="1">
                    <input type="hidden" name="tampilkan_waktu" value="1">
                    <input type="hidden" name="tampilkan_kasir" value="1">
                    <input type="hidden" name="tampilkan_pelanggan" value="1">
                    <input type="hidden" name="tampilkan_detail_room" value="1">
                    <input type="hidden" name="tampilkan_detail_penonton" value="1">
                    <input type="hidden" name="tampilkan_detail_cafe" value="1">
                    <input type="hidden" name="tampilkan_diskon" value="1">
                    <input type="hidden" name="tampilkan_metode_bayar" value="1">
                    <input type="hidden" name="tampilkan_kembalian" value="1">
                    <input type="hidden" name="tampilkan_barcode" value="1">
                    <input type="hidden" name="tampilkan_waktu_cetak" value="1">

                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_aktif" id="modalSwitchAktif" value="1">
                        <label class="form-check-label small fw-semibold" for="modalSwitchAktif">Langsung jadikan template aktif kasir</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald rounded-3 px-4">Buat Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL KONFIRMASI HAPUS TEMPLATE            -->
<!-- ========================================== -->
@if($selectedTemplate && $templates->count() > 1)
<div class="modal fade" id="modalHapusTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="p-3 bg-danger-subtle text-danger rounded-circle d-inline-flex mb-3">
                    <i class="bi bi-trash fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Hapus Template?</h6>
                <p class="text-muted small mb-4">
                    Apakah Anda yakin ingin menghapus template <strong>"{{ $selectedTemplate->nama_template }}"</strong>?
                </p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill rounded-3" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('admin.pengaturan.struk.destroy', $selectedTemplate->id) }}" method="POST" class="flex-fill">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 rounded-3">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

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
        color: #059669;
        border-color: #059669;
    }
    .btn-outline-emerald:hover {
        background: #059669;
        color: #ffffff;
    }
    .text-emerald {
        color: #059669 !important;
    }
    .bg-emerald-subtle {
        background-color: #ecfdf5 !important;
    }
    .border-emerald {
        border-color: #059669 !important;
    }

    /* THERMAL RECEIPT STYLING */
    .thermal-receipt {
        background: #ffffff;
        color: #111827;
        font-family: 'Courier New', Courier, monospace;
        position: relative;
        padding: 0;
        margin: 0 auto;
        border-radius: 4px;
        transition: width 0.3s ease;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    .thermal-receipt-content {
        padding: 16px 14px;
        line-height: 1.35;
    }

    /* Sawtooth jagged tear effect top & bottom */
    .thermal-tear-top, .thermal-tear-bottom {
        height: 8px;
        width: 100%;
        background-size: 14px 8px;
        background-repeat: repeat-x;
    }
    .thermal-tear-top {
        background-image: radial-gradient(circle at 7px 0px, transparent 6px, #ffffff 7px);
    }
    .thermal-tear-bottom {
        background-image: radial-gradient(circle at 7px 8px, transparent 6px, #ffffff 7px);
    }

    .thermal-divider {
        border-top: 1px dashed #94a3b8;
        margin: 7px 0;
    }
    .thermal-double-divider {
        border-top: 2px dashed #475569;
        margin: 7px 0;
    }
    .thermal-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2.5px;
    }

    .paper-choice {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .paper-choice:hover {
        border-color: #059669 !important;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. ELEMEN FORM INPUT
    const inputNamaUsaha = document.getElementById('inputNamaUsaha');
    const inputSubJudul = document.getElementById('inputSubJudul');
    const selectModeCabang = document.getElementById('selectModeCabang');
    const inputCabangManual = document.getElementById('inputCabangManual');
    const wrapperCabangManual = document.getElementById('wrapperCabangManual');
    const inputAlamat = document.getElementById('inputAlamat');
    const inputTelepon = document.getElementById('inputTelepon');
    const inputSosmed = document.getElementById('inputSosmed');
    const inputPesanHeader = document.getElementById('inputPesanHeader');
    const switchTampilkanLogo = document.getElementById('switchTampilkanLogo');
    const inputLogoFile = document.getElementById('inputLogoFile');

    const inputFooter1 = document.getElementById('inputFooter1');
    const inputFooter2 = document.getElementById('inputFooter2');
    const inputCatatanKaki = document.getElementById('inputCatatanKaki');

    // 2. ELEMEN REVIEW / PREVIEW
    const receiptPaper = document.getElementById('receiptPaper');
    const thermalContent = document.getElementById('thermalContent');
    const previewNamaUsaha = document.getElementById('previewNamaUsaha');
    const previewSubJudul = document.getElementById('previewSubJudul');
    const previewCabang = document.getElementById('previewCabang');
    const previewAlamat = document.getElementById('previewAlamat');
    const previewKontak = document.getElementById('previewKontak');
    const previewTelepon = document.getElementById('previewTelepon');
    const previewSosmed = document.getElementById('previewSosmed');
    const previewPesanHeader = document.getElementById('previewPesanHeader');
    const previewLogoWrapper = document.getElementById('previewLogoWrapper');
    const previewLogoImg = document.getElementById('previewLogoImg');

    const previewFooter1 = document.getElementById('previewFooter1');
    const previewFooter2 = document.getElementById('previewFooter2');
    const previewCatatanKaki = document.getElementById('previewCatatanKaki');

    const labelQuickPaper = document.getElementById('labelQuickPaper');

    // Cabang default dari sampel jika mode otomatis
    const sampleCabangNama = @json($sampleData['nama_cabang']);
    const sampleAlamat = @json($sampleData['alamat_cabang']);
    const sampleTelepon = @json($sampleData['kontak_cabang']);

    // 3. EVENT LISTENERS REAL-TIME TEXT INPUTS
    if (inputNamaUsaha) {
        inputNamaUsaha.addEventListener('input', function () {
            previewNamaUsaha.textContent = this.value.trim() || 'PRESSENT STUDIO & CAFE';
        });
    }

    if (inputSubJudul) {
        inputSubJudul.addEventListener('input', function () {
            if (this.value.trim()) {
                previewSubJudul.style.display = 'block';
                previewSubJudul.textContent = this.value.trim();
            } else {
                previewSubJudul.style.display = 'none';
            }
        });
    }

    if (selectModeCabang) {
        selectModeCabang.addEventListener('change', function () {
            if (this.value === 'manual') {
                if (wrapperCabangManual) wrapperCabangManual.style.display = 'block';
                previewCabang.textContent = inputCabangManual.value.trim() || 'Cabang Studio';
            } else {
                if (wrapperCabangManual) wrapperCabangManual.style.display = 'none';
                previewCabang.textContent = sampleCabangNama;
            }
        });
    }

    if (inputCabangManual) {
        inputCabangManual.addEventListener('input', function () {
            if (selectModeCabang.value === 'manual') {
                previewCabang.textContent = this.value.trim() || 'Cabang Studio';
            }
        });
    }

    if (inputAlamat) {
        inputAlamat.addEventListener('input', function () {
            previewAlamat.textContent = this.value.trim() || sampleAlamat;
        });
    }

    if (inputTelepon) {
        inputTelepon.addEventListener('input', function () {
            previewTelepon.textContent = this.value.trim() || sampleTelepon;
        });
    }

    if (inputSosmed) {
        inputSosmed.addEventListener('input', function () {
            if (this.value.trim()) {
                previewSosmed.style.display = 'block';
                previewSosmed.textContent = this.value.trim();
            } else {
                previewSosmed.style.display = 'none';
            }
        });
    }

    if (inputPesanHeader) {
        inputPesanHeader.addEventListener('input', function () {
            if (this.value.trim()) {
                previewPesanHeader.style.display = 'block';
                previewPesanHeader.textContent = `"${this.value.trim()}"`;
            } else {
                previewPesanHeader.style.display = 'none';
            }
        });
    }

    if (inputFooter1) {
        inputFooter1.addEventListener('input', function () {
            previewFooter1.textContent = this.value.trim() || 'TERIMA KASIH ATAS KUNJUNGAN ANDA';
        });
    }

    if (inputFooter2) {
        inputFooter2.addEventListener('input', function () {
            previewFooter2.textContent = this.value.trim() || '';
        });
    }

    if (inputCatatanKaki) {
        inputCatatanKaki.addEventListener('input', function () {
            if (this.value.trim()) {
                previewCatatanKaki.style.display = 'block';
                previewCatatanKaki.textContent = `* ${this.value.trim()}`;
            } else {
                previewCatatanKaki.style.display = 'none';
            }
        });
    }

    // 4. EVENT LISTENER UNTUK LOGO & FILE PREVIEW
    if (switchTampilkanLogo) {
        switchTampilkanLogo.addEventListener('change', function () {
            previewLogoWrapper.style.display = this.checked ? 'block' : 'none';
        });
    }

    if (inputLogoFile) {
        inputLogoFile.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewLogoImg.src = e.target.result;
                    previewLogoWrapper.style.display = 'block';
                    if (switchTampilkanLogo) switchTampilkanLogo.checked = true;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 5. SWITCH CHECKBOX VISIBILITY MAPPINGS
    const visibilityMap = {
        'swCabang': 'previewCabang',
        'swAlamat': 'previewAlamat',
        'swKontak': 'previewKontak',
        'swNoNota': 'previewRowNoNota',
        'swWaktu': 'previewRowWaktu',
        'swKasir': 'previewRowKasir',
        'swPelanggan': 'previewRowPelanggan',
        'swDetailRoom': 'previewRowDetailRoom',
        'swDetailPenonton': 'previewRowDetailPenonton',
        'swDetailCafe': 'previewSectionCafe',
        'swDiskon': 'previewRowDiskon',
        'swMetodeBayar': 'previewRowMetode',
        'swKembalian': 'previewRowKembalian',
        'swBarcode': 'previewBarcode',
        'swWaktuCetak': 'previewWaktuCetak'
    };

    Object.keys(visibilityMap).forEach(switchId => {
        const sw = document.getElementById(switchId);
        const target = document.getElementById(visibilityMap[switchId]);
        if (sw && target) {
            sw.addEventListener('change', function () {
                target.style.display = this.checked ? '' : 'none';
                if (switchId === 'swMetodeBayar') {
                    const rowUang = document.getElementById('previewRowUangDiterima');
                    if (rowUang) rowUang.style.display = this.checked ? '' : 'none';
                }
            });
        }
    });

    // 6. TOGGLE UKURAN KERTAS & FONT SIZE
    function setPaperWidth(width) {
        if (width === '58mm') {
            receiptPaper.style.width = '280px';
            if (labelQuickPaper) labelQuickPaper.textContent = '58mm';
            document.getElementById('paper58').checked = true;
            document.getElementById('boxPaper58').classList.add('border-emerald', 'bg-emerald-subtle');
            document.getElementById('boxPaper80').classList.remove('border-emerald', 'bg-emerald-subtle');
        } else {
            receiptPaper.style.width = '360px';
            if (labelQuickPaper) labelQuickPaper.textContent = '80mm';
            document.getElementById('paper80').checked = true;
            document.getElementById('boxPaper80').classList.add('border-emerald', 'bg-emerald-subtle');
            document.getElementById('boxPaper58').classList.remove('border-emerald', 'bg-emerald-subtle');
        }
    }

    document.querySelectorAll('.radio-paper').forEach(radio => {
        radio.addEventListener('change', function () {
            setPaperWidth(this.value);
        });
    });

    const btnTogglePaperQuick = document.getElementById('btnTogglePaperQuick');
    if (btnTogglePaperQuick) {
        btnTogglePaperQuick.addEventListener('click', function () {
            const current = receiptPaper.style.width === '280px' ? '58mm' : '80mm';
            const next = current === '80mm' ? '58mm' : '80mm';
            setPaperWidth(next);
        });
    }

    document.querySelectorAll('.radio-fontsize').forEach(radio => {
        radio.addEventListener('change', function () {
            const size = this.value === 'small' ? '11px' : (this.value === 'large' ? '13px' : '12px');
            thermalContent.style.fontSize = size;
        });
    });

    // 7. PRINT PREVIEW FUNCTION
    window.printThermalPreview = function () {
        const previewEl = document.getElementById('receiptPaper');
        if (!previewEl) return;

        const printWindow = window.open('', '_blank', 'width=450,height=650');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test Cetak Struk POS</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', monospace; }
                    body { background: white; padding: 15px; display: flex; justify-content: center; }
                    .thermal-receipt { width: ${receiptPaper.style.width}; }
                    .thermal-tear-top, .thermal-tear-bottom { display: none; }
                    .thermal-row { display: flex; justify-content: space-between; margin-bottom: 2.5px; }
                    .thermal-divider { border-top: 1px dashed #000; margin: 7px 0; }
                    .thermal-double-divider { border-top: 2px dashed #000; margin: 7px 0; }
                    .text-center { text-align: center; }
                    .text-start { text-align: left; }
                    .fw-bold { font-weight: bold; }
                    .text-muted { color: #555; }
                </style>
            </head>
            <body>
                ${previewEl.outerHTML}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(() => window.close(), 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    };

    const btnTestPrint = document.getElementById('btnTestPrint');
    if (btnTestPrint) {
        btnTestPrint.addEventListener('click', window.printThermalPreview);
    }

});
</script>
@endsection
