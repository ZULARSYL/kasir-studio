<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Jabatan::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_jabatan', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.jabatan.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
        ]);

        Jabatan::create($validated);

        return redirect()->route('admin.master.jabatan.index')
            ->with('success', 'Master jabatan baru berhasil ditambahkan!');
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan,' . $jabatan->id,
        ]);

        $jabatan->update($validated);

        return redirect()->route('admin.master.jabatan.index')
            ->with('success', 'Data master jabatan berhasil diperbarui!');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();

        return redirect()->route('admin.master.jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus!');
    }
}
