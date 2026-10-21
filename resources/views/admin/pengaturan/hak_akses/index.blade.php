@extends('template.index')

@section('title', 'Pengaturan Hak Akses | Pressent Studio & Cafe')

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

    <!-- PAGE HEADER -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h4 class="fw-bold mb-0 text-heading">Pengaturan Hak Akses User</h4>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-key-fill me-1"></i>Role &amp; Jabatan
                            </span>
                        </div>
                        <p class="text-muted small mb-0">
                            Atur izin menu apa saja yang dapat dibuka oleh user.
                        </p>
                    </div>
                </div>

                <!-- Info Target Terpilih -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="bg-light px-3 py-2 rounded-3 border">
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">Konfigurasi Saat Ini:</span>
                        <strong class="text-dark d-flex align-items-center gap-1.5 font-monospace">
                            <i class="bi {{ $tipe === 'role' ? 'bi-person-badge-fill text-primary' : 'bi-award-fill text-warning' }}"></i>
                            {{ $targetName }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB PILIHAN: ROLE SISTEM ATAU JABATAN PEGAWAI -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
        <div class="row g-3 align-items-center">
            
            <!-- Tombol Navigasi Tipe (Role vs Jabatan) -->
            {{-- <div class="col-12 col-md-5 col-lg-4">
                <label class="form-label small fw-semibold text-muted mb-1.5">Pilih Kategori Otoritas</label>
                <div class="btn-group w-100" role="group">
                    <a href="{{ route('admin.pengaturan.hak-akses', ['tipe' => 'jabatan']) }}" 
                       class="btn {{ $tipe === 'jabatan' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-end-3 fw-semibold">
                        <i class="bi bi-briefcase me-1"></i> Berdasarkan JabatEan
                    </a>
                </div>
            </div> --}}

            <!-- Selector Detail Target -->
            <div class="col-12 col-md-7 col-lg-8">
                @if($tipe === 'role')
                    <label class="form-label small fw-semibold text-muted mb-1.5">Pilih Role Sistem</label>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.pengaturan.hak-akses', ['tipe' => 'role', 'role' => 'admin']) }}"
                           class="btn btn-sm rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 {{ $role === 'admin' ? 'btn-dark' : 'btn-light border' }}">
                            <i class="bi bi-shield-check text-primary"></i>
                            <span>Administrator</span>
                            <span class="badge bg-secondary-subtle text-dark rounded-pill">{{ $stats['total_admin'] }} User</span>
                        </a>

                        <a href="{{ route('admin.pengaturan.hak-akses', ['tipe' => 'role', 'role' => 'kasir']) }}"
                           class="btn btn-sm rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 {{ $role === 'kasir' ? 'btn-dark' : 'btn-light border' }}">
                            <i class="bi bi-calculator text-success"></i>
                            <span>Kasir POS</span>
                            <span class="badge bg-secondary-subtle text-dark rounded-pill">{{ $stats['total_kasir'] }} User</span>
                        </a>
                    </div>
                @else
                    <label class="form-label small fw-semibold text-muted mb-1.5">Pilih Jabatan Pegawai</label>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($jabatans as $jb)
                            <a href="{{ route('admin.pengaturan.hak-akses', ['tipe' => 'jabatan', 'jabatan_id' => $jb->id]) }}"
                               class="btn btn-sm rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 {{ $jabatanId == $jb->id ? 'btn-dark' : 'btn-light border' }}">
                                <i class="bi bi-award text-warning"></i>
                                <span>{{ $jb->nama_jabatan }}</span>
                            </a>
                        @empty
                            <span class="text-muted small">Belum ada data master jabatan. Silakan buat di menu Kepegawaian &rarr; Jabatan.</span>
                        @endforelse
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- FORM HAK AKSES DENGAN CEKLIS PER MENU & SUBMENU -->
    <form action="{{ route('admin.pengaturan.hak-akses.update') }}" method="POST" id="formHakAkses">
        @csrf
        <input type="hidden" name="tipe" value="{{ $tipe }}">
        <input type="hidden" name="role" value="{{ $role }}">
        <input type="hidden" name="jabatan_id" value="{{ $jabatanId }}">

        <!-- TOOLBAR AKSI MASSAL (SEARCH & SELECT ALL) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
            <div class="row g-2 align-items-center justify-content-between">
                
                <!-- Pencarian Cepat Menu / Submenu -->
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="searchMenuInput" class="form-control border-start-0 py-2" placeholder="Cari nama menu atau submenu...">
                    </div>
                </div>

                <!-- Tombol Centang Semua & Hapus Semua -->
                <div class="col-12 col-md-6 col-lg-7 d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-2 fw-semibold" id="btnSelectAll">
                        <i class="bi bi-check-all me-1"></i>Centang Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-2 fw-semibold" id="btnDeselectAll">
                        <i class="bi bi-dash-circle me-1"></i>Hapus Semua
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>

            </div>
        </div>

        @if($tipe === 'role' && $role === 'admin')
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
                <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
                <div class="small">
                    <strong>Catatan Perlindungan Administrator:</strong> Role <strong>Administrator</strong> selalu memiliki akses penuh ke menu Hak Akses dan Dashboard Utama guna mencegah terkuncinya sistem secara permanen.
                </div>
            </div>
        @endif

        <!-- DAFTAR MODUL MENU & SUBMENU (CARDS) -->
        <div class="row g-4" id="menuModuleContainer">
            @foreach($menuStructure as $moduleKey => $module)
                @php
                    $childCount = count($module['menus']);
                    $moduleCheckedCount = 0;
                    foreach(array_keys($module['menus']) as $mKey) {
                        if (in_array($mKey, $currentPermissions)) {
                            $moduleCheckedCount++;
                        }
                    }
                    $isAllModuleChecked = $childCount > 0 && $moduleCheckedCount === $childCount;
                @endphp

                <div class="col-12 module-card-wrapper" data-module="{{ $moduleKey }}">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white module-card">
                        
                        <!-- CARD HEADER DENGAN MASTER CHECKBOX MODUL -->
                        <div class="card-header bg-light-subtle border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark fs-6">{{ $module['title'] }}</h5>
                                    <span class="text-muted small" style="font-size: 0.75rem;">Kategori Menu: <code>{{ $moduleKey }}</code></span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <!-- Counter Per Modul -->
                                <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1.5 font-monospace module-counter-badge" id="counter_{{ $moduleKey }}">
                                    {{ $moduleCheckedCount }} / {{ $childCount }} Submenu
                                </span>

                                <!-- Master Checkbox Modul -->
                                <div class="form-check form-switch m-0 d-flex align-items-center gap-1.5">
                                    <input class="form-check-input module-master-checkbox" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="master_check_{{ $moduleKey }}"
                                           data-module="{{ $moduleKey }}"
                                           {{ $isAllModuleChecked ? 'checked' : '' }}
                                           style="width: 2.4em; height: 1.25em; cursor: pointer;">
                                    <label class="form-check-label small fw-semibold text-muted user-select-none" for="master_check_{{ $moduleKey }}">
                                        Pilih Semua Modul Ini
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- CARD BODY: GRID CEKLIS PER SUBMENU -->
                        <div class="card-body p-3 p-md-4">
                            <div class="row g-3">
                                @foreach($module['menus'] as $submenuKey => $item)
                                    @php
                                        $isChecked = in_array($submenuKey, $currentPermissions);
                                        $isDisabled = ($tipe === 'role' && $role === 'admin' && in_array($submenuKey, ['pengaturan.hak-akses', 'dashboard']));
                                    @endphp

                                    <div class="col-12 col-md-6 col-xl-4 submenu-item-wrapper" data-label="{{ strtolower($item['label'] . ' ' . $submenuKey) }}">
                                        <div class="p-3 rounded-4 border bg-white submenu-box h-100 transition-all {{ $isChecked ? 'border-primary bg-primary-subtle-5' : '' }}">
                                            <div class="form-check d-flex align-items-start gap-3 m-0 p-0">
                                                <input class="form-check-input flex-shrink-0 mt-1 menu-checkbox child-of-{{ $moduleKey }}" 
                                                       type="checkbox" 
                                                       name="menus[]" 
                                                       value="{{ $submenuKey }}" 
                                                       id="check_{{ str_replace(['.', '-', '_'], '', $submenuKey) }}"
                                                       data-module="{{ $moduleKey }}"
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       {{ $isDisabled ? 'checked onclick="return false;"' : '' }}
                                                       style="width: 1.25rem; height: 1.25rem; cursor: pointer;">
                                                
                                                <label class="form-check-label flex-grow-1 user-select-none" for="check_{{ str_replace(['.', '-', '_'], '', $submenuKey) }}" style="cursor: pointer;">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <strong class="text-heading small d-flex align-items-center gap-2.5">
                                                            <span>{{ $item['label'] }}</span>
                                                        </strong>
                                                        @if($isDisabled)
                                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.65rem;">Wajib</span>
                                                        @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <!-- STICKY ACTION BAR DI BAWAH -->
        <div class="card border-0 shadow-lg rounded-4 mt-4 bg-white sticky-bottom p-3" style="bottom: 16px; z-index: 1020;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success fs-4"></i>
                    <div>
                        <strong class="text-dark small d-block">Konfigurasi Hak Akses Siap Disimpan</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Perubahan hak akses akan langsung aktif untuk setiap user dengan {{ $targetName }}.</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->full() }}" class="btn btn-light rounded-3 px-3 py-2 fw-semibold">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i>Simpan Hak Akses Sekarang
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>

