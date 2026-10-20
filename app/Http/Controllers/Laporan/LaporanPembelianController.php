<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\FakturBarang;
use App\Models\TokoCabang;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanPembelianController extends Controller
{
    /**
     * Tampilkan halaman laporan pembelian barang (faktur pembelian).
     */
    public function index(Request $request)
    {
        $tokoCabangs = TokoCabang::orderBy('nama_cabang')->get();
        $selectedCabang = $request->get('toko_cabang_id', 'all');
        $periode = $request->get('periode', 'perhari');

        // Parameter filter tanggal
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $query = FakturBarang::with(['tokoCabang', 'user.pegawai', 'details.barang', 'details.satuanBarang']);

        // Filter Cabang
        if ($selectedCabang && $selectedCabang !== 'all') {
            $query->where('toko_cabang_id', $selectedCabang);
        }

        // Filter Periode
        if ($periode === 'perhari') {
            $query->whereDate('tanggal_faktur', $tanggal);
        } elseif ($periode === 'perminggu') {
            $query->whereDate('tanggal_faktur', '>=', $startDate)
                  ->whereDate('tanggal_faktur', '<=', $endDate);
        } elseif ($periode === 'perbulan') {
            $query->whereMonth('tanggal_faktur', $bulan)
                  ->whereYear('tanggal_faktur', $tahun);
        }

        // Clone query untuk kalkulasi ringkasan KPI
        $kpiQuery = clone $query;
        $totalPembelian = (float) $kpiQuery->sum('total_nominal');
        $totalFaktur = $kpiQuery->count();
        $rataRataFaktur = $totalFaktur > 0 ? $totalPembelian / $totalFaktur : 0;

        // Total kuantitas barang masuk dari faktur yang terfilter
        $fakturIds = (clone $query)->pluck('id');
        $totalQtyBarang = (float) \App\Models\FakturBarangDetail::whereIn('faktur_barang_id', $fakturIds)->sum('qty');

        $fakturs = $query->latest('tanggal_faktur')->latest('id')->paginate(15)->withQueryString();

        return view('admin.laporan.pembelian.index', compact(
            'tokoCabangs',
            'selectedCabang',
            'periode',
            'tanggal',
            'startDate',
            'endDate',
            'bulan',
            'tahun',
            'totalPembelian',
            'totalFaktur',
            'rataRataFaktur',
            'totalQtyBarang',
            'fakturs'
        ));
    }

    /**
     * Dapatkan detail faktur barang via JSON/Modal.
     */
    public function detailFaktur($id)
    {
        $faktur = FakturBarang::with([
            'tokoCabang',
            'user.pegawai',
            'details.barang',
            'details.satuanBarang'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'faktur' => $faktur,
            'details' => $faktur->details,
        ]);
    }

    /**
     * Export laporan pembelian ke format Excel (.xls).
     */
    public function export(Request $request)
    {
        $selectedCabang = $request->get('toko_cabang_id', 'all');
        $periode = $request->get('periode', 'perhari');

        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $bulan = (int) $request->get('bulan', Carbon::now()->month);
        $tahun = (int) $request->get('tahun', Carbon::now()->year);

        $query = FakturBarang::with(['tokoCabang', 'user.pegawai', 'details.barang', 'details.satuanBarang']);

        $namaCabang = 'Semua Outlet';
        if ($selectedCabang && $selectedCabang !== 'all') {
            $cabang = TokoCabang::find($selectedCabang);
            if ($cabang) {
                $namaCabang = $cabang->nama_cabang;
            }
            $query->where('toko_cabang_id', $selectedCabang);
        }

        $labelPeriode = '';
        if ($periode === 'perhari') {
            $query->whereDate('tanggal_faktur', $tanggal);
            $labelPeriode = 'Hari: ' . Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($periode === 'perminggu') {
            $query->whereDate('tanggal_faktur', '>=', $startDate)
                  ->whereDate('tanggal_faktur', '<=', $endDate);
            $labelPeriode = 'Minggu: ' . Carbon::parse($startDate)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d M Y');
        } elseif ($periode === 'perbulan') {
            $query->whereMonth('tanggal_faktur', $bulan)
                  ->whereYear('tanggal_faktur', $tahun);
            $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
            $labelPeriode = 'Bulan: ' . $namaBulan;
        }

        $fakturs = $query->orderBy('tanggal_faktur', 'asc')->get();

        $filename = 'Laporan_Pembelian_' . str_replace([' ', '/', ':'], '_', $namaCabang) . '_' . date('Ymd_His') . '.xls';

        return response()->view('admin.laporan.pembelian.export-excel', [
            'fakturs' => $fakturs,
            'namaCabang' => $namaCabang,
            'labelPeriode' => $labelPeriode,
            'tanggalExport' => Carbon::now()->translatedFormat('d F Y H:i:s'),
        ])->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
          ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
          ->header('Cache-Control', 'max-age=0');
    }
}
