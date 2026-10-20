<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JenisBiaya;
use App\Models\Pemasukan;
use App\Models\TokoCabang;
use Illuminate\Http\Request;

class PemasukanController extends Controller
{
    /**
     * Tampilkan daftar pemasukan manual di luar transaksi kasir.
     */
    public function index(Request $request)
    {
        $query = Pemasukan::with(['tokoCabang', 'jenisBiaya', 'user']);

        // Filter Cabang
        if ($request->filled('toko_cabang_id')) {
            $query->where('toko_cabang_id', $request->toko_cabang_id);
        }

        // Filter Kategori (Jenis Biaya)
        if ($request->filled('jenis_biaya_id')) {
            $query->where('jenis_biaya_id', $request->jenis_biaya_id);
        }

        // Filter Tanggal Mulai & Akhir
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter Pencarian Keterangan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('jenisBiaya', function ($j) use ($search) {
                      $j->where('kategori', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tokoCabang', function ($c) use ($search) {
                      $c->where('nama_cabang', 'like', "%{$search}%");
                  });
            });
        }

        // Clone query untuk kalkulasi statistik berdasarkan filter
        $statQuery = clone $query;
        $totalNominal = (clone $query)->sum('nominal');
        $totalTransaksi = (clone $query)->count();

        // Statistik bulanan (bulan ini)
        $pemasukanBulanIni = Pemasukan::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');

        $items = $query->orderBy('tanggal', 'desc')->latest('id')->paginate(10)->withQueryString();

        $tokoCabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();
        $jenisBiayas = JenisBiaya::orderBy('kategori', 'asc')->get();

        return view('admin.keuangan.pemasukan.index', compact(
            'items',
            'tokoCabangs',
            'jenisBiayas',
            'totalNominal',
            'totalTransaksi',
            'pemasukanBulanIni'
        ));
    }

    /**
     * Simpan data pemasukan baru.
     */
    public function store(Request $request)
    {
        if ($request->filled('nominal')) {
            $rawNominal = trim((string)$request->nominal);
            $isNegative = str_starts_with($rawNominal, '-');
            $cleaned = preg_replace('/[^0-9]/', '', $rawNominal);
            if ($cleaned !== '') {
                $request->merge(['nominal' => $isNegative ? -((float)$cleaned) : (float)$cleaned]);
            }
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'toko_cabang_id' => 'required|exists:toko_cabang,id',
            'jenis_biaya_id' => 'required|exists:jenis_biayas,id',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'tanggal.required' => 'Tanggal pemasukan wajib diisi.',
            'tanggal.date' => 'Format tanggal pemasukan tidak valid.',
            'toko_cabang_id.required' => 'Toko cabang wajib dipilih.',
            'toko_cabang_id.exists' => 'Toko cabang yang dipilih tidak valid.',
            'jenis_biaya_id.required' => 'Kategori pemasukan wajib dipilih.',
            'jenis_biaya_id.exists' => 'Kategori pemasukan tidak valid atau telah dihapus.',
            'nominal.required' => 'Nominal pemasukan wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka yang valid.',
            'nominal.min' => 'Nominal pemasukan minimal Rp 1.',
        ]);

        $validated['user_id'] = auth()->id();

        Pemasukan::create($validated);

        return redirect()->route('admin.keuangan.pemasukan')
            ->with('success', 'Data pemasukan baru berhasil dicatat!');
    }

    /**
     * Perbarui data pemasukan.
     */
    public function update(Request $request, Pemasukan $pemasukan)
    {
        if ($request->filled('nominal')) {
            $rawNominal = trim((string)$request->nominal);
            $isNegative = str_starts_with($rawNominal, '-');
            $cleaned = preg_replace('/[^0-9]/', '', $rawNominal);
            if ($cleaned !== '') {
                $request->merge(['nominal' => $isNegative ? -((float)$cleaned) : (float)$cleaned]);
            }
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'toko_cabang_id' => 'required|exists:toko_cabang,id',
            'jenis_biaya_id' => 'required|exists:jenis_biayas,id',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'tanggal.required' => 'Tanggal pemasukan wajib diisi.',
            'tanggal.date' => 'Format tanggal pemasukan tidak valid.',
            'toko_cabang_id.required' => 'Toko cabang wajib dipilih.',
            'toko_cabang_id.exists' => 'Toko cabang yang dipilih tidak valid.',
            'jenis_biaya_id.required' => 'Kategori pemasukan wajib dipilih.',
            'jenis_biaya_id.exists' => 'Kategori pemasukan tidak valid atau telah dihapus.',
            'nominal.required' => 'Nominal pemasukan wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka yang valid.',
            'nominal.min' => 'Nominal pemasukan minimal Rp 1.',
        ]);

        $pemasukan->update($validated);

        return redirect()->route('admin.keuangan.pemasukan')
            ->with('success', 'Data pemasukan berhasil diperbarui!');
    }

    /**
     * Hapus data pemasukan.
     */
    public function destroy(Pemasukan $pemasukan)
    {
        $info = 'Rp ' . number_format($pemasukan->nominal, 0, ',', '.') . ' (' . ($pemasukan->jenisBiaya?->kategori ?? 'Pemasukan') . ')';
        $pemasukan->delete();

        return redirect()->route('admin.keuangan.pemasukan')
            ->with('success', "Data pemasukan {$info} berhasil dihapus!");
    }
}
