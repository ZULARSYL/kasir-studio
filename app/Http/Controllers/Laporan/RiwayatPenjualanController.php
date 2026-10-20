<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RiwayatPenjualanController extends Controller
{
    /**
     * Tampilkan riwayat penjualan harian kasir.
     */
    public function index(Request $request)
    {
        $tokoCabangs = TokoCabang::orderBy('nama_cabang')->get();
        $selectedCabang = $request->get('toko_cabang_id', 'all');
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $search = $request->get('search');

        $query = Transaksi::with([
            'user.pegawai',
            'tokoCabang',
            'film',
            'room',
            'tipePembayaran',
            'promo',
            'details.barang',
            'details.satuanBarang'
        ])->whereDate('waktu_transaksi', $tanggal);

        // Filter Cabang
        if ($selectedCabang && $selectedCabang !== 'all') {
            $query->where('toko_cabang_id', $selectedCabang);
        }

        // Pencarian nomor transaksi atau nama pelanggan
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%");
            });
        }

        // Clone query untuk kalkulasi ringkasan KPI hari ini
        $kpiQuery = clone $query;
        $totalOmset = (float) $kpiQuery->sum('total_bayar');
        $totalTransaksi = $kpiQuery->count();
        $totalTiketStudio = (float) $kpiQuery->sum('subtotal_film_room');
        $totalCafe = (float) $kpiQuery->sum('subtotal_cafe');
        $totalDiskon = (float) $kpiQuery->sum('diskon');

        $transaksis = $query->latest('waktu_transaksi')->paginate(20)->withQueryString();

        return view('admin.laporan.riwayat.index', compact(
            'tokoCabangs',
            'selectedCabang',
            'tanggal',
            'search',
            'totalOmset',
            'totalTransaksi',
            'totalTiketStudio',
            'totalCafe',
            'totalDiskon',
            'transaksis'
        ));
    }

    /**
     * Dapatkan detail transaksi lengkap via JSON untuk modal.
     */
    public function detailTransaksi($id)
    {
        $transaksi = Transaksi::with([
            'user.pegawai',
            'tokoCabang',
            'film',
            'room',
            'tipePembayaran',
            'promo',
            'details.barang',
            'details.satuanBarang',
            'kasirShift'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Export riwayat penjualan harian ke format Excel (.xls).
     */
    public function export(Request $request)
    {
        $selectedCabang = $request->get('toko_cabang_id', 'all');
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $search = $request->get('search');

        $query = Transaksi::with([
            'user.pegawai',
            'tokoCabang',
            'film',
            'room',
            'tipePembayaran',
            'promo',
            'details.barang',
            'details.satuanBarang'
        ])->whereDate('waktu_transaksi', $tanggal);

        $namaCabang = 'Semua Outlet';
        if ($selectedCabang && $selectedCabang !== 'all') {
            $cabang = TokoCabang::find($selectedCabang);
            if ($cabang) {
                $namaCabang = $cabang->nama_cabang;
            }
            $query->where('toko_cabang_id', $selectedCabang);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$search}%");
            });
        }

        $transaksis = $query->orderBy('waktu_transaksi', 'asc')->get();

        $labelTanggal = Carbon::parse($tanggal)->translatedFormat('d F Y');
        $filename = 'Riwayat_Penjualan_' . str_replace([' ', '/', ':'], '_', $namaCabang) . '_' . date('Ymd', strtotime($tanggal)) . '.xls';

        return response()->view('admin.laporan.riwayat.export-excel', [
            'transaksis' => $transaksis,
            'namaCabang' => $namaCabang,
            'labelTanggal' => $labelTanggal,
            'tanggalExport' => Carbon::now()->translatedFormat('d F Y H:i:s'),
        ])->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
          ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
          ->header('Cache-Control', 'max-age=0');
    }
}
