<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cafe\FakturBarangController;
use App\Http\Controllers\Cafe\StokCafeController;
use App\Http\Controllers\FilmController;
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
    });

Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::view('/', 'kasir.index')->name('index');
        Route::view('/kasir', 'kasir.kasir')->name('kasir');
    });
