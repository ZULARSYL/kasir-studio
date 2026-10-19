<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ $transaksi->nomor_transaksi }} | Pressent Studio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }
        .receipt-container {
            width: 80mm;
            max-width: 380px;
            background: #ffffff;
            padding: 18px 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            color: #111827;
            font-size: 12px;
            line-height: 1.35;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .divider {
            border-top: 1px dashed #94a3b8;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #475569;
            margin: 8px 0;
        }
        .row-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3px;
        }
        .receipt-title {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .btn-action-bar {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .btn-print {
            flex: 1;
            padding: 10px;
            background: #059669;
            color: white;
            border: none;
            border-radius: 6px;
            font-family: system-ui, sans-serif;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #047857; }
        .btn-close-window {
            padding: 10px 14px;
            background: #e2e8f0;
            color: #334155;
            border: none;
            border-radius: 6px;
            font-family: system-ui, sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .barcode {
            font-family: monospace;
            font-size: 20px;
            letter-spacing: 4px;
            font-weight: bold;
            margin-top: 6px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                width: 100%;
                max-width: 100%;
                padding: 0;
                border-radius: 0;
            }
            .btn-action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Store Header -->
        <div class="text-center">
            <div class="receipt-title">PRESSENT STUDIO & CAFE</div>
            <div style="font-size: 11px; margin-top: 2px;">{{ $transaksi->tokoCabang->nama_cabang ?? 'Pekanbaru Center' }}</div>
            <div style="font-size: 10px; color: #475569;">{{ $transaksi->tokoCabang->alamat ?? 'Jl. Arifin Ahmad No. 88, Pekanbaru' }}</div>
            <div style="font-size: 10px; color: #475569;">Telp / WA: {{ $transaksi->tokoCabang->kontak ?? '0812-3456-7890' }}</div>
        </div>

        <div class="divider"></div>

        <!-- Meta Info Transaksi -->
        <div class="row-flex">
            <span>NO NOTA:</span>
            <span class="fw-bold">{{ $transaksi->nomor_transaksi }}</span>
        </div>
        <div class="row-flex">
            <span>TANGGAL:</span>
            <span>{{ $transaksi->waktu_transaksi ? $transaksi->waktu_transaksi->format('d/m/Y H:i') : date('d/m/Y H:i') }}</span>
        </div>
        <div class="row-flex">
            <span>KASIR:</span>
            <span>{{ $transaksi->user->name ?? 'Kasir Pressent' }}</span>
        </div>
        <div class="row-flex">
            <span>PELANGGAN:</span>
            <span>{{ $transaksi->nama_pelanggan }}</span>
        </div>

        <div class="double-divider"></div>

        <!-- Rincian Tiket Film & Room -->
        <div class="fw-bold mb-1" style="font-size: 11px;">[ TIKET STUDIO & FILM ]</div>
        <div class="row-flex">
            <span style="max-width: 70%; font-weight: bold;">
                {{ $transaksi->film->judul ?? 'Film Studio' }}
            </span>
            <span class="fw-bold">
                Rp {{ number_format($transaksi->subtotal_film_room, 0, ',', '.') }}
            </span>
        </div>
        <div style="font-size: 10px; color: #475569; margin-left: 4px; margin-bottom: 6px;">
            <div>• Room: <strong>{{ $transaksi->room->nama ?? '-' }}</strong> (Kap: {{ $transaksi->room->kapasitas ?? '-' }} org)</div>
            <div>• Durasi: {{ $transaksi->durasi_menit }} Menit</div>
        </div>

        <!-- Rincian Menu Cafe (Jika ada) -->
        @if($transaksi->details && $transaksi->details->count() > 0)
            <div class="divider"></div>
            <div class="fw-bold mb-1" style="font-size: 11px;">[ PESANAN CAFE & SNACK ]</div>
            @foreach($transaksi->details as $item)
                <div class="row-flex">
                    <span style="max-width: 65%;">
                        {{ $item->barang->nama_barang ?? 'Item Cafe' }}
                    </span>
                    <span class="fw-bold">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </span>
                </div>
                <div style="font-size: 10px; color: #64748b; margin-left: 4px; margin-bottom: 4px;">
                    {{ (int)$item->qty == $item->qty ? (int)$item->qty : $item->qty }} {{ $item->satuanBarang->nama_satuan ?? 'pcs' }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                </div>
            @endforeach
        @endif

        <div class="divider"></div>

        <!-- Ringkasan Pembayaran -->
        <div class="row-flex">
            <span>SUBTOTAL:</span>
            <span>Rp {{ number_format($transaksi->subtotal_film_room + $transaksi->subtotal_cafe, 0, ',', '.') }}</span>
        </div>
        @if($transaksi->diskon > 0)
            <div class="row-flex" style="color: #059669;">
                <span>DISKON PROMO:</span>
                <span>- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="row-flex fw-bold" style="font-size: 14px; margin: 4px 0;">
            <span>TOTAL BAYAR:</span>
            <span>Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <div class="row-flex">
            <span>METODE BAYAR:</span>
            <span class="fw-bold">{{ strtoupper($transaksi->tipePembayaran->metode_pembayaran ?? 'TUNAI') }}</span>
        </div>
        <div class="row-flex">
            <span>UANG DITERIMA:</span>
            <span>Rp {{ number_format($transaksi->uang_diterima, 0, ',', '.') }}</span>
        </div>
        <div class="row-flex">
            <span>KEMBALIAN:</span>
            <span class="fw-bold">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
        </div>

        <div class="double-divider"></div>

        <!-- Footer Struk -->
        <div class="text-center" style="margin-top: 8px;">
            <div style="font-size: 11px; font-weight: bold;">TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
            <div style="font-size: 10px; color: #475569; margin-top: 2px;">
                Selamat menikmati pengalaman menonton & hidangan kami di Pressent Studio.
            </div>
            <div class="barcode">||| | |||| ||| |||||</div>
            <div style="font-size: 9px; color: #94a3b8; margin-top: 4px;">
                Dicetak pada: {{ date('d-m-Y H:i:s') }}
            </div>
        </div>

        <!-- Tombol Aksi (Hanya di layar browser, tidak tercetak) -->
        <div class="btn-action-bar">
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="bi bi-printer-fill"></i> Cetak Struk
            </button>
            <a href="{{ route('kasir.kasir') }}" class="btn-close-window">
                Kembali ke Kasir
            </a>
        </div>
    </div>

    <script>
        // Opsional: otomatis buka dialog print ketika dibuka di popup
        window.addEventListener('DOMContentLoaded', () => {
            if (window.opener) {
                setTimeout(() => { window.print(); }, 500);
            }
        });
    </script>
</body>
</html>
