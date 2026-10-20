<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Pressent Studio</title>
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
    <div class="header-subtitle">LAPORAN PENJUALAN KASIR (REKAP TUTUP SHIFT)</div>
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
                <th>Outlet Cabang</th>
                <th>Kasir</th>
                <th>Waktu Buka</th>
                <th>Waktu Tutup</th>
                <th>Modal Awal (Rp)</th>
                <th>Penjualan (Rp)</th>
                <th>Tunai (Rp)</th>
                <th>Non-Tunai (Rp)</th>
                <th>Modal Akhir (Rp)</th>
                <th>Selisih Kas (Rp)</th>
                <th>Trx</th>
                <th>Catatan Tutup</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandModalAwal = 0;
                $grandPenjualan = 0;
                $grandTunai = 0;
                $grandNonTunai = 0;
                $grandModalAkhir = 0;
                $grandSelisih = 0;
                $grandTrx = 0;
            @endphp
            @forelse($shifts as $index => $shift)
                @php
                    $expectedCash = $shift->modal_awal + $shift->total_tunai;
                    $selisih = $shift->modal_akhir - $expectedCash;

                    $grandModalAwal += $shift->modal_awal;
                    $grandPenjualan += $shift->total_penjualan;
                    $grandTunai += $shift->total_tunai;
                    $grandNonTunai += $shift->total_non_tunai;
                    $grandModalAkhir += $shift->modal_akhir;
                    $grandSelisih += $selisih;
                    $grandTrx += $shift->total_transaksi;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $shift->tokoCabang->nama_cabang ?? 'Outlet Umum' }}</td>
                    <td>{{ $shift->user->name ?? $shift->user->username ?? '-' }}</td>
                    <td class="text-center">{{ $shift->waktu_buka ? $shift->waktu_buka->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center">{{ $shift->waktu_tutup ? $shift->waktu_tutup->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-end">{{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($shift->total_penjualan, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($shift->total_tunai, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($shift->total_non_tunai, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($shift->modal_akhir, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($selisih, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $shift->total_transaksi }}</td>
                    <td>{{ $shift->catatan_tutup ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">Tidak ada data shift tutup penjualan untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($shifts) > 0)
            <tfoot>
                <tr class="bg-total">
                    <td colspan="5" class="text-center">GRAND TOTAL</td>
                    <td class="text-end">{{ number_format($grandModalAwal, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandPenjualan, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandTunai, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandNonTunai, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandModalAkhir, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandSelisih, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $grandTrx }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
