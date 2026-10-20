<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang - Pressent Studio</title>
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
            padding: 8px 10px;
            text-align: center;
        }
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 10px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .bg-grand-total {
            background-color: #d1fae5;
            font-weight: bold;
            color: #065f46;
            font-size: 11pt;
        }
    </style>
</head>
<body>

    <div class="header-title">PRESSENT STUDIO &amp; CAFE</div>
    <div class="header-subtitle">LAPORAN STOK BARANG &amp; TOTAL NILAI ASET</div>
    <br>

    <table class="meta-table">
        <tr>
            <td style="width: 150px;"><strong>Toko Cabang</strong></td>
            <td>: {{ $namaCabang }}</td>
        </tr>
        <tr>
            <td><strong>Periode</strong></td>
            <td>: {{ $labelPeriode }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Export</strong></td>
            <td>: {{ now()->translatedFormat('d F Y H:i:s') }}</td>
        </tr>
        <tr>
            <td><strong>Grand Total Aset</strong></td>
            <td>: Rp {{ number_format($grandTotalAset, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Grand Total Kuantitas</strong></td>
            <td>: {{ number_format($grandTotalStok, 0, ',', '.') }} Unit</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th style="text-align: left;">Nama Barang</th>
                <th style="text-align: left;">Kategori</th>
                <th style="text-align: left;">Outlet Cabang</th>
                <th style="text-align: center;">Satuan</th>
                <th style="text-align: center;">Banyaknya Stok di Sistem</th>
                <th style="text-align: right;">Harga Modal Rata-rata (Rp)</th>
                <th style="text-align: right;">Total Aset per Item (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupedItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->nama_kategori ?? 'Umum' }}</td>
                    <td>{{ $item->nama_cabang }}</td>
                    <td class="text-center">{{ $item->nama_satuan ?? '-' }}</td>
                    <td class="text-center font-mono fw-bold">{{ number_format($item->total_stok, 0, ',', '.') }}</td>
                    <td class="text-end font-mono">Rp {{ number_format($item->avg_harga_awal, 0, ',', '.') }}</td>
                    <td class="text-end font-mono fw-bold">Rp {{ number_format($item->total_asset_item, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data stok barang pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-grand-total">
                <td colspan="5" style="padding: 10px;">
                    <strong>GRAND TOTAL KESELURUHAN STOK</strong>
                </td>
                <td class="text-center font-mono" style="padding: 10px;">
                    <strong>{{ number_format($grandTotalStok, 0, ',', '.') }} Unit</strong>
                </td>
                <td class="text-end" style="padding: 10px;">
                    <strong>Total Nilai Aset:</strong>
                </td>
                <td class="text-end font-mono" style="padding: 10px;">
                    <strong>Rp {{ number_format($grandTotalAset, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
