<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KasirShift;
use App\Models\Shift;
use App\Models\TokoCabang;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Tampilkan antarmuka Master Data Shift pada sisi Admin.
     * Dilengkapi monitoring shift yang sedang aktif per cabang dan riwayat pergantian shift.
     */
    public function index(Request $request)
    {
        $query = Shift::ordered();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_shift', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhere('jam_mulai', 'like', "%{$search}%")
                    ->orWhere('jam_selesai', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(10)->withQueryString();

        $totalShift = Shift::count();
        $totalShiftAktif = Shift::where('status', 'aktif')->count();

        // 1. Monitoring Shift yang Sedang Berjalan (Aktif) di Seluruh Cabang Hari Ini
        $activeBranchShifts = KasirShift::with(['user', 'tokoCabang', 'shift'])
            ->where('status', 'buka')
            ->latest('waktu_buka')
            ->get();

        // 2. Riwayat Pergantian Shift & Tutup Kasir Terakhir (Rule 5 & 7)
        $shiftHandovers = KasirShift::with(['user', 'tokoCabang', 'shift', 'previousShift.user'])
            ->whereDate('waktu_buka', '>=', Carbon::today()->subDays(2))
            ->latest('waktu_buka')
            ->paginate(10, ['*'], 'riwayat_page')
            ->withQueryString();

        $cabangs = TokoCabang::orderBy('nama_cabang')->get();

        return view('admin.master.shift.index', compact(
            'items',
            'totalShift',
            'totalShiftAktif',
            'activeBranchShifts',
            'shiftHandovers',
            'cabangs'
        ));
    }

    /**
     * Simpan konfigurasi shift baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_shift' => 'required|string|max:100',
            'jam_mulai' => 'required|string|max:10',
            'jam_selesai' => 'required|string|max:10',
            'aturan_modal_awal' => 'required|in:manual,otomatis_sebelumnya',
            'urutan' => 'nullable|integer|min:1',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $validated['urutan'] = $validated['urutan'] ?? ((int) Shift::max('urutan') + 1);

        Shift::create($validated);

        return redirect()->route('admin.master.shift.index')
            ->with('success', "Master data shift '{$validated['nama_shift']}' berhasil ditambahkan!");
    }

    /**
     * Perbarui konfigurasi shift.
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'nama_shift' => 'required|string|max:100',
            'jam_mulai' => 'required|string|max:10',
            'jam_selesai' => 'required|string|max:10',
            'aturan_modal_awal' => 'required|in:manual,otomatis_sebelumnya',
            'urutan' => 'nullable|integer|min:1',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $shift->update($validated);

        return redirect()->route('admin.master.shift.index')
            ->with('success', "Konfigurasi master shift '{$shift->nama_shift}' berhasil diperbarui!");
    }

    /**
     * Ubah status aktif/nonaktif shift secara instan.
     */
    public function toggleStatus($id)
    {
        $shift = Shift::findOrFail($id);
        $newStatus = $shift->status === 'aktif' ? 'nonaktif' : 'aktif';
        $shift->update(['status' => $newStatus]);

        $statusText = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.master.shift.index')
            ->with('success', "Shift '{$shift->nama_shift}' berhasil {$statusText}!");
    }

    /**
     * Hapus master shift jika belum pernah digunakan dalam riwayat transaksi.
     */
    public function destroy(Shift $shift)
    {
        $usedCount = $shift->kasirShifts()->count();

        if ($usedCount > 0) {
            // Jika sudah digunakan dalam transaksi kasir, nonaktifkan saja untuk integritas data
            $shift->update(['status' => 'nonaktif']);

            return redirect()->route('admin.master.shift.index')
                ->with('success', "Shift '{$shift->nama_shift}' memiliki {$usedCount} riwayat sesi kasir sehingga otomatis diubah statusnya menjadi Nonaktif.");
        }

        $shiftName = $shift->nama_shift;
        $shift->delete();

        return redirect()->route('admin.master.shift.index')
            ->with('success', "Master shift '{$shiftName}' berhasil dihapus!");
    }
}
