<?php

namespace App\Http\Controllers\Cafe;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\FakturBarangDetail;
use App\Models\MenuCafe;
use App\Models\SatuanBarang;
use App\Models\TokoCabang;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StokCafeController extends Controller
{
    /**
     * Tampilkan data inventaris stok barang cafe per outlet (diambil dari faktur barang).
     */
    public function index(Request $request)
    {
        // 1. Query Dasar: Gabungkan Faktur Detail, Faktur Header, Barang, Kategori, Outlet, dan Satuan
        $baseQuery = DB::table('faktur_barang_details')
            ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
            ->join('barangs', 'faktur_barang_details.barang_id', '=', 'barangs.id')
            ->leftJoin('menu_cafe', 'barangs.menu_cafe_id', '=', 'menu_cafe.id')
            ->join('toko_cabang', 'faktur_barangs.toko_cabang_id', '=', 'toko_cabang.id')
            ->join('satuan_barangs', 'faktur_barang_details.satuan_barang_id', '=', 'satuan_barangs.id');

        // 2. Filter Pencarian Teks (Nama Barang, Nama Cabang, Kategori)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('barangs.nama_barang', 'like', "%{$search}%")
                  ->orWhere('toko_cabang.nama_cabang', 'like', "%{$search}%")
                  ->orWhere('menu_cafe.nama', 'like', "%{$search}%");
            });
        }

        // 3. Filter Berdasarkan Outlet / Toko Cabang
        if ($request->filled('toko_cabang_id')) {
            $baseQuery->where('faktur_barangs.toko_cabang_id', $request->toko_cabang_id);
        }

        // 4. Filter Berdasarkan Kategori Menu Cafe
        if ($request->filled('menu_cafe_id')) {
            $baseQuery->where('barangs.menu_cafe_id', $request->menu_cafe_id);
        }

        // 5. Perhitungan Card Summary (Total Asset, Total Barang Fisik, Total Jenis Barang, Total Outlet)
        $totalAsset = (clone $baseQuery)->sum('faktur_barang_details.subtotal') ?: 0;
        $totalBarangFisik = (clone $baseQuery)->sum('faktur_barang_details.qty') ?: 0;
        $totalJenisBarang = (clone $baseQuery)->distinct()->count('faktur_barang_details.barang_id') ?: 0;
        $totalOutlet = (clone $baseQuery)->distinct()->count('faktur_barangs.toko_cabang_id') ?: 0;

        // 6. Query Pengelompokan Stok per (Outlet, Barang, Satuan)
        $groupedQuery = (clone $baseQuery)
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
                DB::raw('MAX(faktur_barang_details.harga_jual) as harga_jual_terakhir'),
                DB::raw('MAX(faktur_barangs.tanggal_faktur) as tanggal_terakhir'),
                DB::raw('COUNT(faktur_barangs.id) as total_transaksi_faktur'),
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
            ->orderBy('toko_cabang.nama_cabang', 'asc')
            ->orderBy('barangs.nama_barang', 'asc');

        // 7. Paginate aman dengan LengthAwarePaginator
        $allGrouped = $groupedQuery->get();
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

        // 8. Master Data untuk Komponen Filter
        $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();
        $kategoris = MenuCafe::orderBy('nama', 'asc')->get();

        return view('admin.cafe.stok.index', compact(
            'stokItems',
            'totalAsset',
            'totalBarangFisik',
            'totalJenisBarang',
            'totalOutlet',
            'cabangs',
            'kategoris'
        ));
    }

    /**
     * Endpoint API / AJAX untuk memuat riwayat faktur yang membentuk stok item tertentu.
     */
    public function riwayat($tokoCabangId, $barangId, $satuanBarangId)
    {
        $riwayats = DB::table('faktur_barang_details')
            ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
            ->join('barangs', 'faktur_barang_details.barang_id', '=', 'barangs.id')
            ->join('toko_cabang', 'faktur_barangs.toko_cabang_id', '=', 'toko_cabang.id')
            ->join('satuan_barangs', 'faktur_barang_details.satuan_barang_id', '=', 'satuan_barangs.id')
            ->leftJoin('users', 'faktur_barangs.user_id', '=', 'users.id')
            ->where('faktur_barangs.toko_cabang_id', $tokoCabangId)
            ->where('faktur_barang_details.barang_id', $barangId)
            ->where('faktur_barang_details.satuan_barang_id', $satuanBarangId)
            ->select([
                'faktur_barangs.id as faktur_id',
                'faktur_barangs.nomor_faktur',
                'faktur_barangs.tanggal_faktur',
                'faktur_barangs.pemasok',
                'barangs.nama_barang',
                'toko_cabang.nama_cabang',
                'satuan_barangs.nama_satuan',
                'faktur_barang_details.qty',
                'faktur_barang_details.harga_awal',
                'faktur_barang_details.harga_jual',
                'faktur_barang_details.subtotal',
                'users.name as petugas_nama',
            ])
            ->latest('faktur_barangs.tanggal_faktur')
            ->latest('faktur_barangs.id')
            ->get();

        return response()->json([
            'status' => 'success',
            'total_masuk' => $riwayats->sum('qty'),
            'total_asset' => $riwayats->sum('subtotal'),
            'data' => $riwayats,
        ]);
    }

    /**
     * Edit / Koreksi stok barang dan penyesuaian harga jual kasir.
     */
    public function update(Request $request, $tokoCabangId, $barangId, $satuanBarangId)
    {
        $validated = $request->validate([
            'stok' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'harga_awal' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string|max:255',
        ]);

        $details = FakturBarangDetail::whereHas('fakturBarang', function ($q) use ($tokoCabangId) {
                $q->where('toko_cabang_id', $tokoCabangId);
            })
            ->where('barang_id', $barangId)
            ->where('satuan_barang_id', $satuanBarangId)
            ->orderBy('id', 'asc')
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('admin.cafe.stok')
                ->with('error', 'Data stok barang tidak ditemukan.');
        }

        DB::transaction(function () use ($details, $validated) {
            $newTotalStok = (float) $validated['stok'];
            $newHargaJual = (float) $validated['harga_jual'];
            $newHargaAwal = isset($validated['harga_awal']) && $validated['harga_awal'] !== '' 
                ? (float) $validated['harga_awal'] 
                : null;

            $remaining = $newTotalStok;
            $totalCount = $details->count();

            foreach ($details as $index => $detail) {
                if ($index === $totalCount - 1) {
                    $detail->qty = max(0, $remaining);
                } else {
                    if ($remaining >= (float) $detail->qty) {
                        $remaining -= (float) $detail->qty;
                    } else {
                        $detail->qty = $remaining;
                        $remaining = 0;
                    }
                }

                $detail->harga_jual = $newHargaJual;
                if ($newHargaAwal !== null) {
                    $detail->harga_awal = $newHargaAwal;
                }
                $detail->subtotal = (float) $detail->qty * (float) $detail->harga_awal;
                $detail->save();

                // Sinkronisasi total nominal faktur induk
                $faktur = $detail->fakturBarang;
                if ($faktur) {
                    $faktur->total_nominal = $faktur->details()->sum('subtotal') ?: 0;
                    $faktur->save();
                }
            }
        });

        return redirect()->route('admin.cafe.stok')
            ->with('success', 'Stok dan harga jual barang berhasil diperbarui!');
    }

    /**
     * Hapus persediaan stok item barang dari outlet cabang terkait.
     */
    public function destroy($tokoCabangId, $barangId, $satuanBarangId)
    {
        $details = FakturBarangDetail::whereHas('fakturBarang', function ($q) use ($tokoCabangId) {
                $q->where('toko_cabang_id', $tokoCabangId);
            })
            ->where('barang_id', $barangId)
            ->where('satuan_barang_id', $satuanBarangId)
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('admin.cafe.stok')
                ->with('error', 'Data stok barang tidak ditemukan.');
        }

        DB::transaction(function () use ($details) {
            $fakturIds = $details->pluck('faktur_barang_id')->unique();

            foreach ($details as $detail) {
                $detail->delete();
            }

            foreach ($fakturIds as $fakturId) {
                $faktur = \App\Models\FakturBarang::find($fakturId);
                if ($faktur) {
                    $faktur->total_nominal = $faktur->details()->sum('subtotal') ?: 0;
                    $faktur->save();
                }
            }
        });

        return redirect()->route('admin.cafe.stok')
            ->with('success', 'Stok barang pada outlet tersebut berhasil dihapus!');
    }
}
