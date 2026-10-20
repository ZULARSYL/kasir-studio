<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian - Pressent Studio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333333;
        }
        .header-title {
            font-size: 16pt;
            font-weight: bold;
            color: #059669;
            text-align: center;
        }
        .header-subtitle {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .meta-table {
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px 6px;
        }
        table.data-table {
            border-collapse: collapse;
            width: 100%;
        }
        table.data-table th {
            background-color: #059669;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #047857;
            padding: 8px 6px;
            text-align: center;
        }
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .bg-total {
            background-color: #ecfdf5;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header-title">PRESSENT STUDIO & CAFE</div>
    <div class="header-subtitle">LAPORAN PEMBELIAN BARANG (PENGADAAN FAKTUR)</div>
    <br>

    <table class="meta-table">
        <tr>
            <td style="width: 130px;"><strong>Outlet Cabang</strong></td>
            <td>: {{ $namaCabang }}</td>
        </tr>
        <tr>
            <td><strong>Periode</strong></td>
            <td>: {{ $labelPeriode }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Export</strong></td>
            <td>: {{ $tanggalExport }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>No. Faktur</th>
                <th>Tanggal Faktur</th>
                <th>Outlet Cabang</th>
                <th>Pemasok / Supplier</th>
                <th>Rincian Barang</th>
                <th>Total Qty</th>
                <th>Total Nominal (Rp)</th>
                <th>Dicatat Oleh</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandQty = 0;
                $grandNominal = 0;
            @endphp
            @forelse($fakturs as $index => $faktur)
                @php
                    $sumQty = $faktur->details ? $faktur->details->sum('qty') : 0;
                    $grandQty += $sumQty;
                    $grandNominal += $faktur->total_nominal;

                    $itemsStr = '-';
                    if ($faktur->details && $faktur->details->count() > 0) {
                        $itemsStr = $faktur->details->map(function($d) {
                            $nama = $d->barang ? $d->barang->nama_barang : 'Barang';
                            $satuan = $d->satuanBarang ? $d->satuanBarang->nama_satuan : '';
                            return $nama . ' (' . (float)$d->qty . ' ' . $satuan . ' @ Rp ' . number_format($d->harga_awal, 0, ',', '.') . ')';
                        })->implode('; ');
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center fw-bold">{{ $faktur->nomor_faktur }}</td>
                    <td class="text-center">{{ $faktur->tanggal_faktur ? $faktur->tanggal_faktur->format('d/m/Y') : '-' }}</td>
                    <td>{{ $faktur->tokoCabang->nama_cabang ?? 'Outlet Umum' }}</td>
                    <td>{{ $faktur->pemasok ?: 'Supplier Umum' }}</td>
                    <td>{{ $itemsStr }}</td>
                    <td class="text-center">{{ (float)$sumQty }}</td>
                    <td class="text-end fw-bold">{{ number_format($faktur->total_nominal, 0, ',', '.') }}</td>
                    <td>{{ $faktur->user->name ?? $faktur->user->username ?? '-' }}</td>
                    <td>{{ $faktur->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data faktur pembelian barang untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($fakturs) > 0)
            <tfoot>
                <tr class="bg-total">
                    <td colspan="6" class="text-center">GRAND TOTAL</td>
                    <td class="text-center">{{ (float)$grandQty }}</td>
                    <td class="text-end">{{ number_format($grandNominal, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
