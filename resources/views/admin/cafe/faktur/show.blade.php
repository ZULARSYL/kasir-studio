@extends('template.index')

@section('title', 'Detail Faktur: ' . $faktur->nomor_faktur . ' | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.cafe.faktur.index') }}" class="text-decoration-none text-muted">Faktur Barang</a></li>
                    <li class="breadcrumb-item active text-emerald fw-semibold" aria-current="page">Detail Faktur</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-heading">Detail Faktur Pembelian: {{ $faktur->nomor_faktur }}</h3>
            <p class="text-muted small mb-0">Rincian barang masuk grosir, harga awal beli, dan harga penjualan kasir.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cafe.faktur.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-emerald px-3 py-2 rounded-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Faktur
            </button>
        </div>
    </div>

    <!-- Invoice Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" id="printableInvoice">
        <!-- Invoice Header -->
        <div class="card-header bg-white border-bottom p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="p-2 bg-emerald-subtle text-emerald rounded-3">
                            <i class="bi bi-receipt-cutoff fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-0">{{ $faktur->nomor_faktur }}</h4>
                            <span class="text-muted small">Faktur Pembelian / Barang Masuk</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-emerald-subtle text-emerald border border-emerald px-3 py-2 fs-6">
                        Total Faktur: {{ $faktur->total_nominal_format }}
                    </span>
                </div>
            </div>
            <hr class="my-3 text-muted opacity-25">
            <div class="row g-3 small">
                <div class="col-md-3">
                    <span class="text-muted d-block">Outlet Cabang Penerima:</span>
                    <strong class="text-dark fs-6">{{ $faktur->tokoCabang?->nama_cabang ?? '-' }}</strong>
                    <div class="text-muted">{{ $faktur->tokoCabang?->alamat ?? '' }}</div>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Tanggal Faktur:</span>
                    <strong class="text-dark fs-6">{{ $faktur->tanggal_faktur ? $faktur->tanggal_faktur->format('d F Y') : '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Pemasok / Toko Grosir:</span>
                    <strong class="text-dark fs-6">{{ $faktur->pemasok ?: 'Pembelian Grosir Umum' }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block">Petugas Input:</span>
                    <strong class="text-dark fs-6">{{ $faktur->user?->name ?? 'Admin Studio' }}</strong>
                </div>
                @if($faktur->catatan)
                    <div class="col-12 mt-2">
                        <span class="text-muted d-block">Catatan:</span>
                        <div class="p-2 bg-light rounded-3 text-dark">{{ $faktur->catatan }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-muted small fw-bold" style="width: 50px;">NO</th>
                            <th class="text-muted small fw-bold">NAMA BARANG</th>
                            <th class="text-muted small fw-bold text-center" style="width: 100px;">QTY</th>
                            <th class="text-muted small fw-bold" style="width: 120px;">SATUAN</th>
                            <th class="text-muted small fw-bold text-end" style="width: 160px;">HARGA AWAL (BELI)</th>
                            <th class="text-muted small fw-bold text-end" style="width: 160px;">HARGA PENJUALAN (KASIR)</th>
                            <th class="text-muted small fw-bold text-end pe-4" style="width: 160px;">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($faktur->details as $index => $detail)
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-heading">
                                        <i class="bi bi-box2 me-1 text-emerald"></i>{{ $detail->barang?->nama_barang ?? '-' }}
                                    </div>
                                </td>
                                <td class="text-center fw-bold text-dark font-monospace">
                                    {{ (float) $detail->qty == (int) $detail->qty ? (int) $detail->qty : $detail->qty }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $detail->satuanBarang?->nama_satuan ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace text-muted">
                                    {{ $detail->harga_awal_format }}
                                </td>
                                <td class="text-end font-monospace fw-bold text-dark">
                                    {{ $detail->harga_jual_format }}
                                </td>
                                <td class="text-end pe-4 font-monospace fw-bold text-emerald">
                                    {{ $detail->subtotal_format }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="ps-4 fw-bold text-uppercase text-dark">Total Keseluruhan Nilai Faktur:</td>
                            <td colspan="2" class="text-muted text-end fw-semibold">{{ $faktur->details->count() }} Macam Barang</td>
                            <td class="text-end pe-4 fw-bold text-emerald fs-5 font-monospace">
                                {{ $faktur->total_nominal_format }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
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
    .text-emerald {
        color: #059669 !important;
    }
    .border-emerald {
        border-color: #059669 !important;
    }
    .bg-emerald-subtle {
        background-color: #d1fae5 !important;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        #printableInvoice, #printableInvoice * {
            visibility: visible;
        }
        #printableInvoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endsection
