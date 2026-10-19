<?php

namespace App\Http\Controllers\Cafe;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\SatuanBarang;
use App\Models\TokoCabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FakturBarangController extends Controller
{
    /**
     * Tampilkan riwayat daftar faktur barang masuk (pembelian/grosir).
     */
    public function index(Request $request)
    {
        $query = FakturBarang::with(['tokoCabang', 'user', 'details.barang', 'details.satuanBarang']);

        // Filter pencarian nomor faktur, pemasok, catatan
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_faktur', 'like', "%{$search}%")
                  ->orWhere('pemasok', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        // Filter Outlet / Toko Cabang
        if ($request->filled('toko_cabang_id')) {
            $query->where('toko_cabang_id', $request->toko_cabang_id);
        }

        // Filter Rentang Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_faktur', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_faktur', '<=', $request->tanggal_selesai);
        }

        $fakturs = $query->latest('tanggal_faktur')->latest('id')->paginate(10)->withQueryString();
        $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();

        return view('admin.cafe.faktur.index', compact('fakturs', 'cabangs'));
    }

    /**
     * Tampilkan form input faktur barang baru.
     */
    public function create()
    {
        $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();
        $barangs = Barang::with('menuCafe')->orderBy('nama_barang', 'asc')->get();
        $satuans = SatuanBarang::orderBy('nama_satuan', 'asc')->get();
        $nomorFaktur = FakturBarang::generateNomorFaktur();

        return view('admin.cafe.faktur.create', compact('cabangs', 'barangs', 'satuans', 'nomorFaktur'));
    }

    /**
     * Simpan faktur barang beserta rincian item, harga awal & harga jual.
     */
    public function store(Request $request)
    {
        // 1. Validasi Ketat Sisi Server (termasuk proteksi Inspect Element DevTools)
        $validated = $request->validate([
            'nomor_faktur' => 'required|string|max:100|unique:faktur_barangs,nomor_faktur',
            'toko_cabang_id' => [
                'required',
                'integer',
                Rule::exists('toko_cabang', 'id'),
            ],
            'tanggal_faktur' => 'required|date',
            'pemasok' => 'nullable|string|max:255',
            'catatan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => [
                'required',
                'integer',
                Rule::exists('barangs', 'id'),
            ],
            'items.*.satuan_barang_id' => [
                'required',
                'integer',
                Rule::exists('satuan_barangs', 'id'),
            ],
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_awal' => 'required|numeric|min:0',
            'items.*.harga_jual' => 'required|numeric|min:0',
        ], [
            'toko_cabang_id.exists' => 'Opsi outlet yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'toko_cabang_id.required' => 'Wajib memilih outlet cabang toko.',
            'items.required' => 'Wajib memasukkan minimal satu barang ke dalam faktur!',
            'items.min' => 'Wajib memasukkan minimal satu barang ke dalam faktur!',
            'items.*.barang_id.exists' => 'Data barang yang dipilih tidak valid atau tidak terdaftar di sistem master!',
            'items.*.satuan_barang_id.exists' => 'Opsi satuan barang yang dipilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'items.*.qty.min' => 'Jumlah Qty barang minimal 0.01.',
            'items.*.harga_awal.min' => 'Harga awal tidak boleh bernilai negatif.',
            'items.*.harga_jual.min' => 'Harga penjualan tidak boleh bernilai negatif.',
        ]);

        // 2. Simpan Faktur dan Rincian Item dalam Database Transaction
        $faktur = DB::transaction(function () use ($validated) {
            $totalNominal = 0;
            $preparedDetails = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['qty'];
                $hargaAwal = (float) $item['harga_awal'];
                $hargaJual = (float) $item['harga_jual'];
                $subtotal = round($qty * $hargaAwal, 2);

                $totalNominal += $subtotal;

                $preparedDetails[] = [
                    'barang_id' => (int) $item['barang_id'],
                    'satuan_barang_id' => (int) $item['satuan_barang_id'],
                    'qty' => $qty,
                    'harga_awal' => $hargaAwal,
                    'harga_jual' => $hargaJual,
                    'subtotal' => $subtotal,
                ];
            }

            // Buat Record Header Faktur
            $fakturBarang = FakturBarang::create([
                'nomor_faktur' => $validated['nomor_faktur'],
                'toko_cabang_id' => (int) $validated['toko_cabang_id'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'pemasok' => $validated['pemasok'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'total_nominal' => $totalNominal,
                'user_id' => Auth::id(),
            ]);

            // Simpan Detail Items
            foreach ($preparedDetails as $detail) {
                $fakturBarang->details()->create($detail);
            }

            return $fakturBarang;
        });

        return redirect()->route('admin.cafe.faktur.index')
            ->with('success', "Faktur pembelian barang {$faktur->nomor_faktur} berhasil disimpan! Data harga penjualan telah tersimpan dan siap digunakan pada menu kasir.");
    }

    /**
     * Tampilkan detail rincian faktur barang.
     */
    public function show(FakturBarang $faktur)
    {
        $faktur->load(['tokoCabang', 'user', 'details.barang', 'details.satuanBarang']);

        return view('admin.cafe.faktur.show', compact('faktur'));
    }

    /**
     * Hapus faktur barang beserta itemnya.
     */
    public function destroy(FakturBarang $faktur)
    {
        DB::transaction(function () use ($faktur) {
            $nomorFaktur = $faktur->nomor_faktur;
            $faktur->details()->delete();
            $faktur->delete();
        });

        return redirect()->route('admin.cafe.faktur.index')
            ->with('success', 'Faktur barang berhasil dihapus dari sistem!');
    }
}
