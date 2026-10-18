<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        $query = Promo::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_voucher', 'like', "%{$search}%")
                  ->orWhere('nama_promo', 'like', "%{$search}%")
                  ->orWhere('nilai', 'like', "%{$search}%")
                  ->orWhere('periode_berlaku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.promo.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePromo($request);

        Promo::create($validated);

        return redirect()->route('admin.master.promo.index')
            ->with('success', 'Promo voucher baru berhasil ditambahkan!');
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $this->validatePromo($request, $promo);

        $promo->update($validated);

        return redirect()->route('admin.master.promo.index')
            ->with('success', 'Data promo voucher berhasil diperbarui!');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();

        return redirect()->route('admin.master.promo.index')
            ->with('success', 'Promo berhasil dihapus!');
    }

    private function validatePromo(Request $request, ?Promo $promo = null): array
    {
        $kodeVoucherRule = 'required|string|max:100|unique:promos,kode_voucher';
        if ($promo) {
            $kodeVoucherRule .= ',' . $promo->id;
        }

        $validated = $request->validate([
            'kode_voucher' => $kodeVoucherRule,
            'nama_promo' => 'required|string|max:255',
            'jenis_diskon' => 'required|in:potongan,persentase',
            'jumlah_diskon' => $request->input('jenis_diskon') === 'persentase'
                ? 'required|numeric|gt:0|lte:100'
                : 'required|integer|min:1|max:999999999999',
            'periode_mulai' => 'required|date_format:Y-m-d',
            'periode_selesai' => 'required|date_format:Y-m-d|after_or_equal:periode_mulai',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $jumlahDiskon = $validated['jenis_diskon'] === 'persentase'
            ? rtrim(rtrim(number_format((float) $validated['jumlah_diskon'], 2, '.', ''), '0'), '.') . '%'
            : 'Rp ' . number_format((int) $validated['jumlah_diskon'], 0, ',', '.');

        return [
            'kode_voucher' => $validated['kode_voucher'],
            'nama_promo' => $validated['nama_promo'],
            'nilai' => $jumlahDiskon,
            'periode_berlaku' => $validated['periode_mulai'] . ' - ' . $validated['periode_selesai'],
            'status' => $validated['status'],
        ];
    }
}
