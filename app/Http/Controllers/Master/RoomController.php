<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\TokoCabang;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('tokoCabang');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        if ($request->filled('toko_cabang_id')) {
            $query->where('toko_cabang_id', $request->toko_cabang_id);
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $cabangs = TokoCabang::orderBy('nama_cabang')->get();

        return view('admin.master.room.index', compact('items', 'cabangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'toko_cabang_id' => 'required|exists:toko_cabang,id',
        ]);

        Room::create($validated);

        return redirect()->route('admin.master.room.index')
            ->with('success', 'Room studio baru berhasil ditambahkan!');
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kapasitas' => 'required|integer|min:1',
            'toko_cabang_id' => 'required|exists:toko_cabang,id',
        ]);

        $room->update($validated);

        return redirect()->route('admin.master.room.index')
            ->with('success', 'Data room studio berhasil diperbarui!');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.master.room.index')
            ->with('success', 'Room studio berhasil dihapus!');
    }
}
