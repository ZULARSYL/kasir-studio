<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\HargaPerdurasi;
use Illuminate\Http\Request;

class HargaPerdurasiController extends Controller
{
    public function index(Request $request)
    {
        $query = HargaPerdurasi::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('durasi', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.harga-perdurasi.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'durasi_menit' => 'required|integer|min:1|max:100000',
            'kalkulasi_harga' => 'required|numeric|min:0',
        ]);

        HargaPerdurasi::create([
            'nama' => $validated['nama'],
            'durasi' => $validated['durasi_menit'] . ' Menit',
            'kalkulasi_harga' => $validated['kalkulasi_harga'],
        ]);

        return redirect()->route('admin.master.harga-perdurasi.index')
            ->with('success', 'Master durasi berhasil ditambahkan!');
    }

    public function update(Request $request, HargaPerdurasi $hargaPerdurasi)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'durasi_menit' => 'required|integer|min:1|max:100000',
            'kalkulasi_harga' => 'required|numeric|min:0',
        ]);

        $hargaPerdurasi->update([
            'nama' => $validated['nama'],
            'durasi' => $validated['durasi_menit'] . ' Menit',
            'kalkulasi_harga' => $validated['kalkulasi_harga'],
        ]);

        return redirect()->route('admin.master.harga-perdurasi.index')
            ->with('success', 'Master durasi berhasil diperbarui!');
    }

    public function destroy(HargaPerdurasi $hargaPerdurasi)
    {
        $hargaPerdurasi->delete();

        return redirect()->route('admin.master.harga-perdurasi.index')
            ->with('success', 'Master durasi berhasil dihapus!');
    }
}
