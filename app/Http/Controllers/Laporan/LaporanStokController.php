<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\MenuCafe;
use App\Models\TokoCabang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LaporanStokController extends Controller
{
    /**
     * Tampilkan halaman laporan stok barang pada sisi admin.
     */
    public function index(Request $request)
    {
        $data = $this->getStokReportData($request);

        // Paginate hasil grouped query untuk tampilan web
        $allGrouped = $data['groupedItems'];
        $page = (int) $request->input('page', 1);
        $perPage = 15;

        $stokItems = new LengthAwarePaginator(
            $allGrouped->forPage($page, $perPage)->values(),
            $allGrouped->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $viewData = array_merge($data, [
            'stokItems' => $stokItems,
        ]);

        return view('admin.laporan.stok.index', $viewData);
    }

    /**
     * Ekspor laporan stok barang ke format Excel (.xls).
     */
    public function export(Request $request)
    {
        $data = $this->getStokReportData($request);

        $cleanCabang = str_replace([' ', '/', ':', '\\', '.'], '_', $data['namaCabang']);
        $filename = 'Laporan_Stok_' . $cleanCabang . '_' . date('Ymd_His') . '.xls';

        return response()->view('admin.laporan.stok.export-excel', $data)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Helper untuk mengambil data stok & kalkulasi total aset.
     */
    protected function getStokReportData(Request $request): array
    {
        $tokoCabangs = TokoCabang::orderBy('nama_cabang')->get();
        $selectedCabang = $request->get('toko_cabang_id', 'all');
        $periode = $request->get('periode', 'perhari');

        // Parameter Tanggal
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        // 1. Query Dasar: Faktur Detail, Faktur Header, Barang, Kategori, Cabang, Satuan
        $baseQuery = DB::table('faktur_barang_details')
            ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
            ->join('barangs', 'faktur_barang_details.barang_id', '=', 'barangs.id')
            ->leftJoin('menu_cafe', 'barangs.menu_cafe_id', '=', 'menu_cafe.id')
            ->join('toko_cabang', 'faktur_barangs.toko_cabang_id', '=', 'toko_cabang.id')
            ->join('satuan_barangs', 'faktur_barang_details.satuan_barang_id', '=', 'satuan_barangs.id');

        // 2. Filter Cabang
        $namaCabang = 'Semua Toko Cabang';
        if ($selectedCabang && $selectedCabang !== 'all') {
            $cabang = TokoCabang::find($selectedCabang);
            if ($cabang) {
                $namaCabang = $cabang->nama_cabang;
            }
            $baseQuery->where('faktur_barangs.toko_cabang_id', $selectedCabang);
        }

        // 3. Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('barangs.nama_barang', 'like', "%{$search}%")
                  ->orWhere('menu_cafe.nama', 'like', "%{$search}%")
                  ->orWhere('toko_cabang.nama_cabang', 'like', "%{$search}%");
            });
        }

        // 4. Filter Periode (Perhari, Perminggu, Perbulan, Semua)
        $labelPeriode = '';
        if ($periode === 'perhari') {
            $baseQuery->whereDate('faktur_barangs.tanggal_faktur', $tanggal);
            $labelPeriode = 'Hari: ' . Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
        } elseif ($periode === 'perminggu') {
            $baseQuery->whereDate('faktur_barangs.tanggal_faktur', '>=', $startDate)
                      ->whereDate('faktur_barangs.tanggal_faktur', '<=', $endDate);
            $labelPeriode = 'Minggu: ' . Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y');
        } elseif ($periode === 'perbulan') {
            $baseQuery->whereMonth('faktur_barangs.tanggal_faktur', $bulan)
                      ->whereYear('faktur_barangs.tanggal_faktur', $tahun);
            $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y');
            $labelPeriode = 'Bulan: ' . $namaBulan;
        } else {
            $periode = 'semua';
            $labelPeriode = 'Semua Periode';
        }

        // 5. Kalkulasi Grand Total Keseluruhan
        $grandTotalStok = (float) (clone $baseQuery)->sum('faktur_barang_details.qty');
        $grandTotalAset = (float) (clone $baseQuery)->sum('faktur_barang_details.subtotal');
        $totalJenisBarang = (clone $baseQuery)->distinct()->count('faktur_barang_details.barang_id');
        $totalOutlet = (clone $baseQuery)->distinct()->count('faktur_barangs.toko_cabang_id');

        // 6. Query Pengelompokan Data Stok per Barang, Satuan, dan Cabang
        $groupedItems = (clone $baseQuery)
            ->select([
                'faktur_barangs.toko_cabang_id',
                'faktur_barang_details.barang_id',
                'faktur_barang_details.satuan_barang_id',
                'barangs.nama_barang',
                'toko_cabang.nama_cabang',
                'menu_cafe.nama as nama_kategori',
                'satuan_barangs.nama_satuan',
                DB::raw('SUM(faktur_barang_details.qty) as total_stok'),
                DB::raw('SUM(faktur_barang_details.subtotal) as total_asset_item'),
                DB::raw('AVG(faktur_barang_details.harga_awal) as avg_harga_awal'),
                DB::raw('MAX(faktur_barangs.tanggal_faktur) as tanggal_terakhir'),
            ])
            ->groupBy(
                'faktur_barangs.toko_cabang_id',
                'faktur_barang_details.barang_id',
                'faktur_barang_details.satuan_barang_id',
                'barangs.nama_barang',
                'toko_cabang.nama_cabang',
                'menu_cafe.nama',
                'satuan_barangs.nama_satuan'
            )
            ->orderBy('barangs.nama_barang', 'asc')
            ->orderBy('toko_cabang.nama_cabang', 'asc')
            ->get();

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
            'grandTotalStok',
            'grandTotalAset',
            'totalJenisBarang',
            'totalOutlet',
            'groupedItems'
        );
    }
}
