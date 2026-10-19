<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\MenuCafe;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with('menuCafe');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('nama_barang', 'like', "%{$search}%");
        }

        if ($request->filled('menu_cafe_id')) {
            $query->where('menu_cafe_id', $request->menu_cafe_id);
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $menuCafes = MenuCafe::orderBy('nama', 'asc')->get();

        return view('admin.master.barang.index', compact('items', 'menuCafes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'menu_cafe_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_cafe', 'id'),
            ],
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'menu_cafe_id.exists' => 'Opsi menu cafe yang dipilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
        ]);

        Barang::create($validated);

        return redirect()->route('admin.master.barang.index')
            ->with('success', 'Master data barang baru berhasil ditambahkan!');
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'menu_cafe_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_cafe', 'id'),
            ],
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'menu_cafe_id.exists' => 'Opsi menu cafe yang dipilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
        ]);

        $barang->update($validated);

        return redirect()->route('admin.master.barang.index')
            ->with('success', 'Data master barang berhasil diperbarui!');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('admin.master.barang.index')
            ->with('success', 'Data barang berhasil dihapus!');
    }
}
