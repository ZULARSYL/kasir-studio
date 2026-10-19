@extends('template.index')

@section('title', 'Input Faktur Barang Masuk | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.cafe.faktur.index') }}" class="text-decoration-none text-muted">Faktur Barang</a></li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Input Faktur Baru</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Input Faktur Barang (Pembelian / Grosir)</h3>
            <p class="text-muted small mb-0">Pilih outlet cabang, masukkan barang dari master barang, atur satuan, harga modal awal, dan harga jual kasir.</p>
        </div>
        <div>
            <a href="{{ route('admin.cafe.faktur.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Faktur
            </a>
        </div>
    </div>

    <!-- Error / Validation Alert Banner -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-x fs-4 text-danger"></i>
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

    <form action="{{ route('admin.cafe.faktur.store') }}" method="POST" id="formFakturBarang">
        @csrf

        <!-- Card Informasi Utama Faktur & Outlet -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold text-heading mb-0">
                    <i class="bi bi-info-circle-fill text-emerald me-2"></i>Informasi Faktur & Outlet Cabang
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Nomor Faktur -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nomor Faktur / Nota <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_faktur" value="{{ old('nomor_faktur', $nomorFaktur) }}" class="form-control rounded-3 font-monospace fw-bold" required>
                    </div>

                    <!-- Outlet Cabang (Data Master) -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Pilih Outlet Cabang <span class="text-danger">*</span></label>
                        <select name="toko_cabang_id" class="form-select rounded-3" required>
                            <option value="" disabled selected>-- Pilih Toko Cabang --</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}" {{ old('toko_cabang_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Faktur -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tanggal Faktur / Masuk <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_faktur" value="{{ old('tanggal_faktur', date('Y-m-d')) }}" class="form-control rounded-3" required>
                    </div>

                    <!-- Pemasok / Grosir -->
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nama Pemasok / Toko Grosir</label>
                        <input type="text" name="pemasok" value="{{ old('pemasok') }}" class="form-control rounded-3" placeholder="Contoh: Grosir Sumber Rezeki Pekanbaru">
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Catatan Pembelian (Opsional)</label>
                        <textarea name="catatan" rows="2" class="form-control rounded-3" placeholder="Keterangan kondisi barang, nomor kontak sales, dsb...">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Rincian Barang Pembelian / Grosir -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title fw-bold text-heading mb-0">
                        <i class="bi bi-box2-fill text-emerald me-2"></i>Daftar Barang Masuk & Penetapan Harga
                    </h5>
                    <p class="text-muted small mb-0">Klik tombol Tambah Barang dari Master untuk memilih barang yang dibeli.</p>
                </div>
                <div>
                    <button type="button" class="btn btn-emerald rounded-3 px-3 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalPilihBarang">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                        <span class="fw-semibold">Tambah Barang dari Master</span>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabelItemFaktur">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-muted small fw-bold" style="width: 50px;">NO</th>
                                <th class="text-muted small fw-bold" style="min-width: 220px;">NAMA BARANG</th>
                                <th class="text-muted small fw-bold text-center" style="width: 120px;">QTY</th>
                                <th class="text-muted small fw-bold" style="width: 150px;">SATUAN</th>
                                <th class="text-muted small fw-bold" style="width: 160px;">HARGA AWAL (BELI)</th>
                                <th class="text-muted small fw-bold" style="width: 170px;">HARGA PENJUALAN</th>
                                <th class="text-muted small fw-bold text-end" style="width: 150px;">SUBTOTAL</th>
                                <th class="text-end pe-4 text-muted small fw-bold" style="width: 60px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="containerItemFaktur">
                            <tr id="emptyRow">
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-cart-x fs-1"></i></div>
                                    <h6 class="fw-bold text-dark">Belum ada barang di dalam faktur</h6>
                                    <p class="text-muted small mb-3">Silakan klik tombol <strong>Tambah Barang dari Master</strong> di atas untuk mulai memasukkan barang.</p>
                                    <button type="button" class="btn btn-sm btn-emerald rounded-3" data-bs-toggle="modal" data-bs-target="#modalPilihBarang">
                                        <i class="bi bi-plus-circle me-1"></i> Pilih Barang Sekarang
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="ps-4 fw-bold text-dark text-uppercase small">Ringkasan Total Faktur:</td>
                                <td colspan="2" class="fw-bold text-muted text-end" id="labelTotalItem">0 Barang Terdaftar</td>
                                <td class="text-end fw-bold text-emerald fs-5 font-monospace" id="previewTotalFaktur">Rp 0</td>
                                <td class="pe-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-emerald rounded-3 px-3 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalPilihBarang">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Barang Lainnya</span>
                    </button>
                    <span class="text-muted small">Anda dapat menambahkan lebih dari 1 barang sekaligus.</span>
                </div>
                <div>
                    <button type="submit" class="btn btn-emerald px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" id="btnSimpanFaktur">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span class="fw-bold">Simpan Faktur Barang</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Dialog Pilih Barang dari Master Barang -->
