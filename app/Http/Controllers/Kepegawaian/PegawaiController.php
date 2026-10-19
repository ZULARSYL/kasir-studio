<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Tampilkan daftar pegawai pada modul Kepegawaian.
     */
    public function index(Request $request)
    {
        $query = Pegawai::with('jabatan');

        // Filter Pencarian Teks (Nama, No HP, Alamat)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_pegawai', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        // Filter Jabatan
        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $jabatans = Jabatan::orderBy('nama_jabatan', 'asc')->get();

        // Statistik
        $totalPegawai = Pegawai::count();
        $totalJabatan = $jabatans->count();
        $pegawaiBulanIni = Pegawai::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.kepegawaian.pegawai.index', compact(
            'items',
            'jabatans',
            'totalPegawai',
            'totalJabatan',
            'pegawaiBulanIni'
        ));
    }

    /**
     * Simpan data pegawai baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pegawai' => 'required|string|max:255',
            'jabatan_id' => 'required|exists:jabatans,id',
            'nomor_hp' => 'required|string|max:30',
            'alamat' => 'required|string|max:1000',
        ], [
            'nama_pegawai.required' => 'Nama pegawai wajib diisi.',
            'jabatan_id.required' => 'Jabatan pegawai wajib dipilih.',
            'jabatan_id.exists' => 'Pilihan jabatan tidak valid atau telah dihapus.',
            'nomor_hp.required' => 'Nomor HP wajib diisi.',
            'alamat.required' => 'Alamat pegawai wajib diisi.',
        ]);

        Pegawai::create($validated);

        return redirect()->route('admin.kepegawaian.pegawai.index')
            ->with('success', 'Data pegawai baru berhasil ditambahkan!');
    }

    /**
     * Perbarui data pegawai.
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $request->validate([
            'nama_pegawai' => 'required|string|max:255',
            'jabatan_id' => 'required|exists:jabatans,id',
            'nomor_hp' => 'required|string|max:30',
            'alamat' => 'required|string|max:1000',
        ], [
            'nama_pegawai.required' => 'Nama pegawai wajib diisi.',
            'jabatan_id.required' => 'Jabatan pegawai wajib dipilih.',
            'jabatan_id.exists' => 'Pilihan jabatan tidak valid atau telah dihapus.',
            'nomor_hp.required' => 'Nomor HP wajib diisi.',
            'alamat.required' => 'Alamat pegawai wajib diisi.',
        ]);

        $pegawai->update($validated);

        return redirect()->route('admin.kepegawaian.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    /**
     * Hapus data pegawai.
     */
    public function destroy(Pegawai $pegawai)
    {
        $nama = $pegawai->nama_pegawai;
        $pegawai->delete();

        return redirect()->route('admin.kepegawaian.pegawai.index')
            ->with('success', "Pegawai '{$nama}' berhasil dihapus dari sistem!");
    }
}