{{-- CSS KHUSUS TAMPILAN CHECKLIST HAK AKSES --}}
<style>
    .bg-primary-subtle-5 {
        background-color: rgba(0, 123, 139, 0.04) !important;
    }
    .submenu-box {
        transition: all 0.2s ease-in-out;
    }
    .submenu-box:hover {
        border-color: #007B8B !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .menu-checkbox:checked + label strong {
        color: #007B8B !important;
    }
    .module-card {
        border: 1px solid #e2e8f0 !important;
    }
</style>

{{-- INTERACTIVE JAVASCRIPT LOGIC --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchMenuInput');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const totalCheckedCounter = document.getElementById('totalCheckedCounter');
    const menuCheckboxes = document.querySelectorAll('.menu-checkbox');
    const masterCheckboxes = document.querySelectorAll('.module-master-checkbox');

    // 1. Fungsi Kalkulasi Total & Per Modul
    function updateCounters() {
        let totalChecked = 0;
        
        masterCheckboxes.forEach(master => {
            const moduleKey = master.getAttribute('data-module');
            const children = document.querySelectorAll(`.child-of-${moduleKey}`);
            let moduleChecked = 0;

            children.forEach(child => {
                if (child.checked) {
                    moduleChecked++;
                    totalChecked++;
                }
            });

            // Update badge counter per modul
            const counterBadge = document.getElementById(`counter_${moduleKey}`);
            if (counterBadge) {
                counterBadge.textContent = `${moduleChecked} / ${children.length} Submenu`;
            }

            // Update state master checkbox
            if (children.length > 0) {
                master.checked = (moduleChecked === children.length);
                master.indeterminate = (moduleChecked > 0 && moduleChecked < children.length);
            }
        });

        // Update total counter
        if (totalCheckedCounter) {
            totalCheckedCounter.textContent = totalChecked;
        }
    }

    // Inisialisasi hitungan awal
    updateCounters();

    // 2. Event Toggle Per Submenu Checkbox
    menuCheckboxes.forEach(chk => {
        chk.addEventListener('change', function () {
            const box = this.closest('.submenu-box');
            if (box) {
                if (this.checked) {
                    box.classList.add('border-primary', 'bg-primary-subtle-5');
                } else {
                    box.classList.remove('border-primary', 'bg-primary-subtle-5');
                }
            }
            updateCounters();
        });
    });

    // 3. Event Toggle Master Checkbox Modul
    masterCheckboxes.forEach(master => {
        master.addEventListener('change', function () {
            const moduleKey = this.getAttribute('data-module');
            const isChecked = this.checked;
            const children = document.querySelectorAll(`.child-of-${moduleKey}`);

            children.forEach(child => {
                // Hindari uncheck yang disabled (safeguard)
                if (!child.disabled && child.getAttribute('onclick') !== 'return false;') {
                    child.checked = isChecked;
                    const box = child.closest('.submenu-box');
                    if (box) {
                        if (isChecked) {
                            box.classList.add('border-primary', 'bg-primary-subtle-5');
                        } else {
                            box.classList.remove('border-primary', 'bg-primary-subtle-5');
                        }
                    }
                }
            });

            updateCounters();
        });
    });

    // 4. Centang Semua (Select All)
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function () {
            menuCheckboxes.forEach(chk => {
                chk.checked = true;
                const box = chk.closest('.submenu-box');
                if (box) box.classList.add('border-primary', 'bg-primary-subtle-5');
            });
            masterCheckboxes.forEach(m => {
                m.checked = true;
                m.indeterminate = false;
            });
            updateCounters();
        });
    }

    // 5. Hapus Semua (Deselect All)
    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function () {
            menuCheckboxes.forEach(chk => {
                if (!chk.disabled && chk.getAttribute('onclick') !== 'return false;') {
                    chk.checked = false;
                    const box = chk.closest('.submenu-box');
                    if (box) box.classList.remove('border-primary', 'bg-primary-subtle-5');
                }
            });
            masterCheckboxes.forEach(m => {
                m.checked = false;
                m.indeterminate = false;
            });
            updateCounters();
        });
    }

    // 6. Pencarian Realtime Menu & Submenu
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            const moduleWrappers = document.querySelectorAll('.module-card-wrapper');

            moduleWrappers.forEach(mod => {
                const submenus = mod.querySelectorAll('.submenu-item-wrapper');
                let matchInModule = 0;

                submenus.forEach(sub => {
                    const label = sub.getAttribute('data-label') || '';
                    if (!q || label.includes(q)) {
                        sub.classList.remove('d-none');
                        matchInModule++;
                    } else {
                        sub.classList.add('d-none');
                    }
                });

                if (!q || matchInModule > 0) {
                    mod.classList.remove('d-none');
                } else {
                    mod.classList.add('d-none');
                }
            });
        });
    }
});
</script>
@endsection
