<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    /**
     * Tampilkan daftar master jabatan pegawai pada modul Kepegawaian.
     */
    public function index(Request $request)
    {
        $query = Jabatan::withCount('pegawais');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_jabatan', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $totalJabatan = Jabatan::count();

        return view('admin.kepegawaian.jabatan.index', compact('items', 'totalJabatan'));
    }

    /**
     * Simpan data nama jabatan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique' => 'Nama jabatan ini sudah terdaftar.',
            'nama_jabatan.max' => 'Nama jabatan maksimal 255 karakter.',
        ]);

        Jabatan::create($validated);

        return redirect()->route('admin.kepegawaian.jabatan.index')
            ->with('success', 'Jabatan baru berhasil ditambahkan!');
    }

    /**
     * Perbarui data nama jabatan.
     */
    public function update(Request $request, Jabatan $jabatan)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan,' . $jabatan->id,
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique' => 'Nama jabatan ini sudah digunakan.',
            'nama_jabatan.max' => 'Nama jabatan maksimal 255 karakter.',
        ]);

        $jabatan->update($validated);

        return redirect()->route('admin.kepegawaian.jabatan.index')
            ->with('success', 'Data jabatan berhasil diperbarui!');
    }

    /**
     * Hapus data jabatan.
     */
    public function destroy(Jabatan $jabatan)
    {
        $nama = $jabatan->nama_jabatan;
        $jabatan->delete();

        return redirect()->route('admin.kepegawaian.jabatan.index')
            ->with('success', "Jabatan '{$nama}' berhasil dihapus!");
    }
}
