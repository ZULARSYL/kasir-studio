<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\GenreFilm;
use App\Models\KasirShift;
use App\Models\MenuCafe;
use App\Models\Promo;
use App\Models\Room;
use App\Models\SatuanBarang;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    /**
     * Tampilkan antarmuka Menu Kasir (POS).
     * Jika kasir belum membuka shift/modal awal, arahkan ke form modal awal.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeShift = KasirShift::getActiveShift($user->id);

        // Jika belum ada shift aktif, muat form Buka Kasir (Modal Awal)
        if (! $activeShift) {
            $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();
            return view('kasir.kasir', [
                'hasActiveShift' => false,
                'activeShift' => null,
                'cabangs' => $cabangs,
                'assignedCabang' => $user->tokoCabang,
            ]);
        }

        // Shift sedang aktif, muat data transaksi POS
        $shift = $activeShift->load('tokoCabang');

        $now = Carbon::now();

        // 1. Film yang tersedia beserta genrenya (Katalog Film sesuai referensi movie.png)
        $films = Film::with('genres')
            ->where('status', 'tersedia')
            ->orderBy('id', 'desc')
            ->get();
        $genres = GenreFilm::orderBy('nama_genre', 'asc')->get();

        // Hitung penayangan film yang sedang aktif di room saat ini (concurrent limit)
        $activeTrxs = Transaksi::with('room')
            ->whereNotNull('film_id')
            ->whereNotNull('room_id')
            ->where('status', 'selesai')
            ->where('waktu_transaksi', '>=', $now->copy()->subHours(24))
            ->where('waktu_transaksi', '<=', $now)
            ->get();

        $activeFilmUsage = [];
        $occupiedRoomIds = [];

        foreach ($activeTrxs as $trx) {
            $mulai = Carbon::parse($trx->waktu_transaksi);
            $durasi = (int) ($trx->durasi_menit ?: 120);
            $selesai = $mulai->copy()->addMinutes($durasi);

            if ($now->greaterThanOrEqualTo($mulai) && $now->lessThan($selesai)) {
                if (! isset($activeFilmUsage[$trx->film_id])) {
                    $activeFilmUsage[$trx->film_id] = [];
                }
                $sisaMenit = (int) $now->diffInMinutes($selesai, false);
                $activeFilmUsage[$trx->film_id][$trx->room_id] = [
                    'room_nama' => $trx->room?->nama ?? "Room #{$trx->room_id}",
                    'sisa_menit' => $sisaMenit,
                ];

                // Tandai room terpakai jika pada cabang shift kasir saat ini
                if ($trx->toko_cabang_id == $shift->toko_cabang_id) {
                    $occupiedRoomIds[] = $trx->room_id;
                }
            }
        }

        $occupiedRoomIds = array_values(array_unique($occupiedRoomIds));

        // Inject info limit dan slot ketersediaan ke setiap film
        foreach ($films as $film) {
            $activeRooms = $activeFilmUsage[$film->id] ?? [];
            $activeCount = count($activeRooms);
            $limit = max(1, (int) ($film->limit_penggunaan ?? 1));
            $sisaSlot = max(0, $limit - $activeCount);
            $isFull = $activeCount >= $limit;

            $film->active_rooms_count = $activeCount;
            $film->sisa_slot = $sisaSlot;
            $film->is_slot_full = $isFull;
            $film->active_rooms_details = array_values($activeRooms);
        }

        // 2. Room studio pada toko cabang kasir
        $rooms = Room::where('toko_cabang_id', $shift->toko_cabang_id)
            ->orderBy('nama', 'asc')
            ->get();

        // 3. Menu Cafe & Snack yang memiliki stok di cabang ini
        $cafeItems = DB::table('faktur_barang_details')
            ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
            ->join('barangs', 'faktur_barang_details.barang_id', '=', 'barangs.id')
            ->leftJoin('menu_cafe', 'barangs.menu_cafe_id', '=', 'menu_cafe.id')
            ->join('satuan_barangs', 'faktur_barang_details.satuan_barang_id', '=', 'satuan_barangs.id')
            ->where('faktur_barangs.toko_cabang_id', $shift->toko_cabang_id)
            ->select([
                'barangs.id as barang_id',
                'barangs.nama_barang',
                'menu_cafe.id as menu_cafe_id',
                'menu_cafe.nama as nama_kategori',
                'satuan_barangs.id as satuan_barang_id',
                'satuan_barangs.nama_satuan',
                DB::raw('SUM(faktur_barang_details.qty) as stok'),
                DB::raw('MAX(faktur_barang_details.harga_jual) as harga_jual'),
            ])
            ->groupBy(
                'barangs.id',
                'barangs.nama_barang',
                'menu_cafe.id',
                'menu_cafe.nama',
                'satuan_barangs.id',
                'satuan_barangs.nama_satuan'
            )
            ->havingRaw('SUM(faktur_barang_details.qty) > 0')
            ->orderBy('barangs.nama_barang', 'asc')
            ->get();

        $kategoriCafes = MenuCafe::orderBy('nama', 'asc')->get();

        // 4. Metode pembayaran dari master data
        $tipePembayarans = TipePembayaran::orderBy('id', 'asc')->get();

        // 5. Promo aktif
        $promos = Promo::where('status', 'aktif')->get();

        // 6. Transaksi terakhir shift ini
        $recentTransactions = Transaksi::with(['film', 'room', 'tipePembayaran'])
            ->where('kasir_shift_id', $shift->id)
            ->latest('waktu_transaksi')
            ->take(5)
            ->get();

        return view('kasir.kasir', [
            'hasActiveShift' => true,
            'activeShift' => $shift,
            'films' => $films,
            'genres' => $genres,
            'rooms' => $rooms,
            'occupiedRoomIds' => $occupiedRoomIds,
            'cafeItems' => $cafeItems,
            'kategoriCafes' => $kategoriCafes,
            'tipePembayarans' => $tipePembayarans,
            'promos' => $promos,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Buka Shift Kasir dengan memasukkan modal awal kasir.
     */
    public function bukaShift(Request $request)
    {
        $user = Auth::user();

        // Jika user kasir sudah memiliki penugasan outlet (toko_cabang_id), otomatis gunakan itu
        if ($user->toko_cabang_id) {
            $request->merge(['toko_cabang_id' => $user->toko_cabang_id]);
        }

        $validated = $request->validate([
            'toko_cabang_id' => 'required|exists:toko_cabang,id',
            'modal_awal' => 'required|numeric|min:0',
            'catatan_buka' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Tutup shift menggantung jika ada sebelumnya
        KasirShift::where('user_id', $user->id)
            ->where('status', 'buka')
            ->update([
                'status' => 'tutup',
                'waktu_tutup' => now(),
            ]);

        $shift = KasirShift::create([
            'user_id' => $user->id,
            'toko_cabang_id' => $validated['toko_cabang_id'],
            'modal_awal' => $validated['modal_awal'],
            'status' => 'buka',
            'waktu_buka' => now(),
            'catatan_buka' => $validated['catatan_buka'] ?? null,
        ]);

        $redirectRoute = Auth::user()->role === 'admin' ? 'admin.kasir.index' : 'kasir.kasir';

        return redirect()->route($redirectRoute)
            ->with('success', 'Shift kasir berhasil dibuka dengan modal awal Rp ' . number_format($shift->modal_awal, 0, ',', '.') . '. Selamat bertugas!');
    }

    /**
     * Tutup Shift Kasir (Tutup Toko).
     */
    public function tutupShift(Request $request)
    {
        $user = Auth::user();
        $shift = KasirShift::getActiveShift($user->id);
        $redirectRoute = Auth::user()->role === 'admin' ? 'admin.kasir.index' : 'kasir.kasir';

        if (! $shift) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Tidak ada shift kasir yang sedang aktif.');
        }

        $validated = $request->validate([
            'modal_akhir' => 'required|numeric|min:0',
            'catatan_tutup' => 'nullable|string|max:500',
        ]);

        $shift->update([
            'modal_akhir' => $validated['modal_akhir'],
            'status' => 'tutup',
            'waktu_tutup' => now(),
            'catatan_tutup' => $validated['catatan_tutup'] ?? null,
        ]);

        return redirect()->route($redirectRoute)
            ->with('success', 'Shift kasir telah ditutup. Laporan penjualan harian telah tersimpan dengan aman.');
    }

    /**
     * Cek validitas voucher promo via AJAX.
     */
    public function cekPromo(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $promo = Promo::where('kode_voucher', strtoupper(trim($request->kode_voucher)))
            ->where('status', 'aktif')
            ->first();

        if (! $promo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode voucher promo tidak ditemukan atau sudah tidak aktif.',
            ], 404);
        }

        $subtotal = (float) $request->subtotal;
        $nilai = trim($promo->nilai);
        $diskon = 0;

        if (str_contains($nilai, '%')) {
            $persen = (float) str_replace('%', '', $nilai);
            $diskon = round(($subtotal * $persen) / 100);
        } else {
            $cleanNilai = (float) preg_replace('/[^0-9]/', '', $nilai);
            $diskon = min($subtotal, $cleanNilai);
        }

        return response()->json([
            'status' => 'success',
            'promo_id' => $promo->id,
            'nama_promo' => $promo->nama_promo,
            'nilai_teks' => $promo->nilai,
            'diskon' => $diskon,
            'total_setelah_diskon' => max(0, $subtotal - $diskon),
        ]);
    }

    /**
     * Proses Checkout Pesanan: Simpan transaksi, potong stok cafe, update shift kasir, dan cetak struk.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $shift = KasirShift::getActiveShift($user->id);

        if (! $shift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi kasir belum dibuka. Silakan masukkan modal awal terlebih dahulu.',
            ], 422);
        }

        $validated = $request->validate([
            'film_id' => 'required|exists:films,id',
            'room_id' => 'required|exists:rooms,id',
            'tipe_pembayaran_id' => 'required|exists:tipe_pembayarans,id',
            'jumlah_penonton' => 'nullable|integer|min:1',
            'nama_pelanggan' => 'nullable|string|max:100',
            'promo_id' => 'nullable|exists:promos,id',
            'uang_diterima' => 'nullable|numeric|min:0',
            'cafe_items' => 'nullable|array',
            'cafe_items.*.barang_id' => 'required_with:cafe_items|exists:barangs,id',
            'cafe_items.*.satuan_barang_id' => 'nullable|exists:satuan_barangs,id',
            'cafe_items.*.qty' => 'required_with:cafe_items|numeric|min:1',
            'cafe_items.*.harga_satuan' => 'required_with:cafe_items|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $shift, $user) {
            $film = Film::findOrFail($validated['film_id']);
            $room = Room::findOrFail($validated['room_id']);

            // Validasi limit penggunaan room secara bersamaan (stok room film)
            if (! $film->isAvailableForBooking()) {
                $activeCount = $film->getActiveRoomsCount();
                $limit = max(1, (int) ($film->limit_penggunaan ?? 1));
                return response()->json([
                    'status' => 'error',
                    'message' => "Batas penggunaan film '{$film->judul}' sedang mencapai batas maksimal ({$activeCount}/{$limit} room aktif bersamaan). Harap tunggu hingga salah satu penayangan di room selesai.",
                ], 422);
            }

            // 1. Hitung biaya Film & Room serta denda jika penonton > 2 orang
            $hargaFilm = (float) $film->total_harga > 0 
                ? (float) $film->total_harga 
                : ((float) $film->harga_per_menit * (int) $film->durasi_menit);
            $hargaRoom = 0; // Biaya room include dalam paket film

            // Aturan Penonton: Standar 2 orang. Jika penonton > 2, dikenakan denda Rp 5.000 / orang lebih
            $jumlahPenonton = max(1, (int) ($validated['jumlah_penonton'] ?? 1));
            $dendaPenonton = $jumlahPenonton > 2 ? ($jumlahPenonton - 2) * 5000 : 0;

            $subtotalFilmRoom = $hargaFilm + $hargaRoom + $dendaPenonton;

            // 2. Hitung Pesanan Cafe & Makanan
            $subtotalCafe = 0;
            $cafeOrderDetails = [];

            if (! empty($validated['cafe_items'])) {
                foreach ($validated['cafe_items'] as $itemData) {
                    $qty = (float) $itemData['qty'];
                    $hargaSatuan = (float) $itemData['harga_satuan'];
                    $lineTotal = $qty * $hargaSatuan;
                    $subtotalCafe += $lineTotal;

                    $cafeOrderDetails[] = [
                        'barang_id' => $itemData['barang_id'],
                        'satuan_barang_id' => $itemData['satuan_barang_id'] ?? null,
                        'qty' => $qty,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $lineTotal,
                    ];
                }
            }

            // 3. Hitung Diskon Promo
            $subtotalKotor = $subtotalFilmRoom + $subtotalCafe;
            $diskon = 0;
            $promoId = $validated['promo_id'] ?? null;

            if ($promoId) {
                $promo = Promo::find($promoId);
                if ($promo && $promo->status === 'aktif') {
                    $nilai = trim($promo->nilai);
                    if (str_contains($nilai, '%')) {
                        $persen = (float) str_replace('%', '', $nilai);
                        $diskon = round(($subtotalKotor * $persen) / 100);
                    } else {
                        $cleanNilai = (float) preg_replace('/[^0-9]/', '', $nilai);
                        $diskon = min($subtotalKotor, $cleanNilai);
                    }
                }
            }

            $totalBayar = max(0, $subtotalKotor - $diskon);
            $uangDiterima = isset($validated['uang_diterima']) ? (float) $validated['uang_diterima'] : $totalBayar;
            $kembalian = max(0, $uangDiterima - $totalBayar);

            // 4. Buat Nomor Transaksi & Simpan Transaksi
            $nomorTransaksi = Transaksi::generateNomorTransaksi();

            $transaksi = Transaksi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'kasir_shift_id' => $shift->id,
                'toko_cabang_id' => $shift->toko_cabang_id,
                'user_id' => $user->id,
                'film_id' => $film->id,
                'room_id' => $room->id,
                'tipe_pembayaran_id' => $validated['tipe_pembayaran_id'],
                'promo_id' => $promoId,
                'nama_pelanggan' => $validated['nama_pelanggan'] ?? 'Pelanggan Umum',
                'durasi_menit' => $film->durasi_menit,
                'jumlah_penonton' => $jumlahPenonton,
                'denda_penonton' => $dendaPenonton,
                'harga_film' => $hargaFilm,
                'harga_room' => $hargaRoom,
                'subtotal_film_room' => $subtotalFilmRoom,
                'subtotal_cafe' => $subtotalCafe,
                'diskon' => $diskon,
                'total_bayar' => $totalBayar,
                'uang_diterima' => $uangDiterima,
                'kembalian' => $kembalian,
                'status' => 'selesai',
                'waktu_transaksi' => now(),
            ]);

            // 5. Simpan Rincian Cafe & Potong Stok FIFO
            foreach ($cafeOrderDetails as $detail) {
                $transaksi->details()->create($detail);

                // Potong stok dari faktur_barang_details di cabang ini
                $qtyToDeduct = $detail['qty'];
                $fakturDetails = FakturBarangDetail::whereHas('fakturBarang', function ($q) use ($shift) {
                        $q->where('toko_cabang_id', $shift->toko_cabang_id);
                    })
                    ->where('barang_id', $detail['barang_id'])
                    ->where('qty', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($fakturDetails as $fDetail) {
                    if ($qtyToDeduct <= 0) break;
                    $fQty = (float) $fDetail->qty;
                    if ($fQty <= $qtyToDeduct) {
                        $qtyToDeduct -= $fQty;
                        $fDetail->qty = 0;
                        $fDetail->subtotal = 0;
                        $fDetail->save();
                    } else {
                        $fDetail->qty = $fQty - $qtyToDeduct;
                        $fDetail->subtotal = $fDetail->qty * $fDetail->harga_awal;
                        $fDetail->save();
                        $qtyToDeduct = 0;
                    }
                }
            }

            // 6. Update Akumulasi Shift Kasir
            $tipePembayaran = TipePembayaran::find($validated['tipe_pembayaran_id']);
            $isCash = $tipePembayaran && (str_contains(strtolower($tipePembayaran->metode_pembayaran), 'tunai') || str_contains(strtolower($tipePembayaran->metode_pembayaran), 'cash'));

            $shift->total_penjualan += $totalBayar;
            $shift->total_transaksi += 1;
            if ($isCash) {
                $shift->total_tunai += $totalBayar;
            } else {
                $shift->total_non_tunai += $totalBayar;
            }
            $shift->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diproses!',
                'transaksi_id' => $transaksi->id,
                'nomor_transaksi' => $transaksi->nomor_transaksi,
            ]);
        });
    }

    /**
     * Tampilkan data struk thermal POS untuk dicetak.
     */
    public function struk($id)
    {
        $transaksi = Transaksi::with([
            'tokoCabang',
            'user',
            'film.genres',
            'room',
            'tipePembayaran',
            'promo',
            'details.barang',
            'details.satuanBarang',
        ])->findOrFail($id);

        return view('kasir.struk', compact('transaksi'));
    }
}
