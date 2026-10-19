<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\TokoCabang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Tampilkan daftar user sistem pada menu Pengaturan -> User.
     */
    public function index(Request $request)
    {
        $query = User::with(['tokoCabang', 'pegawai', 'jabatan']);

        // Filter Pencarian Teks (Username & Nama)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter Penempatan Outlet Cabang
        if ($request->filled('toko_cabang_id')) {
            $query->where('toko_cabang_id', $request->toko_cabang_id);
        }

        // Filter Jabatan / Role
        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        // Data Master untuk Formulir Modal
        $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();
        $pegawais = Pegawai::with('jabatan')->orderBy('nama_pegawai', 'asc')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan', 'asc')->get();

        // Statistik User
        $totalUser = User::count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalKasir = User::where('role', 'kasir')->count();

        return view('admin.pengaturan.user.index', compact(
            'items',
            'cabangs',
            'pegawais',
            'jabatans',
            'totalUser',
            'totalAdmin',
            'totalKasir'
        ));
    }

    /**
     * Simpan data user baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'toko_cabang_id' => 'nullable|exists:toko_cabang,id',
            'pegawai_id' => 'required|exists:pegawais,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
        ], [
            'toko_cabang_id.exists' => 'Outlet cabang yang dipilih tidak valid.',
            'pegawai_id.required' => 'Nama pegawai wajib dipilih dari data kepegawaian.',
            'pegawai_id.exists' => 'Pegawai yang dipilih tidak valid atau tidak ditemukan.',
            'jabatan_id.required' => 'Jabatan (role) wajib dipilih dari submenu jabatan.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid atau tidak terdaftar.',
            'username.required' => 'Username akun wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
        $jabatan = Jabatan::findOrFail($validated['jabatan_id']);

        // Jabatan digunakan sebagai role pada database
        $role = $this->determineRoleFromJabatan($jabatan->nama_jabatan);

        User::create([
            'name' => $pegawai->nama_pegawai,
            'pegawai_id' => $pegawai->id,
            'toko_cabang_id' => $validated['toko_cabang_id'] ?? null,
            'jabatan_id' => $jabatan->id,
            'role' => $role,
            'username' => strtolower(trim($validated['username'])),
            'password' => $validated['password'],
        ]);

        return redirect()->route('admin.pengaturan.user.index')
            ->with('success', 'User akun baru berhasil ditambahkan!');
    }

    /**
     * Perbarui data akun user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'toko_cabang_id' => 'nullable|exists:toko_cabang,id',
            'pegawai_id' => 'required|exists:pegawais,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
        ], [
            'toko_cabang_id.exists' => 'Outlet cabang yang dipilih tidak valid.',
            'pegawai_id.required' => 'Nama pegawai wajib dipilih dari data kepegawaian.',
            'pegawai_id.exists' => 'Pegawai yang dipilih tidak valid atau tidak ditemukan.',
            'jabatan_id.required' => 'Jabatan (role) wajib dipilih dari submenu jabatan.',
            'jabatan_id.exists' => 'Jabatan yang dipilih tidak valid atau tidak terdaftar.',
            'username.required' => 'Username akun wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan akun lain.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        $pegawai = Pegawai::findOrFail($validated['pegawai_id']);
        $jabatan = Jabatan::findOrFail($validated['jabatan_id']);

        // Jabatan digunakan sebagai role pada database
        $role = $this->determineRoleFromJabatan($jabatan->nama_jabatan);

        $payload = [
            'name' => $pegawai->nama_pegawai,
            'pegawai_id' => $pegawai->id,
            'toko_cabang_id' => $validated['toko_cabang_id'] ?? null,
            'jabatan_id' => $jabatan->id,
            'role' => $role,
            'username' => strtolower(trim($validated['username'])),
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()->route('admin.pengaturan.user.index')
            ->with('success', 'Data user berhasil diperbarui!');
    }

    /**
     * Hapus akun user dari sistem.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.pengaturan.user.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        $nama = $user->username;
        $user->delete();

        return redirect()->route('admin.pengaturan.user.index')
            ->with('success', "Akun user '{$nama}' berhasil dihapus dari sistem!");
    }

    /**
     * Menentukan nilai role database berdasarkan nama jabatan dari menu kepegawaian.
     */
    private function determineRoleFromJabatan(string $namaJabatan): string
    {
        $lower = strtolower(trim($namaJabatan));

        if (str_contains($lower, 'admin')) {
            return 'admin';
        }

        if (str_contains($lower, 'kasir') || str_contains($lower, 'cashier')) {
            return 'kasir';
        }

        return Str::slug($lower, '_');
    }
}
