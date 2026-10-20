<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JenisBiaya;
use App\Models\Pengeluaran;
use App\Models\TokoCabang;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    /**
     * Tampilkan daftar pengeluaran operasional studio & cafe.
     */
    public function index(Request $request)
    {
        $query = Pengeluaran::with(['tokoCabang', 'jenisBiaya', 'user']);

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
        $totalNominal = (clone $query)->sum('nominal');
        $totalTransaksi = (clone $query)->count();

        // Statistik bulanan (bulan ini)
        $pengeluaranBulanIni = Pengeluaran::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');

        $items = $query->orderBy('tanggal', 'desc')->latest('id')->paginate(10)->withQueryString();

        $tokoCabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();
        $jenisBiayas = JenisBiaya::orderBy('kategori', 'asc')->get();

        return view('admin.keuangan.pengeluaran.index', compact(
            'items',
            'tokoCabangs',
            'jenisBiayas',
            'totalNominal',
            'totalTransaksi',
            'pengeluaranBulanIni'
        ));
    }

    /**
     * Simpan data pengeluaran baru.
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
            'tanggal.required' => 'Tanggal pengeluaran wajib diisi.',
            'tanggal.date' => 'Format tanggal pengeluaran tidak valid.',
            'toko_cabang_id.required' => 'Toko cabang wajib dipilih.',
            'toko_cabang_id.exists' => 'Toko cabang yang dipilih tidak valid.',
            'jenis_biaya_id.required' => 'Kategori pengeluaran wajib dipilih.',
            'jenis_biaya_id.exists' => 'Kategori pengeluaran tidak valid atau telah dihapus.',
            'nominal.required' => 'Nominal pengeluaran wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka yang valid.',
            'nominal.min' => 'Nominal pengeluaran minimal Rp 1.',
        ]);

        $validated['user_id'] = auth()->id();

        Pengeluaran::create($validated);

        return redirect()->route('admin.keuangan.pengeluaran')
            ->with('success', 'Data pengeluaran operasional berhasil dicatat!');
    }

    /**
     * Perbarui data pengeluaran.
     */
    public function update(Request $request, Pengeluaran $pengeluaran)
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
            'tanggal.required' => 'Tanggal pengeluaran wajib diisi.',
            'tanggal.date' => 'Format tanggal pengeluaran tidak valid.',
            'toko_cabang_id.required' => 'Toko cabang wajib dipilih.',
            'toko_cabang_id.exists' => 'Toko cabang yang dipilih tidak valid.',
            'jenis_biaya_id.required' => 'Kategori pengeluaran wajib dipilih.',
            'jenis_biaya_id.exists' => 'Kategori pengeluaran tidak valid atau telah dihapus.',
            'nominal.required' => 'Nominal pengeluaran wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka yang valid.',
            'nominal.min' => 'Nominal pengeluaran minimal Rp 1.',
        ]);

        $pengeluaran->update($validated);

        return redirect()->route('admin.keuangan.pengeluaran')
            ->with('success', 'Data pengeluaran berhasil diperbarui!');
    }

    /**
     * Hapus data pengeluaran.
     */
    public function destroy(Pengeluaran $pengeluaran)
    {
        $info = 'Rp ' . number_format($pengeluaran->nominal, 0, ',', '.') . ' (' . ($pengeluaran->jenisBiaya?->kategori ?? 'Pengeluaran') . ')';
        $pengeluaran->delete();

        return redirect()->route('admin.keuangan.pengeluaran')
            ->with('success', "Data pengeluaran {$info} berhasil dihapus!");
    }
}
