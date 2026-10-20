<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\KasirShift;
use App\Models\TokoCabang;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanPenjualanController extends Controller
{
    /**
     * Tampilkan halaman laporan penjualan kasir (berdasarkan shift tutup).
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

        // Query KasirShift dengan status 'tutup'
        $query = KasirShift::with(['user.pegawai', 'tokoCabang'])
            ->where('status', 'tutup');

        // Filter Cabang (Semua Outlet atau Cabang tertentu)
        if ($selectedCabang && $selectedCabang !== 'all') {
            $query->where('toko_cabang_id', $selectedCabang);
        }

        // Filter Periode
        if ($periode === 'perhari') {
            $query->whereDate('waktu_tutup', $tanggal);
        } elseif ($periode === 'perminggu') {
            $query->whereDate('waktu_tutup', '>=', $startDate)
                  ->whereDate('waktu_tutup', '<=', $endDate);
        } elseif ($periode === 'perbulan') {
            $query->whereMonth('waktu_tutup', $bulan)
                  ->whereYear('waktu_tutup', $tahun);
        }

        // Clone query untuk kalkulasi ringkasan KPI
        $kpiQuery = clone $query;
        $totalOmset = (float) $kpiQuery->sum('total_penjualan');
        $totalTunai = (float) $kpiQuery->sum('total_tunai');
        $totalNonTunai = (float) $kpiQuery->sum('total_non_tunai');
        $totalTransaksi = (int) $kpiQuery->sum('total_transaksi');
        $totalShift = $kpiQuery->count();

        // Data shifts paginasi
        $shifts = $query->latest('waktu_tutup')->paginate(15)->withQueryString();

        // Data outlet terpilih untuk info tampilan
        $cabangModel = ($selectedCabang && $selectedCabang !== 'all')
            ? TokoCabang::find($selectedCabang)
            : null;

        return view('admin.laporan.penjualan.index', compact(
            'tokoCabangs',
            'selectedCabang',
            'periode',
            'tanggal',
            'startDate',
            'endDate',
            'bulan',
            'tahun',
            'totalOmset',
            'totalTunai',
            'totalNonTunai',
            'totalTransaksi',
            'totalShift',
            'shifts'
        ));
    }

    /**
     * Dapatkan detail transaksi pada shift tertentu via JSON/Modal.
     */
    public function detailShift($id)
    {
        $shift = KasirShift::with([
            'user.pegawai',
            'tokoCabang',
            'transaksis.film',
            'transaksis.room',
            'transaksis.tipePembayaran',
            'transaksis.promo',
            'transaksis.details.barang',
            'transaksis.details.satuanBarang',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'shift' => $shift,
            'transaksis' => $shift->transaksis,
        ]);
    }

    /**
     * Export laporan penjualan shift tutup ke format Excel (.xls).
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

        $query = KasirShift::with(['user.pegawai', 'tokoCabang'])
            ->where('status', 'tutup');

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
            $query->whereDate('waktu_tutup', $tanggal);
            $labelPeriode = 'Hari: ' . Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($periode === 'perminggu') {
            $query->whereDate('waktu_tutup', '>=', $startDate)
                  ->whereDate('waktu_tutup', '<=', $endDate);
            $labelPeriode = 'Minggu: ' . Carbon::parse($startDate)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d M Y');
        } elseif ($periode === 'perbulan') {
            $query->whereMonth('waktu_tutup', $bulan)
                  ->whereYear('waktu_tutup', $tahun);
            $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
            $labelPeriode = 'Bulan: ' . $namaBulan;
        }

        $shifts = $query->orderBy('waktu_tutup', 'asc')->get();

        $filename = 'Laporan_Penjualan_' . str_replace([' ', '/', ':'], '_', $namaCabang) . '_' . date('Ymd_His') . '.xls';

        return response()->view('admin.laporan.penjualan.export-excel', [
            'shifts' => $shifts,
            'namaCabang' => $namaCabang,
            'labelPeriode' => $labelPeriode,
            'tanggalExport' => Carbon::now()->translatedFormat('d F Y H:i:s'),
        ])->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
          ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
          ->header('Cache-Control', 'max-age=0');
    }
}
