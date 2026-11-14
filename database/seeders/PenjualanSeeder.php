<?php

namespace Database\Seeders;

use App\Models\FakturBarangDetail;
use App\Models\Film;
use App\Models\Jabatan;
use App\Models\KasirShift;
use App\Models\Pegawai;
use App\Models\Promo;
use App\Models\Room;
use App\Models\Shift;
use App\Models\TipePembayaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiFilmHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Data Cabang, Shift, dan User Kasir Tersedia
        $cabangSudirman = TokoCabang::where('nama_cabang', 'like', '%Sudirman%')->first() ?? TokoCabang::first();
        $cabangPanam = TokoCabang::where('nama_cabang', 'like', '%Panam%')->first() ?? TokoCabang::orderBy('id', 'desc')->first();

        $shift1 = Shift::where('nama_shift', 'Shift 1')->first() ?? Shift::first();
        $shift2 = Shift::where('nama_shift', 'Shift 2')->first() ?? Shift::orderBy('id', 'desc')->first();

        $jabatanKasir = Jabatan::firstOrCreate(['nama_jabatan' => 'Kasir']);
        $jabatanManager = Jabatan::firstOrCreate(['nama_jabatan' => 'Store Manager']);

        // Pegawai untuk kasir & admin
        $pegawaiSudirman = Pegawai::firstOrCreate(
            ['nama_pegawai' => 'Siti Rahmawati'],
            [
                'jabatan_id' => $jabatanKasir->id,
                'nomor_hp' => '0812-3456-7890',
                'alamat' => 'Jl. Jenderal Sudirman No. 45, Pekanbaru',
            ]
        );

        $pegawaiPanam = Pegawai::firstOrCreate(
            ['nama_pegawai' => 'Ahmad Fauzi'],
            [
                'jabatan_id' => $jabatanKasir->id,
                'nomor_hp' => '0813-9876-5432',
                'alamat' => 'Jl. HR. Soebrantas No. 12, Panam, Pekanbaru',
            ]
        );

        $pegawaiManager = Pegawai::firstOrCreate(
            ['nama_pegawai' => 'Bambang Wijaya'],
            [
                'jabatan_id' => $jabatanManager->id,
                'nomor_hp' => '0811-2233-4455',
                'alamat' => 'Jl. Tuanku Tambusai No. 88, Pekanbaru',
            ]
        );

        // Update User Admin & Kasir
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Bambang (Admin)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'toko_cabang_id' => $cabangSudirman?->id,
                'pegawai_id' => $pegawaiManager->id,
                'jabatan_id' => $jabatanManager->id,
            ]
        );

        $userKasirSudirman = User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Siti Kasir Sudirman',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabangSudirman?->id,
                'pegawai_id' => $pegawaiSudirman->id,
                'jabatan_id' => $jabatanKasir->id,
            ]
        );

        $userKasirPanam = User::updateOrCreate(
            ['username' => 'kasir_panam'],
            [
                'name' => 'Ahmad Kasir Panam',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'toko_cabang_id' => $cabangPanam?->id,
                'pegawai_id' => $pegawaiPanam->id,
                'jabatan_id' => $jabatanKasir->id,
            ]
        );

        // 2. Persiapkan Data Pendukung (Film, Room, Metode Bayar, Promo)
        $films = Film::where('status', 'tersedia')->get();
        if ($films->isEmpty()) {
            return;
        }

        $roomsSudirman = Room::where('toko_cabang_id', $cabangSudirman?->id)->get();
        $roomsPanam = Room::where('toko_cabang_id', $cabangPanam?->id)->get();

        $tipeTunai = TipePembayaran::where('metode_pembayaran', 'like', '%Tunai%')->first();
        $tipeQris = TipePembayaran::where('metode_pembayaran', 'like', '%QRIS%')->first();
        $tipeBca = TipePembayaran::where('metode_pembayaran', 'like', '%BCA%')->first();
        $tipeMandiri = TipePembayaran::where('metode_pembayaran', 'like', '%Mandiri%')->first();
        $allTipeBayar = TipePembayaran::all();

        $promos = Promo::where('status', 'aktif')->get();

        $pelangganPool = [
            'Budi Santoso', 'Rina Wulandari', 'Aditya Pratama', 'Siti Nurhaliza',
            'Kevin Sanjaya', 'Reza Rahadian', 'Amanda Manopo', 'Dimas Anggara',
            'Putri Marino', 'Rizky Febian', 'Mahalini Raharja', 'Fajar Alfian',
            'Dewi Sandra', 'Chico Jericho', 'Maudy Ayunda', 'Nicholas Saputra',
            'Dian Sastrowardoyo', 'Pevita Pearce', 'Iqbaal Ramadhan', 'Angga Yunanda',
            'Shenina Cinnamon', 'Prilly Latuconsina', 'Jefri Nichol', 'Bryan Domani',
            'Syifa Hadju', 'Refal Hady', 'Tara Basro', 'Vino G. Bastian',
            'Marsha Timothy', 'Ario Bayu', 'Laura Basuki', 'Arifin Putra'
        ];

        // 3. Generate Transaksi Penjualan untuk 25 Hari Terakhir hingga Hari Ini
        $now = Carbon::now();
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(24);

        // Loop setiap hari
        for ($date = $startDate->copy(); $date->lessThanOrEqualTo($today); $date->addDay()) {
            $isToday = $date->isSameDay($today);
            $datePrefix = $date->format('Ymd');
            $dailyTrxCounter = 0;

            // Transaksi untuk kedua cabang (Sudirman & Panam)
            $branchesConfig = [
                [
                    'cabang' => $cabangSudirman,
                    'user' => $userKasirSudirman,
                    'rooms' => $roomsSudirman,
                    'shifts' => [$shift1, $shift2],
                ],
                [
                    'cabang' => $cabangPanam,
                    'user' => $userKasirPanam,
                    'rooms' => $roomsPanam,
                    'shifts' => [$shift1],
                ],
            ];

            foreach ($branchesConfig as $bConfig) {
                $cabang = $bConfig['cabang'];
                $kasirUser = $bConfig['user'];
                $cabangRooms = $bConfig['rooms'];

                if (! $cabang || $cabangRooms->isEmpty()) {
                    continue;
                }

                foreach ($bConfig['shifts'] as $shiftIndex => $shiftDef) {
                    $shiftName = $shiftDef?->nama_shift ?? 'Shift ' . ($shiftIndex + 1);
                    $isShift1 = str_contains($shiftName, '1');

                    // Waktu jam operasional shift
                    $jamMulai = $isShift1 ? 11 : 16;
                    $jamSelesai = $isShift1 ? 16 : 23;

                    $waktuBuka = $date->copy()->setTime($jamMulai, 0, 0);
                    $waktuTutup = $date->copy()->setTime($jamSelesai, 0, 0);

                    // Khusus hari ini: shift 1 sedang 'buka' (aktif), shift 2 belum dimulai
                    $shiftStatus = 'tutup';
                    if ($isToday) {
                        if ($isShift1) {
                            $shiftStatus = 'buka';
                            $waktuTutup = null;
                        } else {
                            continue; // Shift 2 hari ini belum dimulai
                        }
                    }

                    $modalAwal = 300000;

                    // Buat atau temukan KasirShift
                    $kasirShift = KasirShift::updateOrCreate(
                        [
                            'toko_cabang_id' => $cabang->id,
                            'shift_id' => $shiftDef?->id,
                            'waktu_buka' => $waktuBuka,
                        ],
                        [
                            'user_id' => $kasirUser->id,
                            'nama_shift' => $shiftName,
                            'jam_mulai_jadwal' => $shiftDef?->jam_mulai ?? ($isShift1 ? '11:00' : '16:00'),
                            'jam_selesai_jadwal' => $shiftDef?->jam_selesai ?? ($isShift1 ? '16:00' : '00:00'),
                            'modal_awal' => $modalAwal,
                            'total_penjualan' => 0,
                            'total_tunai' => 0,
                            'total_non_tunai' => 0,
                            'total_transaksi' => 0,
                            'modal_akhir' => $modalAwal,
                            'status' => $shiftStatus,
                            'waktu_tutup' => $waktuTutup,
                            'catatan_buka' => "Buka kasir operasional {$shiftName} outlet {$cabang->nama_cabang}",
                            'catatan_tutup' => $shiftStatus === 'tutup' ? "Tutup kasir operasional {$shiftName} lancar & sesuai" : null,
                        ]
                    );

                    // Jumlah transaksi per shift: 3 - 6 transaksi
                    $trxCount = $isToday ? 3 : rand(3, 6);
                    $shiftTotalPenjualan = 0;
                    $shiftTotalTunai = 0;
                    $shiftTotalNonTunai = 0;

                    // Ambil stok cafe yang tersedia di cabang ini untuk pesanan cafe
                    $availableCafeDetails = FakturBarangDetail::whereHas('fakturBarang', function ($q) use ($cabang) {
                        $q->where('toko_cabang_id', $cabang->id);
                    })->where('qty', '>', 5)->get();

                    for ($t = 1; $t <= $trxCount; $t++) {
                        $dailyTrxCounter++;
                        $nomorTrx = sprintf('TRX-%s-%04d', $datePrefix, $dailyTrxCounter);

                        $film = $films->random();
                        $room = $cabangRooms[($t - 1) % $cabangRooms->count()];
                        $customer = $pelangganPool[array_rand($pelangganPool)];

                        // Aturan Penonton (1-4 orang, jika > 2 ada denda 5.000 / orang)
                        $jumlahPenonton = rand(1, 4);
                        $dendaPenonton = $jumlahPenonton > 2 ? ($jumlahPenonton - 2) * 5000 : 0;

                        $hargaFilm = (float) $film->total_harga > 0 
                            ? (float) $film->total_harga 
                            : ((float) $film->harga_per_menit * (int) $film->durasi_menit);
                        $hargaRoom = 0;
                        $subtotalFilmRoom = $hargaFilm + $hargaRoom + $dendaPenonton;

                        // Rincian Pesanan Menu Cafe (1-3 item makanan / minuman)
                        $subtotalCafe = 0;
                        $cafeItemsToOrder = [];
                        if ($availableCafeDetails->isNotEmpty()) {
                            $orderItemsCount = rand(1, 3);
                            $shuffledCafe = $availableCafeDetails->shuffle()->take($orderItemsCount);

                            foreach ($shuffledCafe as $fDetail) {
                                $orderQty = rand(1, 2);
                                $lineTotal = $orderQty * (float) $fDetail->harga_jual;
                                $subtotalCafe += $lineTotal;

                                $cafeItemsToOrder[] = [
                                    'barang_id' => $fDetail->barang_id,
                                    'satuan_barang_id' => $fDetail->satuan_barang_id,
                                    'qty' => $orderQty,
                                    'harga_satuan' => (float) $fDetail->harga_jual,
                                    'subtotal' => $lineTotal,
                                    'faktur_detail_model' => $fDetail,
                                ];
                            }
                        }

                        // Diskon Promo (sekitar 25% transaksi memakai promo)
                        $diskon = 0;
                        $selectedPromo = null;
                        if (rand(1, 100) <= 25 && $promos->isNotEmpty()) {
                            $selectedPromo = $promos->random();
                            $subtotalKotor = $subtotalFilmRoom + $subtotalCafe;
                            $nilai = trim($selectedPromo->nilai);

                            if (str_contains($nilai, '%')) {
                                $persen = (float) str_replace('%', '', $nilai);
                                $diskon = round(($subtotalKotor * $persen) / 100);
                            } else {
                                $cleanNilai = (float) preg_replace('/[^0-9]/', '', $nilai);
                                $diskon = min($subtotalKotor, $cleanNilai);
                            }
                        }

                        $totalBayar = max(0, ($subtotalFilmRoom + $subtotalCafe) - $diskon);

                        // Metode Pembayaran
                        $randPay = rand(1, 100);
                        if ($randPay <= 40) {
                            $metode = $tipeTunai ?? $allTipeBayar->first();
                            $isCash = true;
                        } elseif ($randPay <= 70) {
                            $metode = $tipeQris ?? $allTipeBayar->first();
                            $isCash = false;
                        } elseif ($randPay <= 85) {
                            $metode = $tipeBca ?? $allTipeBayar->first();
                            $isCash = false;
                        } else {
                            $metode = $tipeMandiri ?? $allTipeBayar->first();
                            $isCash = false;
                        }

                        if ($isCash) {
                            $uangDiterima = ceil($totalBayar / 10000) * 10000;
                            if ($uangDiterima < $totalBayar) {
                                $uangDiterima = $totalBayar;
                            }
                            $kembalian = $uangDiterima - $totalBayar;
                        } else {
                            $uangDiterima = $totalBayar;
                            $kembalian = 0;
                        }

                        $durasiFilm = (int) ($film->durasi_menit ?: 120);

                        // Atur Waktu Transaksi & Pemutaran Film
                        if ($isToday) {
                            // Untuk transaksi terakhir hari ini di cabang Sudirman, buat sedang berjalan sekarang!
                            if ($t === $trxCount && $cabang->id === $cabangSudirman?->id) {
                                $waktuTrx = $now->copy()->subMinutes(30);
                                $waktuMulai = $waktuTrx->copy();
                                $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiFilm);
                                $historyStatus = 'berjalan';
                            } else {
                                $offsetMinutes = ($trxCount - $t + 1) * 75;
                                $waktuTrx = $now->copy()->subMinutes($offsetMinutes);
                                $waktuMulai = $waktuTrx->copy();
                                $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiFilm);
                                $historyStatus = 'selesai';
                            }
                        } else {
                            $minuteOffset = rand(10, 40) + ($t - 1) * 60;
                            $waktuTrx = $waktuBuka->copy()->addMinutes(min($minuteOffset, 280));
                            $waktuMulai = $waktuTrx->copy();
                            $waktuSelesai = $waktuMulai->copy()->addMinutes($durasiFilm);
                            $historyStatus = 'selesai';
                        }

                        // Buat atau update Transaksi
                        $transaksi = Transaksi::updateOrCreate(
                            ['nomor_transaksi' => $nomorTrx],
                            [
                                'kasir_shift_id' => $kasirShift->id,
                                'toko_cabang_id' => $cabang->id,
                                'user_id' => $kasirUser->id,
                                'film_id' => $film->id,
                                'room_id' => $room->id,
                                'tipe_pembayaran_id' => $metode->id,
                                'promo_id' => $selectedPromo?->id,
                                'nama_pelanggan' => $customer,
                                'durasi_menit' => $durasiFilm,
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
                                'waktu_transaksi' => $waktuTrx,
                                'waktu_mulai_film' => $waktuMulai,
                                'waktu_selesai_film' => $waktuSelesai,
                                'catatan' => "Penjualan paket studio & cafe {$cabang->nama_cabang}",
                            ]
                        );

                        // Buat Riwayat Film History
                        $tarifPerMenit = (float) $film->harga_per_menit > 0 
                            ? (float) $film->harga_per_menit 
                            : round($hargaFilm / $durasiFilm, 2);

                        TransaksiFilmHistory::updateOrCreate(
                            [
                                'transaksi_id' => $transaksi->id,
                                'film_id' => $film->id,
                            ],
                            [
                                'room_id' => $room->id,
                                'judul_film' => $film->judul,
                                'waktu_mulai' => $waktuMulai,
                                'waktu_selesai' => $waktuSelesai,
                                'durasi_menit_rencana' => $durasiFilm,
                                'durasi_aktual_menit' => $historyStatus === 'berjalan' ? 30 : $durasiFilm,
                                'tarif_per_menit' => $tarifPerMenit,
                                'biaya_film' => $hargaFilm,
                                'status' => $historyStatus,
                                'selisih_biaya' => 0,
                                'tipe_selisih' => 'pas',
                            ]
                        );

                        // Buat Transaksi Detail Cafe & Potong Stok FIFO
                        foreach ($cafeItemsToOrder as $cItem) {
                            $transaksi->details()->updateOrCreate(
                                [
                                    'transaksi_id' => $transaksi->id,
                                    'barang_id' => $cItem['barang_id'],
                                ],
                                [
                                    'satuan_barang_id' => $cItem['satuan_barang_id'],
                                    'qty' => $cItem['qty'],
                                    'harga_satuan' => $cItem['harga_satuan'],
                                    'subtotal' => $cItem['subtotal'],
                                ]
                            );

                            // Potong stok faktur
                            $fDetail = $cItem['faktur_detail_model'];
                            if ($fDetail && $fDetail->qty >= $cItem['qty']) {
                                $fDetail->decrement('qty', $cItem['qty']);
                                $fDetail->subtotal = $fDetail->qty * $fDetail->harga_awal;
                                $fDetail->save();
                            }
                        }

                        // Akumulasi Shift
                        $shiftTotalPenjualan += $totalBayar;
                        if ($isCash) {
                            $shiftTotalTunai += $totalBayar;
                        } else {
                            $shiftTotalNonTunai += $totalBayar;
                        }
                    }

                    // Update KasirShift Final Totals
                    $kasirShift->update([
                        'total_penjualan' => $shiftTotalPenjualan,
                        'total_tunai' => $shiftTotalTunai,
                        'total_non_tunai' => $shiftTotalNonTunai,
                        'total_transaksi' => $trxCount,
                        'modal_akhir' => $modalAwal + $shiftTotalTunai,
                    ]);
                }
            }
        }
    }
}