<div class="modal fade" id="modalPilihBarang" tabindex="-1" aria-labelledby="modalPilihBarangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-heading" id="modalPilihBarangLabel">Pilih Barang dari Master Data</h5>
                    <p class="text-muted small mb-0">Pilih barang yang dibeli untuk dimasukkan ke dalam daftar faktur.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Search Box di dalam Modal -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="inputCariBarangModal" class="form-control border-start-0" placeholder="Ketik nama barang untuk mencari...">
                </div>

                <!-- Daftar Barang Master -->
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-hover align-middle mb-0" id="tabelMasterBarangModal">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 50px;" class="text-center">PILIH</th>
                                <th>NAMA BARANG</th>
                                <th>KATEGORI</th>
                                <th class="text-end" style="width: 100px;">AKSI CEPAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barangs as $b)
                                <tr class="row-pilih-barang" data-id="{{ $b->id }}" data-nama="{{ $b->nama_barang }}">
                                    <td class="text-center">
                                        <input class="form-check-input radio-pilih-barang" type="radio" name="selected_modal_barang" value="{{ $b->id }}">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $b->nama_barang }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-muted border px-2 py-1">
                                            {{ $b->menuCafe?->nama_kategori ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-emerald rounded-3 btn-pilih-langsung" data-id="{{ $b->id }}" data-nama="{{ $b->nama_barang }}">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Belum ada data di master barang. Silakan tambahkan master barang terlebih dahulu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-emerald rounded-3 px-4" id="btnOkModalPilihBarang">
                    <i class="bi bi-check2-circle me-1"></i> OK / Masukkan ke Faktur
                </button>
            </div>
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
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // TEMPLATE DROPDOWN SATUAN BARANG DARI MASTER
    // -------------------------------------------------------------
    const optionsSatuanHTML = `
        <option value="" disabled>-- Pilih Satuan --</option>
        @foreach($satuans as $s)
            <option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>
        @endforeach
    `;

    const container = document.getElementById('containerItemFaktur');
    const emptyRow = document.getElementById('emptyRow');
    const previewTotalFaktur = document.getElementById('previewTotalFaktur');
    const labelTotalItem = document.getElementById('labelTotalItem');

    let itemIndexCounter = 0;

    // -------------------------------------------------------------
    // LIVE FILTER DI DALAM MODAL PILIH BARANG
    // -------------------------------------------------------------
    const inputCariModal = document.getElementById('inputCariBarangModal');
    if (inputCariModal) {
        inputCariModal.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#tabelMasterBarangModal tbody tr.row-pilih-barang');
            rows.forEach(row => {
                const nama = row.getAttribute('data-nama').toLowerCase();
                if (nama.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Klik pada baris modal otomatis memilih radio button
    document.querySelectorAll('#tabelMasterBarangModal tbody tr.row-pilih-barang').forEach(row => {
        row.addEventListener('click', function (e) {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') {
                const radio = this.querySelector('.radio-pilih-barang');
                if (radio) {
                    radio.checked = true;
                }
            }
        });
    });

    // -------------------------------------------------------------
    // FUNGSI MENAMBAH BARIS BARANG KE TABEL FAKTUR
    // -------------------------------------------------------------
    function tambahBarangKeFaktur(barangId, namaBarang, satuanIdDefault) {
        if (!barangId) return;

        // Hilangkan empty placeholder
        if (emptyRow) {
            emptyRow.style.display = 'none';
        }

        const idx = itemIndexCounter++;
        const tr = document.createElement('tr');
        tr.className = 'item-faktur-row';
        tr.setAttribute('data-index', idx);

        tr.innerHTML = `
            <td class="ps-4 fw-semibold text-muted cell-no"></td>
            <td>
                <div class="fw-bold text-dark">${namaBarang}</div>
                <input type="hidden" name="items[${idx}][barang_id]" value="${barangId}">
            </td>
            <td>
                <input type="number" step="any" min="0.01" name="items[${idx}][qty]" class="form-control form-control-sm text-center input-qty rounded-2" value="1" required>
            </td>
            <td>
                <select name="items[${idx}][satuan_barang_id]" class="form-select form-select-sm select-satuan rounded-2" required>
                    ${optionsSatuanHTML}
                </select>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted" style="font-size: 0.75rem;">Rp</span>
                    <input type="number" step="any" min="0" name="items[${idx}][harga_awal]" class="form-control input-harga-awal rounded-end-2 text-end" placeholder="0" value="0" required>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted" style="font-size: 0.75rem;">Rp</span>
                    <input type="number" step="any" min="0" name="items[${idx}][harga_jual]" class="form-control input-harga-jual rounded-end-2 text-end" placeholder="0" value="0" required>
                </div>
            </td>
            <td class="text-end fw-bold text-emerald font-monospace cell-subtotal">
                Rp 0
            </td>
            <td class="text-end pe-4">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-3 btn-hapus-item" title="Hapus Barang Ini">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);

        // Set default satuan barang jika ada
        const selectSatuan = tr.querySelector('.select-satuan');
        if (selectSatuan && satuanIdDefault) {
            selectSatuan.value = satuanIdDefault;
        }

        updateNomorDanKalkulasi();
    }

    // -------------------------------------------------------------
    // EVENT: KLIK TOMBOL "OK / MASUKKAN KE FAKTUR" DARI MODAL
    // -------------------------------------------------------------
    const btnOkModal = document.getElementById('btnOkModalPilihBarang');
    if (btnOkModal) {
        btnOkModal.addEventListener('click', function () {
            const selectedRadio = document.querySelector('#tabelMasterBarangModal input.radio-pilih-barang:checked');
            if (!selectedRadio) {
                alert('Silakan pilih salah satu barang terlebih dahulu.');
                return;
            }

            const row = selectedRadio.closest('.row-pilih-barang');
            const id = row.getAttribute('data-id');
            const nama = row.getAttribute('data-nama');
            const satuanId = row.getAttribute('data-satuan-id');

            tambahBarangKeFaktur(id, nama, satuanId);

            // Tutup modal
            const modalEl = document.getElementById('modalPilihBarang');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            // Uncheck radio untuk pemilihan selanjutnya
            selectedRadio.checked = false;
        });
    }

    // Event: Tombol "Pilih" Cepat di Modal
    document.querySelectorAll('.btn-pilih-langsung').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');
            const satuanId = this.getAttribute('data-satuan-id');

            tambahBarangKeFaktur(id, nama, satuanId);

            const modalEl = document.getElementById('modalPilihBarang');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    });

    // -------------------------------------------------------------
    // EVENT: HAPUS BARIS ITEM
    // -------------------------------------------------------------
    container.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.btn-hapus-item');
        if (removeBtn) {
            const tr = removeBtn.closest('.item-faktur-row');
            if (tr) {
                tr.remove();
                updateNomorDanKalkulasi();
            }
        }
    });

    // -------------------------------------------------------------
    // EVENT: PERUBAHAN QTY ATAU HARGA AWAL (KALKULASI SUBTOTAL & GRAND TOTAL)
    // -------------------------------------------------------------
    container.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-qty') || e.target.classList.contains('input-harga-awal')) {
            updateNomorDanKalkulasi();
        }
    });

    // -------------------------------------------------------------
    // FUNGSI UPDATE NOMOR URUT, SUBTOTAL, DAN TOTAL FAKTUR
    // -------------------------------------------------------------
    function updateNomorDanKalkulasi() {
        const rows = container.querySelectorAll('.item-faktur-row');
        let grandTotal = 0;

        if (rows.length === 0) {
            if (emptyRow) {
                emptyRow.style.display = '';
            }
        } else {
            if (emptyRow) {
                emptyRow.style.display = 'none';
            }
        }

        rows.forEach((row, i) => {
            // Update Nomor Urut
            const cellNo = row.querySelector('.cell-no');
            if (cellNo) cellNo.textContent = (i + 1);

            // Hitung Subtotal per baris
            const qty = parseFloat(row.querySelector('.input-qty')?.value) || 0;
            const hargaAwal = parseFloat(row.querySelector('.input-harga-awal')?.value) || 0;
            const subtotal = Math.round(qty * hargaAwal);

            grandTotal += subtotal;

            const cellSubtotal = row.querySelector('.cell-subtotal');
            if (cellSubtotal) {
                cellSubtotal.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            }
        });

        // Update Total Faktur di Footer
        if (previewTotalFaktur) {
            previewTotalFaktur.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
        if (labelTotalItem) {
            labelTotalItem.textContent = `${rows.length} Macam Barang`;
        }
    }

    // -------------------------------------------------------------
    // VALIDASI FORM SEBELUM SUBMIT (MINIMAL 1 ITEM)
    // -------------------------------------------------------------
    const formFaktur = document.getElementById('formFakturBarang');
    if (formFaktur) {
        formFaktur.addEventListener('submit', function (e) {
            const rows = container.querySelectorAll('.item-faktur-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Peringatan: Faktur belum memiliki barang! Klik tombol "+ Tambah Barang dari Master" untuk memasukkan barang.');
            }
        });
    }
});
</script>
@endpush
