<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TipePembayaran;
use Illuminate\Http\Request;

class TipePembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = TipePembayaran::query();

        if ($request->filled('search')) {
            $query->where('metode_pembayaran', 'like', "%{$request->search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.tipe-pembayaran.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'metode_pembayaran' => 'required|string|max:255|unique:tipe_pembayarans,metode_pembayaran',
        ]);

        TipePembayaran::create($validated);

        return redirect()->route('admin.master.tipe-pembayaran.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan!');
    }

    public function update(Request $request, TipePembayaran $tipePembayaran)
    {
        $validated = $request->validate([
            'metode_pembayaran' => 'required|string|max:255|unique:tipe_pembayarans,metode_pembayaran,' . $tipePembayaran->id,
        ]);

        $tipePembayaran->update($validated);

        return redirect()->route('admin.master.tipe-pembayaran.index')
            ->with('success', 'Metode pembayaran berhasil diperbarui!');
    }

    public function destroy(TipePembayaran $tipePembayaran)
    {
        $tipePembayaran->delete();

        return redirect()->route('admin.master.tipe-pembayaran.index')
            ->with('success', 'Metode pembayaran berhasil dihapus!');
    }
}
