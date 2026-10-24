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
use App\Models\TemplateStruk;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiFilmHistory;
use App\Models\TransaksiRoomHistory;
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
                'gantiFilmContext' => null,
            ]);
        }

        // Shift sedang aktif, muat data transaksi POS
        $shift = $activeShift->load('tokoCabang');

        $now = Carbon::now();
        $totalPendapatanToday = (float) Transaksi::whereDate('waktu_transaksi', Carbon::today())
            ->where('toko_cabang_id', $shift->toko_cabang_id)
            ->sum('total_bayar');

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
            $mulai = $trx->waktu_mulai_film ? Carbon::parse($trx->waktu_mulai_film) : Carbon::parse($trx->waktu_transaksi);
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

        // 7. Context Pergantian Film jika diakses via mode ganti_film (Rule 1, 2, 3)
        $gantiFilmContext = null;
        if ($request->get('mode') === 'ganti_film' && $request->filled('room_id')) {
            $targetRoom = Room::where('id', $request->get('room_id'))
                ->where('toko_cabang_id', $shift->toko_cabang_id)
                ->first();

            if (! $targetRoom) {
                session()->flash('error', 'Room tidak ditemukan pada outlet cabang aktif saat ini.');
            } else {
                $latestTrx = Transaksi::with(['film', 'room', 'tipePembayaran'])
                    ->where('room_id', $targetRoom->id)
                    ->where('status', 'selesai')
                    ->where('waktu_transaksi', '>=', $now->copy()->subHours(24))
                    ->where('waktu_transaksi', '<=', $now)
                    ->latest('waktu_transaksi')
                    ->first();

                if (! $latestTrx) {
                    session()->flash('error', "Tidak ada penayangan aktif pada room {$targetRoom->nama}.");
                } else {
                    $waktuMulai = $latestTrx->waktu_mulai_film 
                        ? Carbon::parse($latestTrx->waktu_mulai_film) 
                        : Carbon::parse($latestTrx->waktu_transaksi);
                    $durasiRencana = (int) ($latestTrx->durasi_menit ?: ($latestTrx->film?->durasi_menit ?? 120));
                    $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiRencana);

                    // Syarat 1: Hanya saat room berstatus sedang berlangsung
                    if (! ($now->greaterThanOrEqualTo($waktuMulai) && $now->lessThan($waktuSelesai))) {
                        session()->flash('error', "Pergantian film hanya dapat dilakukan ketika room {$targetRoom->nama} berstatus sedang berlangsung.");
                    } else {
                        $durasiAktual = max(1, (int) $waktuMulai->diffInMinutes($now));
                        $durasiAktual = min($durasiAktual, $durasiRencana);

                        $currentFilm = $latestTrx->film;
                        $tarifPerMenit = (float) $currentFilm?->harga_per_menit > 0 
                            ? (float) $currentFilm->harga_per_menit 
                            : ((int) $durasiRencana > 0 ? (float) $latestTrx->harga_film / $durasiRencana : 0);

                        $biayaAktualTerpakai = min((float) $latestTrx->harga_film, round($durasiAktual * $tarifPerMenit));

                        $gantiFilmContext = [
                            'is_mode_ganti' => true,
                            'room_id' => $targetRoom->id,
                            'room_nama' => $targetRoom->nama,
                            'room_kapasitas' => $targetRoom->kapasitas,
                            'transaksi_id' => $latestTrx->id,
                            'nomor_transaksi' => $latestTrx->nomor_transaksi,
                            'nama_pelanggan' => $latestTrx->nama_pelanggan,
                            'film_id_lama' => $latestTrx->film_id,
                            'film_judul_lama' => $currentFilm?->judul ?? 'Film Studio',
                            'film_durasi_lama' => $durasiRencana,
                            'harga_film_awal' => (float) $latestTrx->harga_film,
                            'waktu_mulai_formatted' => $waktuMulai->translatedFormat('d F Y, H:i'),
                            'waktu_mulai_time' => $waktuMulai->format('H:i'),
                            'durasi_aktual_menit' => $durasiAktual,
                            'tarif_per_menit' => $tarifPerMenit,
                            'biaya_aktual_terpakai' => $biayaAktualTerpakai,
                            'total_bayar_lama' => (float) $latestTrx->total_bayar,
                            'tipe_pembayaran_lama' => $latestTrx->tipePembayaran?->metode_pembayaran ?? 'TUNAI',
                            'harga_room' => (float) $latestTrx->harga_room,
                            'denda_penonton' => (float) $latestTrx->denda_penonton,
                            'subtotal_cafe' => (float) $latestTrx->subtotal_cafe,
                            'diskon' => (float) $latestTrx->diskon,
                            'catatan' => $latestTrx->catatan,
                        ];
                    }
                }
            }
        }

        return view('kasir.kasir', [
            'hasActiveShift' => true,
            'activeShift' => $shift,
            'totalPendapatanToday' => $totalPendapatanToday,
            'films' => $films,
            'genres' => $genres,
            'rooms' => $rooms,
            'occupiedRoomIds' => $occupiedRoomIds,
            'cafeItems' => $cafeItems,
            'kategoriCafes' => $kategoriCafes,
            'tipePembayarans' => $tipePembayarans,
            'promos' => $promos,
            'recentTransactions' => $recentTransactions,
            'gantiFilmContext' => $gantiFilmContext,
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

            // Buat riwayat pemutaran film awal
            $tarifPerMenit = (float) $film->harga_per_menit > 0 
                ? (float) $film->harga_per_menit 
                : ((int) $film->durasi_menit > 0 ? (float) $hargaFilm / (int) $film->durasi_menit : 0);

            $transaksi->filmHistories()->create([
                'film_id' => $film->id,
                'room_id' => $room->id,
                'judul_film' => $film->judul,
                'waktu_mulai' => now(),
                'waktu_selesai' => now()->copy()->addMinutes((int) $film->durasi_menit),
                'durasi_menit_rencana' => (int) $film->durasi_menit,
                'durasi_aktual_menit' => 0,
                'tarif_per_menit' => $tarifPerMenit,
                'biaya_film' => $hargaFilm,
                'status' => 'berjalan',
                'selisih_biaya' => 0,
                'tipe_selisih' => 'pas',
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

        $templateStruk = TemplateStruk::getActiveTemplate();

        return view('kasir.struk', compact('transaksi', 'templateStruk'));
    }

    /**
     * Ambil data penayangan room aktif, rincian biaya berjalan, dan daftar film pengganti yang tersedia (Rule 1 & 2).
     */
    public function getGantiFilmData($roomId)
    {
        $room = Room::findOrFail($roomId);
        $today = Carbon::today();
        $now = Carbon::now();

        // Cari transaksi aktif yang sedang menggunakan room ini
        $latestTrx = Transaksi::with(['film', 'room', 'tipePembayaran', 'filmHistories'])
            ->where('room_id', $room->id)
            ->where('status', 'selesai')
            ->whereDate('waktu_transaksi', $today)
            ->latest('waktu_transaksi')
            ->first();

        if (! $latestTrx) {
            return response()->json([
                'status' => 'error',
                'message' => "Tidak ada penayangan aktif pada {$room->nama}.",
            ], 404);
        }

        $waktuMulai = $latestTrx->waktu_mulai_film 
            ? Carbon::parse($latestTrx->waktu_mulai_film) 
            : Carbon::parse($latestTrx->waktu_transaksi);
        $durasiRencana = (int) ($latestTrx->durasi_menit ?: ($latestTrx->film?->durasi_menit ?? 120));
        $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiRencana);

        // Syarat 1: Hanya dapat dilakukan ketika room berstatus sedang berlangsung
        if (! ($now->greaterThanOrEqualTo($waktuMulai) && $now->lessThan($waktuSelesai))) {
            return response()->json([
                'status' => 'error',
                'message' => "Pergantian film hanya dapat dilakukan saat room sedang berlangsung penayangan. Status {$room->nama} saat ini tidak sedang aktif.",
            ], 422);
        }

        $durasiAktual = max(1, (int) $waktuMulai->diffInMinutes($now));
        $durasiAktual = min($durasiAktual, $durasiRencana);

        $currentFilm = $latestTrx->film;
        $tarifPerMenit = (float) $currentFilm?->harga_per_menit > 0 
            ? (float) $currentFilm->harga_per_menit 
            : ((int) $durasiRencana > 0 ? (float) $latestTrx->harga_film / $durasiRencana : 0);

        $biayaAktualTerpakai = min((float) $latestTrx->harga_film, round($durasiAktual * $tarifPerMenit));

        // Syarat 2: Kasir memilih film pengganti dari daftar film yang aktif dan tersedia
        $availableFilms = Film::where('status', 'tersedia')
            ->where('id', '!=', $latestTrx->film_id)
            ->orderBy('judul', 'asc')
            ->get()
            ->map(function ($f) {
                $activeCount = $f->getActiveRoomsCount();
                $limit = max(1, (int) ($f->limit_penggunaan ?? 1));
                $isLimitFull = $activeCount >= $limit;

                $harga = (float) $f->total_harga > 0 
                    ? (float) $f->total_harga 
                    : ((float) $f->harga_per_menit * (int) $f->durasi_menit);

                return [
                    'id' => $f->id,
                    'judul' => $f->judul,
                    'durasi_menit' => (int) $f->durasi_menit,
                    'durasi_formatted' => $f->durasi_formatted,
                    'harga' => $harga,
                    'harga_format' => 'Rp ' . number_format($harga, 0, ',', '.'),
                    'harga_per_menit' => (float) $f->harga_per_menit,
                    'poster_url' => $f->poster_url,
                    'limit_penggunaan' => $limit,
                    'active_rooms_count' => $activeCount,
                    'is_limit_full' => $isLimitFull,
                ];
            });

        $tipePembayarans = TipePembayaran::orderBy('id', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'room' => [
                'id' => $room->id,
                'nama' => $room->nama,
                'kapasitas' => $room->kapasitas,
            ],
            'transaksi' => [
                'id' => $latestTrx->id,
                'nomor_transaksi' => $latestTrx->nomor_transaksi,
                'nama_pelanggan' => $latestTrx->nama_pelanggan,
                'film_id' => $latestTrx->film_id,
                'film_judul' => $currentFilm?->judul ?? 'Film Studio',
                'film_durasi' => $durasiRencana,
                'harga_film' => (float) $latestTrx->harga_film,
                'total_bayar' => (float) $latestTrx->total_bayar,
                'waktu_mulai' => $waktuMulai->translatedFormat('d F Y, H:i'),
                'waktu_mulai_time' => $waktuMulai->format('H:i'),
                'durasi_aktual_menit' => $durasiAktual,
                'tarif_per_menit' => $tarifPerMenit,
                'biaya_aktual_terpakai' => $biayaAktualTerpakai,
                'tipe_pembayaran' => $latestTrx->tipePembayaran?->metode_pembayaran ?? 'TUNAI',
            ],
            'films' => $availableFilms,
            'tipe_pembayarans' => $tipePembayarans,
            'server_time' => $now->translatedFormat('d F Y, H:i:s') . ' WIB',
        ]);
    }

    /**
     * Hitung biaya dan selisih harga pergantian film (Rule 3).
     */
    public function kalkulasiGantiFilm(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id' => 'required|exists:transaksis,id',
            'film_baru_id' => 'required|exists:films,id',
        ]);

        $transaksi = Transaksi::with(['film', 'room'])->findOrFail($validated['transaksi_id']);
        $now = Carbon::now();

        $waktuMulai = $transaksi->waktu_mulai_film 
            ? Carbon::parse($transaksi->waktu_mulai_film) 
            : Carbon::parse($transaksi->waktu_transaksi);
        $durasiRencana = (int) ($transaksi->durasi_menit ?: ($transaksi->film?->durasi_menit ?? 120));
        $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiRencana);

        if (! ($now->greaterThanOrEqualTo($waktuMulai) && $now->lessThan($waktuSelesai))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penayangan film pada room ini sudah selesai atau tidak sedang berlangsung.',
            ], 422);
        }

        $durasiAktual = max(1, (int) $waktuMulai->diffInMinutes($now));
        $durasiAktual = min($durasiAktual, $durasiRencana);

        $filmLama = $transaksi->film;
        $tarifLama = (float) $filmLama?->harga_per_menit > 0 
            ? (float) $filmLama->harga_per_menit 
            : ((int) $durasiRencana > 0 ? (float) $transaksi->harga_film / $durasiRencana : 0);

        // Biaya pemutaran aktual film lama
        $biayaFilmSebelumnya = min((float) $transaksi->harga_film, round($durasiAktual * $tarifLama));

        // Biaya film pengganti
        $filmBaru = Film::findOrFail($validated['film_baru_id']);
        $hargaFilmBaru = (float) $filmBaru->total_harga > 0 
            ? (float) $filmBaru->total_harga 
            : ((float) $filmBaru->harga_per_menit * (int) $filmBaru->durasi_menit);

        // Total biaya film final = Biaya film lama yang terpakai + Biaya penuh film baru
        $biayaFilmFinal = $biayaFilmSebelumnya + $hargaFilmBaru;

        // Selisih terhadap harga film awal yang telah dibayarkan
        $selisih = $biayaFilmFinal - (float) $transaksi->harga_film;

        if ($selisih > 0) {
            $tipeSelisih = 'tambahan';
            $labelSelisih = 'Pelanggan Perlu Membayar Tambahan';
        } elseif ($selisih < 0) {
            $tipeSelisih = 'refund';
            $labelSelisih = 'Pengembalian Dana ke Pelanggan (Refund)';
        } else {
            $tipeSelisih = 'pas';
            $labelSelisih = 'Biaya Pas / Tidak Ada Selisih';
        }

        $totalBayarLama = (float) $transaksi->total_bayar;
        $subtotalFilmRoomBaru = $biayaFilmFinal + (float) $transaksi->harga_room + (float) $transaksi->denda_penonton;
        $totalBayarBaru = max(0, $subtotalFilmRoomBaru + (float) $transaksi->subtotal_cafe - (float) $transaksi->diskon);

        return response()->json([
            'status' => 'success',
            'data' => [
                'film_lama' => [
                    'id' => $filmLama?->id,
                    'judul' => $filmLama?->judul ?? 'Film Lama',
                    'durasi_menit' => $durasiRencana,
                    'waktu_mulai' => $waktuMulai->format('H:i') . ' WIB',
                    'durasi_aktual_menit' => $durasiAktual,
                    'tarif_per_menit' => $tarifLama,
                    'biaya_terpakai' => $biayaFilmSebelumnya,
                    'biaya_terpakai_format' => 'Rp ' . number_format($biayaFilmSebelumnya, 0, ',', '.'),
                    'harga_film_awal' => (float) $transaksi->harga_film,
                    'harga_film_awal_format' => 'Rp ' . number_format($transaksi->harga_film, 0, ',', '.'),
                ],
                'film_baru' => [
                    'id' => $filmBaru->id,
                    'judul' => $filmBaru->judul,
                    'durasi_menit' => (int) $filmBaru->durasi_menit,
                    'durasi_formatted' => $filmBaru->durasi_formatted,
                    'harga' => $hargaFilmBaru,
                    'harga_format' => 'Rp ' . number_format($hargaFilmBaru, 0, ',', '.'),
                    'poster_url' => $filmBaru->poster_url,
                ],
                'kalkulasi' => [
                    'biaya_film_final' => $biayaFilmFinal,
                    'biaya_film_final_format' => 'Rp ' . number_format($biayaFilmFinal, 0, ',', '.'),
                    'selisih' => $selisih,
                    'selisih_nominal' => abs($selisih),
                    'selisih_format' => 'Rp ' . number_format(abs($selisih), 0, ',', '.'),
                    'tipe_selisih' => $tipeSelisih,
                    'label_selisih' => $labelSelisih,
                    'total_bayar_lama' => $totalBayarLama,
                    'total_bayar_lama_format' => 'Rp ' . number_format($totalBayarLama, 0, ',', '.'),
                    'total_bayar_baru' => $totalBayarBaru,
                    'total_bayar_baru_format' => 'Rp ' . number_format($totalBayarBaru, 0, ',', '.'),
                ],
                'server_time' => $now->translatedFormat('d F Y, H:i') . ' WIB',
            ],
        ]);
    }

    /**
     * Konfirmasi & simpan pergantian film ke dalam transaksi yang sama (Rule 4, 5, 6).
     */
    public function simpanGantiFilm(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id' => 'required|exists:transaksis,id',
            'film_baru_id' => 'required|exists:films,id',
            'alasan_ganti' => 'nullable|string|max:255',
            'tipe_pembayaran_tambahan_id' => 'nullable|exists:tipe_pembayarans,id',
            'uang_diterima' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $transaksi = Transaksi::with(['film', 'room'])->findOrFail($validated['transaksi_id']);
            $filmBaru = Film::findOrFail($validated['film_baru_id']);

            if ($transaksi->film_id == $filmBaru->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Film pengganti tidak boleh sama dengan film yang sedang diputar saat ini.',
                ], 422);
            }

            // Validasi batas limit penggunaan film baru (Rule 2)
            if (! $filmBaru->isAvailableForBooking()) {
                $activeCount = $filmBaru->getActiveRoomsCount();
                $limit = max(1, (int) ($filmBaru->limit_penggunaan ?? 1));
                return response()->json([
                    'status' => 'error',
                    'message' => "Batas penggunaan film '{$filmBaru->judul}' sedang mencapai batas maksimal ({$activeCount}/{$limit} room aktif bersamaan). Silakan pilih film lain.",
                ], 422);
            }

            $now = Carbon::now();
            $waktuMulai = $transaksi->waktu_mulai_film 
                ? Carbon::parse($transaksi->waktu_mulai_film) 
                : Carbon::parse($transaksi->waktu_transaksi);
            $durasiRencana = (int) ($transaksi->durasi_menit ?: ($transaksi->film?->durasi_menit ?? 120));
            $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiRencana);

            // Syarat 1: Hanya saat room sedang berlangsung
            if (! ($now->greaterThanOrEqualTo($waktuMulai) && $now->lessThan($waktuSelesai))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pergantian film hanya dapat dilakukan ketika status room sedang berlangsung penayangan.',
                ], 422);
            }

            // Durasi aktual pemutaran film lama
            $durasiAktual = max(1, (int) $waktuMulai->diffInMinutes($now));
            $durasiAktual = min($durasiAktual, $durasiRencana);

            $filmLama = $transaksi->film;
            $tarifLama = (float) $filmLama?->harga_per_menit > 0 
                ? (float) $filmLama->harga_per_menit 
                : ((int) $durasiRencana > 0 ? (float) $transaksi->harga_film / $durasiRencana : 0);

            $biayaFilmSebelumnya = min((float) $transaksi->harga_film, round($durasiAktual * $tarifLama));

            // Biaya film pengganti
            $hargaFilmBaru = (float) $filmBaru->total_harga > 0 
                ? (float) $filmBaru->total_harga 
                : ((float) $filmBaru->harga_per_menit * (int) $filmBaru->durasi_menit);

            $biayaFilmFinal = $biayaFilmSebelumnya + $hargaFilmBaru;
            $selisih = $biayaFilmFinal - (float) $transaksi->harga_film;

            $tipeSelisih = $selisih > 0 ? 'tambahan' : ($selisih < 0 ? 'refund' : 'pas');

            // Syarat 4: Catat waktu secara otomatis & tutup riwayat film sebelumnya
            $activeHistory = TransaksiFilmHistory::where('transaksi_id', $transaksi->id)
                ->where('status', 'berjalan')
                ->latest('id')
                ->first();

            if (! $activeHistory) {
                // Buat record awal jika belum ada
                TransaksiFilmHistory::create([
                    'transaksi_id' => $transaksi->id,
                    'film_id' => $transaksi->film_id,
                    'room_id' => $transaksi->room_id,
                    'judul_film' => $filmLama?->judul ?? 'Film Studio',
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $now,
                    'durasi_menit_rencana' => $durasiRencana,
                    'durasi_aktual_menit' => $durasiAktual,
                    'tarif_per_menit' => $tarifLama,
                    'biaya_film' => $biayaFilmSebelumnya,
                    'status' => 'diganti',
                    'alasan_ganti' => $validated['alasan_ganti'] ?? 'Ganti film oleh kasir',
                    'selisih_biaya' => abs($selisih),
                    'tipe_selisih' => $tipeSelisih,
                ]);
            } else {
                $activeHistory->update([
                    'waktu_selesai' => $now,
                    'durasi_aktual_menit' => $durasiAktual,
                    'biaya_film' => $biayaFilmSebelumnya,
                    'status' => 'diganti',
                    'alasan_ganti' => $validated['alasan_ganti'] ?? 'Ganti film oleh kasir',
                    'selisih_biaya' => abs($selisih),
                    'tipe_selisih' => $tipeSelisih,
                ]);
            }

            // Buat riwayat pemutaran untuk film baru (Rule 4)
            $tarifBaru = (float) $filmBaru->harga_per_menit > 0 
                ? (float) $filmBaru->harga_per_menit 
                : ((int) $filmBaru->durasi_menit > 0 ? (float) $hargaFilmBaru / (int) $filmBaru->durasi_menit : 0);

            TransaksiFilmHistory::create([
                'transaksi_id' => $transaksi->id,
                'film_id' => $filmBaru->id,
                'room_id' => $transaksi->room_id,
                'judul_film' => $filmBaru->judul,
                'waktu_mulai' => $now,
                'waktu_selesai' => $now->copy()->addMinutes((int) $filmBaru->durasi_menit),
                'durasi_menit_rencana' => (int) $filmBaru->durasi_menit,
                'durasi_aktual_menit' => 0,
                'tarif_per_menit' => $tarifBaru,
                'biaya_film' => $hargaFilmBaru,
                'status' => 'berjalan',
                'selisih_biaya' => 0,
                'tipe_selisih' => 'pas',
            ]);

            // Syarat 6: Simpan dalam transaksi yang sama
            $transaksi->film_id = $filmBaru->id;
            $transaksi->waktu_mulai_film = $now;
            $transaksi->durasi_menit = (int) $filmBaru->durasi_menit;
            $transaksi->harga_film = $biayaFilmFinal;
            $transaksi->subtotal_film_room = $biayaFilmFinal + (float) $transaksi->harga_room + (float) $transaksi->denda_penonton;

            $totalBayarBaru = max(0, $transaksi->subtotal_film_room + (float) $transaksi->subtotal_cafe - (float) $transaksi->diskon);
            $transaksi->total_bayar = $totalBayarBaru;

            if ($selisih > 0) {
                $bayar = isset($validated['uang_diterima']) && (float) $validated['uang_diterima'] >= $selisih 
                    ? (float) $validated['uang_diterima'] 
                    : $selisih;
                $transaksi->uang_diterima += $bayar;
                if (!empty($validated['tipe_pembayaran_tambahan_id'])) {
                    $transaksi->tipe_pembayaran_id = $validated['tipe_pembayaran_tambahan_id'];
                }
            } elseif ($selisih < 0) {
                // Pengembalian dana ke pelanggan
                $transaksi->kembalian += abs($selisih);
            }

            $logMsg = "\n[" . $now->translatedFormat('d/m/Y H:i:s') . "] Ganti film: '" . ($filmLama?->judul ?? 'Film Lama') . "' -> '{$filmBaru->judul}'. " .
                "Putar film lama: {$durasiAktual} mnt (Rp " . number_format($biayaFilmSebelumnya, 0, ',', '.') . "), " .
                "Film baru (Rp " . number_format($hargaFilmBaru, 0, ',', '.') . "). " .
                "Selisih: " . ($selisih >= 0 ? '+' : '-') . "Rp " . number_format(abs($selisih), 0, ',', '.') . " (" . strtoupper($tipeSelisih) . "). " .
                "Alasan: " . ($validated['alasan_ganti'] ?: '-');

            $transaksi->catatan = trim(($transaksi->catatan ?? '') . $logMsg);
            $transaksi->save();

            return response()->json([
                'status' => 'success',
                'message' => "Film berhasil diganti ke '{$filmBaru->judul}' pada transaksi #{$transaksi->nomor_transaksi}.",
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'nomor_transaksi' => $transaksi->nomor_transaksi,
                    'film_baru' => $filmBaru->judul,
                    'durasi_menit' => $filmBaru->durasi_menit,
                    'total_bayar' => $totalBayarBaru,
                    'selisih' => $selisih,
                    'tipe_selisih' => $tipeSelisih,
                ],
            ]);
        });
    }

    /**
     * Dapatkan data transaksi aktif pada room asal dan daftar room yang tersedia untuk dipindahkan (Move Room).
     */
    public function getAvailableRoomsForMove(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);
        $today = Carbon::today();
        $now = Carbon::now();

        // Cari transaksi aktif terakhir pada room ini hari ini
        $latestTrx = Transaksi::with(['film', 'room', 'details.barang', 'details.satuanBarang', 'tipePembayaran'])
            ->where('room_id', $roomId)
            ->where('status', 'selesai')
            ->whereDate('waktu_transaksi', $today)
            ->latest('waktu_transaksi')
            ->first();

        if (! $latestTrx) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ditemukan transaksi aktif pada ruangan ini.',
            ], 422);
        }

        $waktuMulai = $latestTrx->waktu_mulai_film 
            ? Carbon::parse($latestTrx->waktu_mulai_film) 
            : Carbon::parse($latestTrx->waktu_transaksi);
        $durasiRencana = (int) ($latestTrx->durasi_menit ?: ($latestTrx->film?->durasi_menit ?? 120));
        $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiRencana);

        if (! ($now->greaterThanOrEqualTo($waktuMulai) && $now->lessThan($waktuSelesai))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penayangan pada ruangan ini sudah selesai atau tidak sedang berlangsung.',
            ], 422);
        }

        $durasiBerjalan = max(0, (int) $waktuMulai->diffInMinutes($now));
        $sisaMenit = max(0, (int) $now->diffInMinutes($waktuSelesai, false));

        // Ambil daftar ID room yang sedang terpakai saat ini di cabang yang sama
        $activeTrxs = Transaksi::where('toko_cabang_id', $latestTrx->toko_cabang_id)
            ->where('status', 'selesai')
            ->whereDate('waktu_transaksi', $today)
            ->whereNotNull('room_id')
            ->get();

        $occupiedRoomIds = [];
        foreach ($activeTrxs as $trx) {
            $start = $trx->waktu_mulai_film ? Carbon::parse($trx->waktu_mulai_film) : Carbon::parse($trx->waktu_transaksi);
            $duration = (int) ($trx->durasi_menit ?: ($trx->film?->durasi_menit ?? 120));
            $end = $start->copy()->addMinutes($duration);
            if ($now->greaterThanOrEqualTo($start) && $now->lessThan($end)) {
                $occupiedRoomIds[] = $trx->room_id;
            }
        }

        // Ambil room di cabang yang sama selain room asal dan yang sedang tidak terpakai
        $availableRooms = Room::where('toko_cabang_id', $latestTrx->toko_cabang_id)
            ->where('id', '!=', $room->id)
            ->whereNotIn('id', $occupiedRoomIds)
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'room_asal' => [
                'id' => $room->id,
                'nama' => $room->nama,
                'kapasitas' => $room->kapasitas,
            ],
            'transaksi' => [
                'id' => $latestTrx->id,
                'nomor_transaksi' => $latestTrx->nomor_transaksi,
                'nama_pelanggan' => $latestTrx->nama_pelanggan,
                'film_judul' => $latestTrx->film?->judul ?? 'Film Studio',
                'film_poster' => $latestTrx->film?->poster_url,
                'waktu_mulai' => $waktuMulai->format('H:i') . ' WIB',
                'waktu_selesai' => $waktuSelesai->format('H:i') . ' WIB',
                'durasi_total_menit' => $durasiRencana,
                'durasi_berjalan_menit' => $durasiBerjalan,
                'sisa_menit' => $sisaMenit,
                'sisa_waktu_teks' => KasirDashboardController::formatSisaWaktu($sisaMenit),
                'cafe_items_count' => $latestTrx->details->count(),
                'subtotal_cafe' => (float) $latestTrx->subtotal_cafe,
                'total_bayar' => (float) $latestTrx->total_bayar,
            ],
            'available_rooms' => $availableRooms->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama' => $r->nama,
                    'kapasitas' => $r->kapasitas,
                ];
            }),
            'server_time' => $now->translatedFormat('d F Y, H:i') . ' WIB',
        ]);
    }

    /**
     * Proses pemindahan ruangan (Move Room) dengan mempertahankan waktu mulai, durasi berjalan,
     * film, dan pesanan kafe (Aturan 1 - 7).
     */
    public function moveRoom(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id' => 'required|exists:transaksis,id',
            'room_tujuan_id' => 'required|exists:rooms,id',
            'alasan_pindah' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $transaksi = Transaksi::with(['film', 'room', 'details'])->lockForUpdate()->findOrFail($validated['transaksi_id']);
            $newRoom = Room::findOrFail($validated['room_tujuan_id']);
            $oldRoom = $transaksi->room;

            if (! $oldRoom) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi ini tidak memiliki ruangan asal.',
                ], 422);
            }

            if ($transaksi->room_id == $newRoom->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ruangan tujuan tidak boleh sama dengan ruangan asal.',
                ], 422);
            }

            if ($newRoom->toko_cabang_id != $transaksi->toko_cabang_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ruangan tujuan harus berada pada outlet cabang yang sama.',
                ], 422);
            }

            $now = Carbon::now();
            $today = Carbon::today();

            $waktuMulai = $transaksi->waktu_mulai_film 
                ? Carbon::parse($transaksi->waktu_mulai_film) 
                : Carbon::parse($transaksi->waktu_transaksi);
            $durasiRencana = (int) ($transaksi->durasi_menit ?: ($transaksi->film?->durasi_menit ?? 120));
            $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiRencana);

            // Syarat: Penayangan sedang berlangsung
            if (! ($now->greaterThanOrEqualTo($waktuMulai) && $now->lessThan($waktuSelesai))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Perpindahan ruangan hanya dapat dilakukan ketika penayangan sedang berlangsung.',
                ], 422);
            }

            // Periksa apakah ruangan tujuan sedang terpakai oleh transaksi lain yang aktif
            $targetTrx = Transaksi::where('room_id', $newRoom->id)
                ->where('id', '!=', $transaksi->id)
                ->where('status', 'selesai')
                ->whereDate('waktu_transaksi', $today)
                ->latest('waktu_transaksi')
                ->first();

            if ($targetTrx) {
                $targetMulai = $targetTrx->waktu_mulai_film 
                    ? Carbon::parse($targetTrx->waktu_mulai_film) 
                    : Carbon::parse($targetTrx->waktu_transaksi);
                $targetDurasi = (int) ($targetTrx->durasi_menit ?: ($targetTrx->film?->durasi_menit ?? 120));
                $targetSelesai = $targetMulai->copy()->addMinutes($targetDurasi);

                if ($now->greaterThanOrEqualTo($targetMulai) && $now->lessThan($targetSelesai)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Ruangan {$newRoom->nama} saat ini sedang terpakai dan tidak dapat dipilih.",
                    ], 422);
                }
            }

            // Hitung durasi yang telah berjalan dan sisa durasi saat perpindahan
            $durasiBerjalan = max(0, (int) $waktuMulai->diffInMinutes($now));
            $sisaMenit = max(0, (int) $now->diffInMinutes($waktuSelesai, false));

            $user = Auth::user();
            $namaUser = $user?->name ?? $user?->username ?? 'Kasir';

            // Catat riwayat perpindahan ruangan ke database (Aturan 7)
            $history = TransaksiRoomHistory::create([
                'transaksi_id' => $transaksi->id,
                'room_asal_id' => $oldRoom->id,
                'room_tujuan_id' => $newRoom->id,
                'nama_room_asal' => $oldRoom->nama,
                'nama_room_tujuan' => $newRoom->nama,
                'user_id' => $user?->id ?? $transaksi->user_id,
                'nama_user' => $namaUser,
                'waktu_pindah' => $now,
                'durasi_berjalan_menit' => $durasiBerjalan,
                'sisa_durasi_menit' => $sisaMenit,
                'alasan_pindah' => $validated['alasan_pindah'] ?? null,
            ]);

            // Jika ada riwayat pemutaran film berjalan, sinkronkan room_id-nya ke room baru
            TransaksiFilmHistory::where('transaksi_id', $transaksi->id)
                ->where('status', 'berjalan')
                ->update(['room_id' => $newRoom->id]);

            // Pindahkan transaksi ke ruangan baru:
            // 1. Waktu mulai transaksi TETAP (waktu_transaksi & waktu_mulai_film tidak diubah)
            // 2. Durasi berjalan TETAP dihitung (waktu_mulai dan durasi_menit tidak diubah)
            // 3. Film yang diputar TETAP film yang dipesan (film_id tidak diubah)
            // 4. Pesanan kafe TETAP tersimpan (details & subtotal_cafe tidak diubah)
            // 5. Ruangan asal menjadi tersedia & ruangan tujuan menjadi terisi (room_id berpindah)
            $transaksi->room_id = $newRoom->id;

            $alasanText = !empty($validated['alasan_pindah']) ? " (Alasan: {$validated['alasan_pindah']})" : '';
            $logMsg = "\n[" . $now->translatedFormat('d/m/Y H:i:s') . "] Pindah room: '{$oldRoom->nama}' -> '{$newRoom->nama}'. " .
                "Durasi berjalan: {$durasiBerjalan} mnt, Sisa: {$sisaMenit} mnt. Petugas: {$namaUser}{$alasanText}.";
            $transaksi->catatan = trim(($transaksi->catatan ?? '') . $logMsg);
            $transaksi->save();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memindahkan penayangan dari {$oldRoom->nama} ke {$newRoom->nama}.",
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'nomor_transaksi' => $transaksi->nomor_transaksi,
                    'room_asal' => $oldRoom->nama,
                    'room_tujuan' => $newRoom->nama,
                    'durasi_berjalan_menit' => $durasiBerjalan,
                    'sisa_menit' => $sisaMenit,
                    'history_id' => $history->id,
                ],
            ]);
        });
    }
}

