<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TokoCabang;
use Illuminate\Http\Request;

class TokoCabangController extends Controller
{
    public function index(Request $request)
    {
        $query = TokoCabang::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_cabang', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kontak', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.toko-cabang.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'required|string|max:100',
        ]);

        TokoCabang::create($validated);

        return redirect()->route('admin.master.toko-cabang.index')
            ->with('success', 'Toko cabang baru berhasil ditambahkan!');
    }

    public function update(Request $request, TokoCabang $tokoCabang)
    {
        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kontak' => 'required|string|max:100',
        ]);

        $tokoCabang->update($validated);

        return redirect()->route('admin.master.toko-cabang.index')
            ->with('success', 'Data toko cabang berhasil diperbarui!');
    }

    public function destroy(TokoCabang $tokoCabang)
    {
        if ($tokoCabang->rooms()->count() > 0) {
            return redirect()->route('admin.master.toko-cabang.index')
                ->with('error', 'Tidak dapat menghapus cabang yang masih memiliki relasi room studio!');
        }

        $tokoCabang->delete();

        return redirect()->route('admin.master.toko-cabang.index')
            ->with('success', 'Toko cabang berhasil dihapus!');
    }
}
