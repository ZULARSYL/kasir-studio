<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\HakAkses;
use App\Models\Jabatan;
use App\Models\User;
use App\Services\HakAksesService;
use Illuminate\Http\Request;

class HakAksesController extends Controller
{
    /**
     * Tampilkan halaman konfigurasi Hak Akses menu & submenu per role / jabatan.
     */
    public function index(Request $request)
    {
        // Tentukan target yang sedang aktif dipilih (default: role admin atau kasir)
        $tipe = $request->query('tipe', 'role'); // 'role' atau 'jabatan'
        $role = $request->query('role', 'kasir'); // default kasir
        $jabatanId = $request->query('jabatan_id') ? (int) $request->query('jabatan_id') : null;

        $jabatans = Jabatan::orderBy('nama_jabatan', 'asc')->get();

        // Jika tipe jabatan tapi belum ada jabatanId, pilih jabatan pertama
        if ($tipe === 'jabatan' && ! $jabatanId && $jabatans->isNotEmpty()) {
            $jabatanId = $jabatans->first()->id;
        }

        // Ambil struktur seluruh modul menu & submenu
        $menuStructure = HakAksesService::getMenuStructure();

        // Ambil daftar permissions yang saat ini aktif untuk target terpilih
        $currentPermissions = HakAksesService::getPermissions($tipe, $role, $jabatanId);

        // Metadata nama target
        $targetName = '';
        if ($tipe === 'role') {
            $targetName = $role === 'admin' ? 'Administrator' : 'Kasir';
        } else {
            $activeJabatan = $jabatans->firstWhere('id', $jabatanId);
            $targetName = $activeJabatan ? "Jabatan: {$activeJabatan->nama_jabatan}" : 'Jabatan Tidak Diketahui';
        }

        // Statistik ringkas user
        $stats = [
            'total_admin' => User::where('role', 'admin')->count(),
            'total_kasir' => User::where('role', 'kasir')->count(),
            'total_jabatan' => $jabatans->count(),
        ];

        return view('admin.pengaturan.hak_akses.index', compact(
            'menuStructure',
            'tipe',
            'role',
            'jabatanId',
            'jabatans',
            'currentPermissions',
            'targetName',
            'stats'
        ));
    }

    /**
     * Simpan perubahan hak akses menu & submenu untuk role atau jabatan.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|in:role,jabatan',
            'role' => 'nullable|string|in:admin,kasir',
            'jabatan_id' => 'nullable|exists:jabatans,id',
            'menus' => 'nullable|array',
            'menus.*' => 'string',
        ]);

        $tipe = $validated['tipe'];
        $role = $tipe === 'role' ? ($validated['role'] ?? 'kasir') : null;
        $jabatanId = $tipe === 'jabatan' ? ($validated['jabatan_id'] ?? null) : null;
        $menus = $request->input('menus', []);

        HakAksesService::savePermissions($tipe, $menus, $role, $jabatanId);

        $targetName = $tipe === 'role' 
            ? (ucfirst($role)) 
            : ('Jabatan ' . (Jabatan::find($jabatanId)?->nama_jabatan ?? ''));

        return redirect()->route('admin.pengaturan.hak-akses', [
            'tipe' => $tipe,
            'role' => $role,
            'jabatan_id' => $jabatanId,
        ])->with('success', "Hak akses menu & submenu untuk {$targetName} berhasil disimpan dan diperbarui!");
    }
}
