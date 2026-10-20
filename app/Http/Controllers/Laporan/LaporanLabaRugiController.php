<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\JenisBiaya;
use App\Models\Pengeluaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanLabaRugiController extends Controller
{
    /**
     * Tampilkan halaman laporan laba & rugi.
     */
    public function index(Request $request)
    {
        $data = $this->calculateLabaRugiData($request);

        return view('admin.laporan.laba-rugi.index', $data);
    }

    /**
     * Ekspor laporan laba & rugi ke Excel (.xls).
     */
    public function export(Request $request)
    {
        $data = $this->calculateLabaRugiData($request);

        $namaCabang = $data['namaCabang'];
        $cleanCabang = str_replace([' ', '/', ':', '\\', '.'], '_', $namaCabang);
        $periodeLabel = str_replace([' ', '/', ':', '\\', '.'], '_', $data['labelPeriode']);
        $filename = 'Laporan_Laba_Rugi_' . $cleanCabang . '_' . date('Ymd_His') . '.xls';

        return response()->view('admin.laporan.laba-rugi.export-excel', $data)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Helper kalkulasi data Laporan Laba & Rugi sesuai filter.
     */
    protected function calculateLabaRugiData(Request $request): array
    {
        $tokoCabangs = TokoCabang::orderBy('nama_cabang')->get();
        $selectedCabang = $request->get('toko_cabang_id', 'all');
        $periode = $request->get('periode', 'perbulan');

        // Parameter Tanggal
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        // 1. Setup Query Transaksi
        $transaksiQuery = Transaksi::where('status', 'selesai');
        $pengeluaranQuery = Pengeluaran::query();

        // Filter Cabang
        $namaCabang = 'Semua Toko Cabang';
        if ($selectedCabang && $selectedCabang !== 'all') {
            $cabang = TokoCabang::find($selectedCabang);
            if ($cabang) {
                $namaCabang = $cabang->nama_cabang;
            }
            $transaksiQuery->where('toko_cabang_id', $selectedCabang);
            $pengeluaranQuery->where('toko_cabang_id', $selectedCabang);
        }

        // Filter Periode (perhari, perminggu, perbulan, pertahun)
        $labelPeriode = '';
        if ($periode === 'perhari') {
            $transaksiQuery->whereDate('waktu_transaksi', $tanggal);
            $pengeluaranQuery->whereDate('tanggal', $tanggal);
            $labelPeriode = 'Hari: ' . Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
        } elseif ($periode === 'perminggu') {
            $transaksiQuery->whereDate('waktu_transaksi', '>=', $startDate)
                           ->whereDate('waktu_transaksi', '<=', $endDate);
            $pengeluaranQuery->whereDate('tanggal', '>=', $startDate)
                             ->whereDate('tanggal', '<=', $endDate);
            $labelPeriode = 'Minggu: ' . Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y');
        } elseif ($periode === 'pertahun') {
            $transaksiQuery->whereYear('waktu_transaksi', $tahun);
            $pengeluaranQuery->whereYear('tanggal', $tahun);
            $labelPeriode = 'Tahun: ' . $tahun;
        } else {
            // Default perbulan
            $periode = 'perbulan';
            $transaksiQuery->whereMonth('waktu_transaksi', $bulan)
                           ->whereYear('waktu_transaksi', $tahun);
            $pengeluaranQuery->whereMonth('tanggal', $bulan)
                             ->whereYear('tanggal', $tahun);
            $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y');
            $labelPeriode = 'Bulan: ' . $namaBulan;
        }

        // 2. BAGIAN A: Pendapatan Usaha
        $pendapatanFilm = (float) (clone $transaksiQuery)->sum('subtotal_film_room');
        $pendapatanKafe = (float) (clone $transaksiQuery)->sum('subtotal_cafe');
        $diskonPenjualan = (float) (clone $transaksiQuery)->sum('diskon');
        $totalPendapatan = ($pendapatanFilm + $pendapatanKafe) - $diskonPenjualan;
        $totalTransaksiCount = (int) (clone $transaksiQuery)->count();

        // 3. BAGIAN B: Harga Pokok Penjualan (HPP Makanan & Minuman dari faktur harga awal)
        $transaksiIds = (clone $transaksiQuery)->pluck('id');
        $transaksiDetails = TransaksiDetail::whereIn('transaksi_id', $transaksiIds)->get();

        $barangIds = $transaksiDetails->pluck('barang_id')->unique();
        $hargaAwalMap = [];

        if ($barangIds->isNotEmpty()) {
            $fakturQuery = DB::table('faktur_barang_details')
                ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
                ->whereIn('faktur_barang_details.barang_id', $barangIds);

            if ($selectedCabang && $selectedCabang !== 'all') {
                $fakturQuery->where('faktur_barangs.toko_cabang_id', $selectedCabang);
            }

            $latestFakturs = $fakturQuery->select(
                'faktur_barang_details.barang_id',
                'faktur_barang_details.harga_awal',
                'faktur_barangs.tanggal_faktur',
                'faktur_barang_details.id'
            )->orderBy('faktur_barangs.tanggal_faktur', 'desc')
             ->orderBy('faktur_barang_details.id', 'desc')
             ->get();

            foreach ($latestFakturs as $fkt) {
                if (!isset($hargaAwalMap[$fkt->barang_id])) {
                    $hargaAwalMap[$fkt->barang_id] = (float) $fkt->harga_awal;
                }
            }

            // Fallback untuk barang yang belum punya faktur di cabang ini
            $missingBarangs = $barangIds->diff(array_keys($hargaAwalMap));
            if ($missingBarangs->isNotEmpty()) {
                $fallbackFakturs = DB::table('faktur_barang_details')
                    ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
                    ->whereIn('faktur_barang_details.barang_id', $missingBarangs)
                    ->select('faktur_barang_details.barang_id', 'faktur_barang_details.harga_awal')
                    ->orderBy('faktur_barangs.tanggal_faktur', 'desc')
                    ->orderBy('faktur_barang_details.id', 'desc')
                    ->get();

                foreach ($fallbackFakturs as $fkt) {
                    if (!isset($hargaAwalMap[$fkt->barang_id])) {
                        $hargaAwalMap[$fkt->barang_id] = (float) $fkt->harga_awal;
                    }
                }
            }
        }

        $hppMakananMinuman = 0;
        foreach ($transaksiDetails as $td) {
            $hargaAwal = $hargaAwalMap[$td->barang_id] ?? 0;
            $hppMakananMinuman += ((float) $td->qty * $hargaAwal);
        }

        $labaKotor = $totalPendapatan - $hppMakananMinuman;

        // 4. BAGIAN C: Beban Operasional (dari tabel pengeluarans, dikelompokkan jenis_biaya)
        $pengeluarans = (clone $pengeluaranQuery)->with('jenisBiaya')->get();
        $totalPengeluaranCount = $pengeluarans->count();

        // Ambil semua master jenis biaya
        $allJenisBiayas = JenisBiaya::orderBy('kategori', 'asc')->get();

        // Kelompokkan pengeluaran berdasarkan jenis_biaya_id
        $groupedPengeluaran = $pengeluarans->groupBy('jenis_biaya_id');

        // Buat daftar rincian beban operasional per kategori
        $bebanKategoriList = [];
        $totalBebanOperasional = 0;

        foreach ($allJenisBiayas as $jb) {
            $nominalKategori = isset($groupedPengeluaran[$jb->id])
                ? (float) $groupedPengeluaran[$jb->id]->sum('nominal')
                : 0;

            $bebanKategoriList[] = [
                'id' => $jb->id,
                'kategori' => $jb->kategori,
                'nominal' => $nominalKategori,
                'jumlah_transaksi' => isset($groupedPengeluaran[$jb->id]) ? $groupedPengeluaran[$jb->id]->count() : 0,
            ];

            $totalBebanOperasional += $nominalKategori;
        }

        // Tangani jika ada pengeluaran tanpa kategori jenis biaya
        if (isset($groupedPengeluaran[''])) {
            $tanpaKategoriNominal = (float) $groupedPengeluaran['']->sum('nominal');
            $bebanKategoriList[] = [
                'id' => null,
                'kategori' => 'Lain-lain / Tanpa Kategori',
                'nominal' => $tanpaKategoriNominal,
                'jumlah_transaksi' => $groupedPengeluaran['']->count(),
            ];
            $totalBebanOperasional += $tanpaKategoriNominal;
        }

        // 5. TOTAL NETTO (Laba / Rugi Bersih)
        $totalNetto = $labaKotor - $totalBebanOperasional;
        $statusNetto = $totalNetto >= 0 ? 'laba' : 'rugi';
        $marginNettoPersen = $totalPendapatan > 0
            ? round(($totalNetto / $totalPendapatan) * 100, 2)
            : 0;

        return compact(
            'tokoCabangs',
            'selectedCabang',
            'namaCabang',
            'periode',
            'tanggal',
            'startDate',
            'endDate',
            'bulan',
            'tahun',
            'labelPeriode',
            'pendapatanFilm',
            'pendapatanKafe',
            'diskonPenjualan',
            'totalPendapatan',
            'totalTransaksiCount',
            'hppMakananMinuman',
            'labaKotor',
            'bebanKategoriList',
            'totalBebanOperasional',
            'totalPengeluaranCount',
            'totalNetto',
            'statusNetto',
            'marginNettoPersen'
        );
    }
}
