<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cafe\FakturBarangController;
use App\Http\Controllers\Cafe\StokCafeController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Master\BarangController;
use App\Http\Controllers\Master\GenreFilmController;
use App\Http\Controllers\Master\HargaPerdurasiController;
use App\Http\Controllers\Master\JabatanController;
use App\Http\Controllers\Master\JenisBiayaController;
use App\Http\Controllers\Master\MenuCafeController;
use App\Http\Controllers\Master\PromoController;
use App\Http\Controllers\Master\RoomController;
use App\Http\Controllers\Master\SatuanBarangController;
use App\Http\Controllers\Master\TipePembayaranController;
use App\Http\Controllers\Master\TokoCabangController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin']);

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.authenticate');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Film List & Management
        Route::prefix('film')->name('film.')->group(function () {
            Route::get('/', [FilmController::class, 'index'])->name('index');
            Route::post('/', [FilmController::class, 'store'])->name('store');
            Route::put('/{film}', [FilmController::class, 'update'])->name('update');
            Route::delete('/{film}', [FilmController::class, 'destroy'])->name('destroy');
        });

        // Cafe Management (Faktur Barang, Stok, Menu)
        Route::prefix('cafe')->name('cafe.')->group(function () {
            Route::get('stok', [StokCafeController::class, 'index'])->name('stok');
            Route::get('stok/riwayat/{toko_cabang_id}/{barang_id}/{satuan_barang_id}', [StokCafeController::class, 'riwayat'])->name('stok.riwayat');
            Route::put('stok/{toko_cabang_id}/{barang_id}/{satuan_barang_id}', [StokCafeController::class, 'update'])->name('stok.update');
            Route::delete('stok/{toko_cabang_id}/{barang_id}/{satuan_barang_id}', [StokCafeController::class, 'destroy'])->name('stok.destroy');
            Route::get('faktur', [FakturBarangController::class, 'index'])->name('faktur');
            Route::resource('faktur', FakturBarangController::class)->except(['edit', 'update']);
        });

        // Master Data CRUD Routes
        Route::prefix('master')->name('master.')->group(function () {
            // 1. Tipe Pembayaran
            Route::get('tipe-pembayaran', [TipePembayaranController::class, 'index'])->name('tipe-pembayaran');
            Route::resource('tipe-pembayaran', TipePembayaranController::class);

            // 2. Harga Perdurasi
            Route::get('harga-perdurasi', [HargaPerdurasiController::class, 'index'])->name('harga-perdurasi');
            Route::resource('harga-perdurasi', HargaPerdurasiController::class);

            // 3. Toko Cabang
            Route::get('toko-cabang', [TokoCabangController::class, 'index'])->name('toko-cabang');
            Route::resource('toko-cabang', TokoCabangController::class);

            // 4. Room
            Route::get('room', [RoomController::class, 'index'])->name('room');
            Route::resource('room', RoomController::class);

            // 5. Promo
            Route::get('promo', [PromoController::class, 'index'])->name('promo');
            Route::resource('promo', PromoController::class);

            // 6. Menu Cafe Perjenis
            Route::get('menu-cafe', [MenuCafeController::class, 'index'])->name('menu-cafe');
            Route::resource('menu-cafe', MenuCafeController::class);
            Route::resource('menu-cafe-perjenis', MenuCafeController::class);

            // 7. Genre Film
            Route::get('genre-film', [GenreFilmController::class, 'index'])->name('genre-film');
            Route::resource('genre-film', GenreFilmController::class);

            // 8. Jenis Biaya
            Route::get('jenis-biaya', [JenisBiayaController::class, 'index'])->name('jenis-biaya');
            Route::resource('jenis-biaya', JenisBiayaController::class);

            // 9. Jabatan
            Route::get('jabatan', [JabatanController::class, 'index'])->name('jabatan');
            Route::resource('jabatan', JabatanController::class);

            // 10. Satuan Barang
            Route::get('satuan-barang', [SatuanBarangController::class, 'index'])->name('satuan-barang');
            Route::resource('satuan-barang', SatuanBarangController::class);

            // 11. Master Data Barang
            Route::get('barang', [BarangController::class, 'index'])->name('barang');
            Route::resource('barang', BarangController::class);
        });

        // Kepegawaian (Jabatan & Pegawai)
        Route::prefix('kepegawaian')->name('kepegawaian.')->group(function () {
            // Submenu 1: Jabatan
            Route::get('jabatan', [\App\Http\Controllers\Kepegawaian\JabatanController::class, 'index'])->name('jabatan.index');
            Route::resource('jabatan', \App\Http\Controllers\Kepegawaian\JabatanController::class)->except(['index', 'create', 'show', 'edit']);
            Route::get('jabatan/list', [\App\Http\Controllers\Kepegawaian\JabatanController::class, 'index'])->name('jabatan');

            // Submenu 2: Pegawai
            Route::get('pegawai', [\App\Http\Controllers\Kepegawaian\PegawaiController::class, 'index'])->name('pegawai.index');
            Route::resource('pegawai', \App\Http\Controllers\Kepegawaian\PegawaiController::class)->except(['index', 'create', 'show', 'edit']);
            Route::get('pegawai/list', [\App\Http\Controllers\Kepegawaian\PegawaiController::class, 'index'])->name('pegawai');
        });

        // Pengaturan (User)
        Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
            // Submenu User
            Route::get('user', [\App\Http\Controllers\Pengaturan\UserController::class, 'index'])->name('user.index');
            Route::resource('user', \App\Http\Controllers\Pengaturan\UserController::class)->except(['index', 'create', 'show', 'edit']);
            Route::get('user/list', [\App\Http\Controllers\Pengaturan\UserController::class, 'index'])->name('user');
        });

        // Laporan (Penjualan Shift, Riwayat Penjualan Harian, Pembelian Faktur)
        Route::prefix('laporan')->name('laporan.')->group(function () {
            // 1. Laporan Penjualan (Shift Tutup Kasir)
            Route::get('penjualan', [\App\Http\Controllers\Laporan\LaporanPenjualanController::class, 'index'])->name('penjualan');
            Route::get('penjualan/export', [\App\Http\Controllers\Laporan\LaporanPenjualanController::class, 'export'])->name('penjualan.export');
            Route::get('penjualan/shift/{id}', [\App\Http\Controllers\Laporan\LaporanPenjualanController::class, 'detailShift'])->name('penjualan.shift-detail');

            // 2. Riwayat Penjualan (Transaksi Harian)
            Route::get('riwayat', [\App\Http\Controllers\Laporan\RiwayatPenjualanController::class, 'index'])->name('riwayat');
            Route::get('riwayat/export', [\App\Http\Controllers\Laporan\RiwayatPenjualanController::class, 'export'])->name('riwayat.export');
            Route::get('riwayat/detail/{id}', [\App\Http\Controllers\Laporan\RiwayatPenjualanController::class, 'detailTransaksi'])->name('riwayat.detail');

            // 3. Laporan Pembelian (Faktur Barang)
            Route::get('pembelian', [\App\Http\Controllers\Laporan\LaporanPembelianController::class, 'index'])->name('pembelian');
            Route::get('pembelian/export', [\App\Http\Controllers\Laporan\LaporanPembelianController::class, 'export'])->name('pembelian.export');
            Route::get('pembelian/faktur/{id}', [\App\Http\Controllers\Laporan\LaporanPembelianController::class, 'detailFaktur'])->name('pembelian.faktur-detail');

            // 4. Laporan Laba & Rugi
            Route::get('laba-rugi', [\App\Http\Controllers\Laporan\LaporanLabaRugiController::class, 'index'])->name('laba-rugi');
            Route::get('laba-rugi/export', [\App\Http\Controllers\Laporan\LaporanLabaRugiController::class, 'export'])->name('laba-rugi.export');
        });

        // Keuangan (Pemasukan & Pengeluaran)
        Route::prefix('keuangan')->name('keuangan.')->group(function () {
            // Pemasukan Manual
            Route::resource('pemasukan', \App\Http\Controllers\Keuangan\PemasukanController::class)->except(['create', 'show', 'edit']);
            Route::get('pemasukan', [\App\Http\Controllers\Keuangan\PemasukanController::class, 'index'])->name('pemasukan');
            Route::get('pemasukan/list', [\App\Http\Controllers\Keuangan\PemasukanController::class, 'index'])->name('pemasukan.index');

            // Pengeluaran Operasional
            Route::resource('pengeluaran', \App\Http\Controllers\Keuangan\PengeluaranController::class)->except(['create', 'show', 'edit']);
            Route::get('pengeluaran', [\App\Http\Controllers\Keuangan\PengeluaranController::class, 'index'])->name('pengeluaran');
            Route::get('pengeluaran/list', [\App\Http\Controllers\Keuangan\PengeluaranController::class, 'index'])->name('pengeluaran.index');
        });
    });

Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::view('/', 'kasir.index')->name('index');
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir');
        Route::post('/shift/buka', [KasirController::class, 'bukaShift'])->name('shift.buka');
        Route::post('/shift/tutup', [KasirController::class, 'tutupShift'])->name('shift.tutup');
        Route::post('/cek-promo', [KasirController::class, 'cekPromo'])->name('cek-promo');
        Route::post('/checkout', [KasirController::class, 'checkout'])->name('checkout');
        Route::get('/struk/{id}', [KasirController::class, 'struk'])->name('struk');
    });
