<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MenuCafe;
use Illuminate\Http\Request;

class MenuCafeController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuCafe::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.menu-cafe.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:menu_cafe,nama',
        ]);

        MenuCafe::create($validated);

        return redirect()->route('admin.master.menu-cafe.index')
            ->with('success', 'Kategori cafe berhasil ditambahkan!');
    }

    public function update(Request $request, MenuCafe $menuCafe)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:menu_cafe,nama,' . $menuCafe->id,
        ]);

        $menuCafe->update($validated);

        return redirect()->route('admin.master.menu-cafe.index')
            ->with('success', 'Kategori cafe berhasil diperbarui!');
    }

    public function destroy(MenuCafe $menuCafe)
    {
        $menuCafe->delete();

        return redirect()->route('admin.master.menu-cafe.index')
            ->with('success', 'Kategori cafe berhasil dihapus!');
    }
}
