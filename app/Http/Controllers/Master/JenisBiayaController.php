<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\JenisBiaya;
use Illuminate\Http\Request;

class JenisBiayaController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisBiaya::query();

        if ($request->filled('search')) {
            $query->where('kategori', 'like', "%{$request->search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.jenis-biaya.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:255|unique:jenis_biayas,kategori',
        ]);

        JenisBiaya::create($validated);

        return redirect()->route('admin.master.jenis-biaya.index')
            ->with('success', 'Kategori biaya berhasil ditambahkan!');
    }

    public function update(Request $request, JenisBiaya $jenisBiaya)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:255|unique:jenis_biayas,kategori,' . $jenisBiaya->id,
        ]);

        $jenisBiaya->update($validated);

        return redirect()->route('admin.master.jenis-biaya.index')
            ->with('success', 'Kategori biaya berhasil diperbarui!');
    }

    public function destroy(JenisBiaya $jenisBiaya)
    {
        $jenisBiaya->delete();

        return redirect()->route('admin.master.jenis-biaya.index')
            ->with('success', 'Kategori biaya berhasil dihapus!');
    }
}
