<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi - Pressent Studio</title>
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
        .bg-section {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #111827;
        }
        .bg-subtotal-green {
            background-color: #d1fae5;
            font-weight: bold;
            color: #065f46;
        }
        .bg-subtotal-blue {
            background-color: #e0f2fe;
            font-weight: bold;
            color: #0369a1;
        }
        .bg-subtotal-red {
            background-color: #fee2e2;
            font-weight: bold;
            color: #991b1b;
        }
        .bg-netto-laba {
            background-color: #a7f3d0;
            font-weight: bold;
            color: #064e3b;
            font-size: 12pt;
        }
        .bg-netto-rugi {
            background-color: #fecaca;
            font-weight: bold;
            color: #7f1d1d;
            font-size: 12pt;
        }
        .text-danger { color: #dc2626; }
        .text-success { color: #059669; }
    </style>
</head>
<body>

    <div class="header-title">PRESSENT STUDIO &amp; CAFE</div>
    <div class="header-subtitle">LAPORAN LABA &amp; RUGI KOMPREHENSIF</div>
    <br>

    <table class="meta-table">
        <tr>
            <td style="width: 140px;"><strong>Outlet</strong></td>
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
            <td><strong>Status Finansial</strong></td>
            <td>: {{ strtoupper($statusNetto) }} (Margin: {{ $marginNettoPersen }}%)</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 65%; text-align: left;">KOMPONEN KEUANGAN</th>
                <th style="width: 35%; text-align: right;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>

            <!-- A. PENDAPATAN USAHA -->
            <tr class="bg-section">
                <td colspan="2"><strong>A. PENDAPATAN USAHA</strong></td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">Pendapatan Film</td>
                <td class="text-end font-mono">Rp {{ number_format($pendapatanFilm, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">Pendapatan Kafe</td>
                <td class="text-end font-mono">Rp {{ number_format($pendapatanKafe, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">Diskon Penjualan</td>
                <td class="text-end font-mono text-danger">(Rp {{ number_format($diskonPenjualan, 0, ',', '.') }})</td>
            </tr>
            <tr class="bg-subtotal-green">
                <td style="padding-left: 25px;"><strong>TOTAL PENDAPATAN USAHA</strong></td>
                <td class="text-end font-mono fw-bold">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>

            <!-- B. HARGA POKOK PENJUALAN (HPP) -->
            <tr class="bg-section">
                <td colspan="2"><strong>B. HARGA POKOK PENJUALAN (HPP)</strong></td>
            </tr>
            <tr>
                <td style="padding-left: 25px;">HPP Makanan dan Minuman</td>
                <td class="text-end font-mono text-danger">(Rp {{ number_format($hppMakananMinuman, 0, ',', '.') }})</td>
            </tr>
            <tr class="bg-subtotal-blue">
                <td style="padding-left: 25px;"><strong>LABA KOTOR</strong></td>
                <td class="text-end font-mono fw-bold">Rp {{ number_format($labaKotor, 0, ',', '.') }}</td>
            </tr>

            <!-- C. BEBAN OPERASIONAL -->
            <tr class="bg-section">
                <td colspan="2"><strong>C. BEBAN OPERASIONAL</strong></td>
            </tr>
            @foreach($bebanKategoriList as $beban)
                <tr>
                    <td style="padding-left: 25px;">{{ $beban['kategori'] }}</td>
                    <td class="text-end font-mono text-danger">(Rp {{ number_format($beban['nominal'], 0, ',', '.') }})</td>
                </tr>
            @endforeach
            <tr class="bg-subtotal-red">
                <td style="padding-left: 25px;"><strong>TOTAL BEBAN OPERASIONAL</strong></td>
                <td class="text-end font-mono fw-bold text-danger">(Rp {{ number_format($totalBebanOperasional, 0, ',', '.') }})</td>
            </tr>

            <!-- TOTAL NETTO -->
            <tr class="{{ $statusNetto === 'laba' ? 'bg-netto-laba' : 'bg-netto-rugi' }}">
                <td style="padding: 10px;">
                    <strong>TOTAL NETTO ({{ $statusNetto === 'laba' ? 'LABA BERSIH' : 'RUGI BERSIH' }})</strong><br>
                    <small>Margin Bersih: {{ $marginNettoPersen }}%</small>
                </td>
                <td class="text-end font-mono fw-bold" style="padding: 10px; font-size: 13pt;">
                    {{ $totalNetto < 0 ? '-' : '+' }} Rp {{ number_format(abs($totalNetto), 0, ',', '.') }}
                </td>
            </tr>

        </tbody>
    </table>

</body>
</html>
