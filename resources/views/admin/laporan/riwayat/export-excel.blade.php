<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Penjualan - Pressent Studio</title>
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
    <div class="header-subtitle">RIWAYAT PENJUALAN HARIAN</div>
    <br>

    <table class="meta-table">
        <tr>
            <td style="width: 130px;"><strong>Tanggal</strong></td>
            <td>: {{ $labelTanggal }}</td>
        </tr>
        <tr>
            <td><strong>Outlet Cabang</strong></td>
            <td>: {{ $namaCabang }}</td>
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
                <th>No. Transaksi</th>
                <th>Jam</th>
                <th>Outlet</th>
                <th>Kasir</th>
                <th>Pelanggan</th>
                <th>Studio & Film</th>
                <th>Menu Cafe</th>
                <th>Metode Bayar</th>
                <th>Subtotal Studio (Rp)</th>
                <th>Subtotal Cafe (Rp)</th>
                <th>Diskon (Rp)</th>
                <th>Total Bayar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandStudio = 0;
                $grandCafe = 0;
                $grandDiskon = 0;
                $grandTotal = 0;
            @endphp
            @forelse($transaksis as $index => $trx)
                @php
                    $grandStudio += $trx->subtotal_film_room;
                    $grandCafe += $trx->subtotal_cafe;
                    $grandDiskon += $trx->diskon;
                    $grandTotal += $trx->total_bayar;

                    $filmStr = '-';
                    if ($trx->film) {
                        $filmStr = $trx->film->judul . ' (' . ($trx->room->nama_room ?? 'Studio') . ' - ' . $trx->durasi_menit . ' mnt)';
                    }

                    $cafeStr = '-';
                    if ($trx->details && $trx->details->count() > 0) {
                        $cafeStr = $trx->details->map(function($d) {
                            return ($d->barang ? $d->barang->nama_barang : 'Barang') . ' (' . (float)$d->qty . 'x)';
                        })->implode(', ');
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center fw-bold">{{ $trx->nomor_transaksi }}</td>
                    <td class="text-center">{{ $trx->waktu_transaksi ? $trx->waktu_transaksi->format('H:i') : '-' }}</td>
                    <td>{{ $trx->tokoCabang->nama_cabang ?? 'Outlet Umum' }}</td>
                    <td>{{ $trx->user->name ?? $trx->user->username ?? '-' }}</td>
                    <td>{{ $trx->nama_pelanggan ?: 'Umum' }}</td>
                    <td>{{ $filmStr }}</td>
                    <td>{{ $cafeStr }}</td>
                    <td class="text-center">{{ $trx->tipePembayaran->metode_pembayaran ?? $trx->tipePembayaran->nama_tipe ?? 'Tunai' }}</td>
                    <td class="text-end">{{ number_format($trx->subtotal_film_room, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($trx->subtotal_cafe, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($trx->diskon, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">Tidak ada transaksi penjualan untuk tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($transaksis) > 0)
            <tfoot>
                <tr class="bg-total">
                    <td colspan="9" class="text-center">GRAND TOTAL</td>
                    <td class="text-end">{{ number_format($grandStudio, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandCafe, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandDiskon, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
