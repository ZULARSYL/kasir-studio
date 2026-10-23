<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\TemplateStruk;
use App\Models\TokoCabang;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateStrukController extends Controller
{
    protected ?ImageCompressionService $compressionService;

    public function __construct(?ImageCompressionService $compressionService = null)
    {
        $this->compressionService = $compressionService;
    }

    /**
     * Tampilkan halaman kelola template struk dengan live preview side-by-side.
     */
    public function index(Request $request)
    {
        // Pastikan minimal ada 1 template aktif
        TemplateStruk::getActiveTemplate();

        $templates = TemplateStruk::orderBy('is_aktif', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedId = $request->query('template_id');
        $selectedTemplate = $selectedId 
            ? $templates->firstWhere('id', $selectedId) 
            : $templates->firstWhere('is_aktif', true);

        if (!$selectedTemplate && $templates->isNotEmpty()) {
            $selectedTemplate = $templates->first();
        }

        $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();

        // Data sampel transaksi untuk review / preview visual langsung
        $sampleData = [
            'nomor_transaksi' => 'TRX-' . date('Ymd') . '-0042',
            'waktu_transaksi' => now()->translatedFormat('d/m/Y H:i'),
            'nama_kasir' => auth()->user()?->name ?? 'Kasir Utama',
            'nama_pelanggan' => 'Budi Santoso',
            'nama_cabang' => $cabangs->first()?->nama_cabang ?? 'Pressent Studio Pekanbaru (Pusat)',
            'alamat_cabang' => $cabangs->first()?->alamat ?? 'Jl. Arifin Ahmad No. 88, Pekanbaru',
            'kontak_cabang' => $cabangs->first()?->kontak ?? '0812-3456-7890',
            'film_judul' => 'Avatar: Fire and Ash (Premiere 4K)',
            'room_nama' => 'Studio Room VIP 1',
            'room_kapasitas' => 4,
            'durasi_menit' => 120,
            'jumlah_penonton' => 3,
            'denda_penonton' => 5000,
            'subtotal_film' => 85000,
            'cafe_items' => [
                [
                    'nama_barang' => 'Popcorn Caramel Jumbo',
                    'qty' => 1,
                    'satuan' => 'box',
                    'harga_satuan' => 25000,
                    'subtotal' => 25000,
                ],
                [
                    'nama_barang' => 'Iced Lychee Tea',
                    'qty' => 2,
                    'satuan' => 'cup',
                    'harga_satuan' => 18000,
                    'subtotal' => 36000,
                ],
            ],
            'subtotal_cafe' => 61000,
            'diskon' => 15000,
            'nama_promo' => 'PROMO WEEKEND SERU',
            'total_bayar' => 131000,
            'metode_bayar' => 'QRIS BCA',
            'uang_diterima' => 150000,
            'kembalian' => 19000,
            'waktu_cetak' => now()->translatedFormat('d-m-Y H:i:s'),
        ];

        return view('admin.pengaturan.struk.index', compact(
            'templates',
            'selectedTemplate',
            'cabangs',
            'sampleData'
        ));
    }

    /**
     * Simpan template struk baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:100',
            'lebar_kertas' => 'required|in:80mm,58mm',
            'font_size' => 'required|in:small,medium,large',
            'nama_usaha' => 'required|string|max:150',
            'sub_judul' => 'nullable|string|max:255',
            'mode_cabang' => 'required|in:otomatis,manual',
            'nama_cabang_manual' => 'nullable|string|max:150',
            'alamat' => 'nullable|string|max:500',
            'telepon' => 'nullable|string|max:50',
            'website_sosmed' => 'nullable|string|max:100',
            'pesan_header' => 'nullable|string|max:500',
            'pesan_footer_1' => 'nullable|string|max:255',
            'pesan_footer_2' => 'nullable|string|max:500',
            'catatan_kaki' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $booleanFields = [
            'tampilkan_logo',
            'tampilkan_cabang',
            'tampilkan_alamat',
            'tampilkan_kontak',
            'tampilkan_no_nota',
            'tampilkan_waktu',
            'tampilkan_kasir',
            'tampilkan_pelanggan',
            'tampilkan_detail_room',
            'tampilkan_detail_penonton',
            'tampilkan_detail_cafe',
            'tampilkan_diskon',
            'tampilkan_metode_bayar',
            'tampilkan_kembalian',
            'tampilkan_barcode',
            'tampilkan_waktu_cetak',
            'is_aktif',
        ];

        $data = $validated;
        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        // Handle upload logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_struk_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/struk', $filename, 'public');
            $data['logo_path'] = 'storage/' . $path;
            $data['tampilkan_logo'] = true;
        }

        if (!empty($data['is_aktif'])) {
            TemplateStruk::query()->update(['is_aktif' => false]);
        }

        $template = TemplateStruk::create($data);

        return redirect()->route('admin.pengaturan.struk', ['template_id' => $template->id])
            ->with('success', "Template struk '{$template->nama_template}' berhasil dibuat.");
    }

    /**
     * Update template struk yang dipilih.
     */
    public function update(Request $request, $id)
    {
        $template = TemplateStruk::findOrFail($id);

        $validated = $request->validate([
            'nama_template' => 'required|string|max:100',
            'lebar_kertas' => 'required|in:80mm,58mm',
            'font_size' => 'required|in:small,medium,large',
            'nama_usaha' => 'required|string|max:150',
            'sub_judul' => 'nullable|string|max:255',
            'mode_cabang' => 'required|in:otomatis,manual',
            'nama_cabang_manual' => 'nullable|string|max:150',
            'alamat' => 'nullable|string|max:500',
            'telepon' => 'nullable|string|max:50',
            'website_sosmed' => 'nullable|string|max:100',
            'pesan_header' => 'nullable|string|max:500',
            'pesan_footer_1' => 'nullable|string|max:255',
            'pesan_footer_2' => 'nullable|string|max:500',
            'catatan_kaki' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $booleanFields = [
            'tampilkan_logo',
            'tampilkan_cabang',
            'tampilkan_alamat',
            'tampilkan_kontak',
            'tampilkan_no_nota',
            'tampilkan_waktu',
            'tampilkan_kasir',
            'tampilkan_pelanggan',
            'tampilkan_detail_room',
            'tampilkan_detail_penonton',
            'tampilkan_detail_cafe',
            'tampilkan_diskon',
            'tampilkan_metode_bayar',
            'tampilkan_kembalian',
            'tampilkan_barcode',
            'tampilkan_waktu_cetak',
            'is_aktif',
        ];

        $data = $validated;
        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        // Handle upload logo baru
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_struk_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/struk', $filename, 'public');
            $data['logo_path'] = 'storage/' . $path;
            $data['tampilkan_logo'] = true;
        }

        if (!empty($data['is_aktif'])) {
            TemplateStruk::where('id', '!=', $template->id)->update(['is_aktif' => false]);
        }

        $template->update($data);

        return redirect()->route('admin.pengaturan.struk', ['template_id' => $template->id])
            ->with('success', "Template struk '{$template->nama_template}' berhasil diperbarui.");
    }

    /**
     * Aktifkan template struk tertentu.
     */
    public function setActive($id)
    {
        $template = TemplateStruk::findOrFail($id);
        
        TemplateStruk::where('id', '!=', $id)->update(['is_aktif' => false]);
        $template->update(['is_aktif' => true]);

        return redirect()->route('admin.pengaturan.struk', ['template_id' => $template->id])
            ->with('success', "Template '{$template->nama_template}' kini aktif sebagai template cetak struk kasir.");
    }

    /**
     * Duplikat template struk yang sudah ada.
     */
    public function duplicate($id)
    {
        $source = TemplateStruk::findOrFail($id);
        $newTemplate = $source->replicate();
        $newTemplate->nama_template = $source->nama_template . ' (Salinan)';
        $newTemplate->is_aktif = false;
        $newTemplate->save();

        return redirect()->route('admin.pengaturan.struk', ['template_id' => $newTemplate->id])
            ->with('success', "Template berhasil disalin sebagai '{$newTemplate->nama_template}'.");
    }

    /**
     * Hapus template struk.
     */
    public function destroy($id)
    {
        $count = TemplateStruk::count();
        if ($count <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus. Sistem harus memiliki minimal satu template struk.');
        }

        $template = TemplateStruk::findOrFail($id);
        $wasActive = $template->is_aktif;
        $nama = $template->nama_template;
        $template->delete();

        if ($wasActive) {
            $next = TemplateStruk::first();
            if ($next) {
                $next->update(['is_aktif' => true]);
            }
        }

        return redirect()->route('admin.pengaturan.struk')
            ->with('success', "Template '{$nama}' berhasil dihapus.");
    }
}
