<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;

class SatuanBarangController extends Controller
{
    public function index(Request $request)
    {
        $query = SatuanBarang::query();

        if ($request->filled('search')) {
            $query->where('nama_satuan', 'like', "%{$request->search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.satuan-barang.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:100|unique:satuan_barangs,nama_satuan',
        ], [
            'nama_satuan.required' => 'Nama satuan barang wajib diisi.',
            'nama_satuan.unique' => 'Nama satuan barang ini sudah terdaftar.',
        ]);

        SatuanBarang::create($validated);

        return redirect()->route('admin.master.satuan-barang.index')
            ->with('success', 'Master satuan barang baru berhasil ditambahkan!');
    }

    public function update(Request $request, SatuanBarang $satuanBarang)
    {
        $validated = $request->validate([
            'nama_satuan' => 'required|string|max:100|unique:satuan_barangs,nama_satuan,' . $satuanBarang->id,
        ], [
            'nama_satuan.required' => 'Nama satuan barang wajib diisi.',
            'nama_satuan.unique' => 'Nama satuan barang ini sudah terdaftar.',
        ]);

        $satuanBarang->update($validated);

        return redirect()->route('admin.master.satuan-barang.index')
            ->with('success', 'Data satuan barang berhasil diperbarui!');
    }

    public function destroy(SatuanBarang $satuanBarang)
    {
        $satuanBarang->delete();

        return redirect()->route('admin.master.satuan-barang.index')
            ->with('success', 'Satuan barang berhasil dihapus!');
    }
}
