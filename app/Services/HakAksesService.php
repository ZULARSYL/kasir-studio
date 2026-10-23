<?php

namespace App\Services;

use App\Models\HakAkses;
use App\Models\Jabatan;
use App\Models\User;

class HakAksesService
{
    /**
     * Definisi struktur modul, menu utama, dan submenu di aplikasi Pressent Studio.
     *
     * @return array
     */
    public static function getMenuStructure(): array
    {
        return [
            'main' => [
                'title' => 'Menu Utama & POS',
                'icon' => 'bi-grid-fill',
                'parent_key' => 'main',
                'menus' => [
                    'dashboard' => [
                        'label' => 'Dashboard Utama (Admin)',
                        'icon' => 'bi-speedometer2',
                        'route' => 'admin.dashboard',
                        'url' => '/admin',
                        'description' => 'Akses dashboard utama analitik metrik dan ringkasan studio.',
                    ],
                    'kasir_dashboard' => [
                        'label' => 'Dashboard Kasir (Monitoring)',
                        'icon' => 'bi-grid-fill',
                        'route' => 'kasir.dashboard',
                        'url' => '/kasir',
                        'description' => 'Akses dashboard kasir: monitoring room studio & transaksi hari ini.',
                    ],
                    'kasir_pos' => [
                        'label' => 'Menu Kasir (POS Transaksi)',
                        'icon' => 'bi-shop',
                        'route' => 'admin.kasir.index',
                        'url' => '/admin/kasir',
                        'description' => 'Akses POS pemesanan tiket film, room, cafe, dan cetak struk nota.',
                    ],
                ],
            ],

            'film' => [
                'title' => 'Layanan Film Studio',
                'icon' => 'bi-film',
                'parent_key' => 'film',
                'menus' => [
                    'film.index' => [
                        'label' => 'List Film & Manajemen Film',
                        'icon' => 'bi-camera-reels',
                        'route' => 'admin.film.index',
                        'url' => '/admin/film',
                        'description' => 'Katalog film, tambah, ubah, dan hapus data film studio.',
                    ],
                ],
            ],

            'cafe' => [
                'title' => 'Cafe & Makanan Minuman',
                'icon' => 'bi-cup-hot',
                'parent_key' => 'cafe',
                'menus' => [
                    'cafe.stok' => [
                        'label' => 'Stok Barang Cafe',
                        'icon' => 'bi-boxes',
                        'route' => 'admin.cafe.stok',
                        'url' => '/admin/cafe/stok',
                        'description' => 'Monitoring stok menu cafe, sisa unit, dan riwayat per cabang.',
                    ],
                    'cafe.faktur' => [
                        'label' => 'Faktur Barang Masuk',
                        'icon' => 'bi-receipt-cutoff',
                        'route' => 'admin.cafe.faktur',
                        'url' => '/admin/cafe/faktur',
                        'description' => 'Pencatatan faktur barang masuk dan penetapan harga jual cafe.',
                    ],
                ],
            ],

            'keuangan' => [
                'title' => 'Keuangan (Arus Kas)',
                'icon' => 'bi-wallet2',
                'parent_key' => 'keuangan',
                'menus' => [
                    'keuangan.pemasukan' => [
                        'label' => 'Pemasukan Kas',
                        'icon' => 'bi-arrow-down-left-circle text-success',
                        'route' => 'admin.keuangan.pemasukan',
                        'url' => '/admin/keuangan/pemasukan',
                        'description' => 'Pencatatan arus kas masuk manual di luar transaksi kasir.',
                    ],
                    'keuangan.pengeluaran' => [
                        'label' => 'Pengeluaran Operasional',
                        'icon' => 'bi-arrow-up-right-circle text-danger',
                        'route' => 'admin.keuangan.pengeluaran',
                        'url' => '/admin/keuangan/pengeluaran',
                        'description' => 'Pencatatan biaya pengeluaran operasional (listrik, air, gaji, sewa, dll).',
                    ],
                ],
            ],

            'master' => [
                'title' => 'Master Data Sistem',
                'icon' => 'bi-database-fill-gear',
                'parent_key' => 'master',
                'menus' => [
                    'master.tipe-pembayaran' => [
                        'label' => 'Tipe Pembayaran',
                        'icon' => 'bi-credit-card',
                        'route' => 'admin.master.tipe-pembayaran',
                        'url' => '/admin/master/tipe-pembayaran',
                        'description' => 'Master tipe pembayaran (Tunai, QRIS, Transfer, Debit).',
                    ],
                    'master.toko-cabang' => [
                        'label' => 'Toko Cabang',
                        'icon' => 'bi-buildings',
                        'route' => 'admin.master.toko-cabang',
                        'url' => '/admin/master/toko-cabang',
                        'description' => 'Data cabang outlet studio dan cafe beserta alamat & kontak.',
                    ],
                    'master.room' => [
                        'label' => 'Room Studio',
                        'icon' => 'bi-door-open',
                        'route' => 'admin.master.room',
                        'url' => '/admin/master/room',
                        'description' => 'Daftar room studio per cabang beserta kapasitas penonton.',
                    ],
                    'master.promo' => [
                        'label' => 'Promo & Voucher',
                        'icon' => 'bi-percent',
                        'route' => 'admin.master.promo',
                        'url' => '/admin/master/promo',
                        'description' => 'Kelola kode voucher diskon penonton dan masa berlaku.',
                    ],
                    'master.menu-cafe' => [
                        'label' => 'Menu Cafe Perjenis',
                        'icon' => 'bi-tag',
                        'route' => 'admin.master.menu-cafe',
                        'url' => '/admin/master/menu-cafe',
                        'description' => 'Kategori menu cafe (Makanan, Minuman, Snack, dsb).',
                    ],
                    'master.genre-film' => [
                        'label' => 'Genre Film',
                        'icon' => 'bi-camera-video',
                        'route' => 'admin.master.genre-film',
                        'url' => '/admin/master/genre-film',
                        'description' => 'Kategori genre film (Action, Horror, Drama, dll).',
                    ],
                    'master.jenis-biaya' => [
                        'label' => 'Master Jenis Biaya',
                        'icon' => 'bi-cash-coin',
                        'route' => 'admin.master.jenis-biaya',
                        'url' => '/admin/master/jenis-biaya',
                        'description' => 'Kategori master biaya untuk pemasukan dan beban operasional.',
                    ],
                    'master.jabatan' => [
                        'label' => 'Master Jabatan',
                        'icon' => 'bi-person-badge',
                        'route' => 'admin.master.jabatan',
                        'url' => '/admin/master/jabatan',
                        'description' => 'Master nama jabatan pegawai di lingkungan studio.',
                    ],
                    'master.satuan-barang' => [
                        'label' => 'Satuan Barang',
                        'icon' => 'bi-box-seam',
                        'route' => 'admin.master.satuan-barang',
                        'url' => '/admin/master/satuan-barang',
                        'description' => 'Master satuan barang cafe (Pcs, Botol, Dus, Pack, Cup).',
                    ],
                    'master.barang' => [
                        'label' => 'Master Data Barang',
                        'icon' => 'bi-box2',
                        'route' => 'admin.master.barang',
                        'url' => '/admin/master/barang',
                        'description' => 'Katalog produk dan barang yang dijual di cafe.',
                    ],
                ],
            ],

            'laporan' => [
                'title' => 'Laporan & Rekapitulasi',
                'icon' => 'bi-bar-chart-line-fill',
                'parent_key' => 'laporan',
                'menus' => [
                    'laporan.riwayat' => [
                        'label' => 'Riwayat Penjualan (Harian)',
                        'icon' => 'bi-clock-history',
                        'route' => 'admin.laporan.riwayat',
                        'url' => '/admin/laporan/riwayat',
                        'description' => 'Daftar riwayat transaksi per nota dan cetak ulang struk.',
                    ],
                    'laporan.penjualan' => [
                        'label' => 'Laporan Penjualan (Shift)',
                        'icon' => 'bi-graph-up-arrow text-success',
                        'route' => 'admin.laporan.penjualan',
                        'url' => '/admin/laporan/penjualan',
                        'description' => 'Rekapitulasi penjualan shift tutup kasir per outlet.',
                    ],
                    'laporan.pembelian' => [
                        'label' => 'Laporan Pembelian Faktur',
                        'icon' => 'bi-cart-check',
                        'route' => 'admin.laporan.pembelian',
                        'url' => '/admin/laporan/pembelian',
                        'description' => 'Rekapitulasi pembelian barang dan faktur belanja cafe.',
                    ],
                    'laporan.stok' => [
                        'label' => 'Laporan Stok Barang',
                        'icon' => 'bi-box-seam',
                        'route' => 'admin.laporan.stok',
                        'url' => '/admin/laporan/stok',
                        'description' => 'Laporan stok barang cafe dan total aset persediaan.',
                    ],
                    'laporan.laba-rugi' => [
                        'label' => 'Laporan Laba & Rugi',
                        'icon' => 'bi-cash-stack',
                        'route' => 'admin.laporan.laba-rugi',
                        'url' => '/admin/laporan/laba-rugi',
                        'description' => 'Laporan pendapatan, HPP, beban operasional, dan laba bersih.',
                    ],
                ],
            ],

            'kepegawaian' => [
                'title' => 'Kepegawaian & SDM',
                'icon' => 'bi-people-fill',
                'parent_key' => 'kepegawaian',
                'menus' => [
                    'kepegawaian.jabatan' => [
                        'label' => 'Jabatan Pegawai',
                        'icon' => 'bi-person-badge',
                        'route' => 'admin.kepegawaian.jabatan.index',
                        'url' => '/admin/kepegawaian/jabatan',
                        'description' => 'Pengelolaan data jabatan dan penugasan divisi.',
                    ],
                    'kepegawaian.pegawai' => [
                        'label' => 'Data Pegawai',
                        'icon' => 'bi-person-lines-fill',
                        'route' => 'admin.kepegawaian.pegawai.index',
                        'url' => '/admin/kepegawaian/pegawai',
                        'description' => 'Biodata pegawai, penempatan cabang, dan informasi gaji.',
                    ],
                ],
            ],

            'pengaturan' => [
                'title' => 'Sistem & Pengaturan',
                'icon' => 'bi-gear-fill',
                'parent_key' => 'pengaturan',
                'menus' => [
                    'pengaturan.user' => [
                        'label' => 'Kelola User',
                        'icon' => 'bi-person-gear',
                        'route' => 'admin.pengaturan.user.index',
                        'url' => '/admin/pengaturan/user',
                        'description' => 'Manajemen akun login sistem, password, dan penempatan.',
                    ],
                    'pengaturan.hak-akses' => [
                        'label' => 'Hak Akses Role & Jabatan',
                        'icon' => 'bi-key',
                        'route' => 'admin.pengaturan.hak-akses',
                        'url' => '/admin/pengaturan/hak-akses',
                        'description' => 'Konfigurasi izin menu & submenu per role dan jabatan.',
                    ],
                    'pengaturan.struk' => [
                        'label' => 'Template untuk Struk',
                        'icon' => 'bi-printer',
                        'route' => 'admin.pengaturan.struk',
                        'url' => '/admin/pengaturan/struk',
                        'description' => 'Pengaturan format header/footer struk kasir thermal.',
                    ],
                ],
            ],
        ];
    }

    /**
     * Ambil seluruh kunci (keys) menu & submenu yang ada di sistem.
     *
     * @return array
     */
    public static function getAllMenuKeys(): array
    {
        $keys = [];
        foreach (self::getMenuStructure() as $moduleKey => $module) {
            $keys[] = $moduleKey;
            foreach (array_keys($module['menus']) as $submenuKey) {
                $keys[] = $submenuKey;
            }
        }
        return array_values(array_unique($keys));
    }

    /**
     * Menu bawaan untuk Role default (jika belum pernah dikonfigurasi).
     *
     * @param string $role
     * @return array
     */
    public static function getDefaultMenusForRole(string $role): array
    {
        $normalizedRole = strtolower(trim($role));

        if ($normalizedRole === 'admin') {
            // Admin default memiliki akses ke seluruh menu & submenu
            return self::getAllMenuKeys();
        }

        if ($normalizedRole === 'kasir') {
            // Kasir default memiliki akses ke Dashboard Kasir dan POS
            return [
                'main',
                'kasir_dashboard',
                'kasir_pos',
            ];
        }

        return ['dashboard'];
    }

    /**
     * Ambil daftar menu yang diizinkan untuk target (role atau jabatan).
     *
     * @param string $tipe
     * @param string|null $role
     * @param int|null $jabatanId
     * @return array
     */
    public static function getPermissions(string $tipe, ?string $role = null, ?int $jabatanId = null): array
    {
        try {
            $targetKey = HakAkses::makeTargetKey($tipe, $role, $jabatanId);
            $record = HakAkses::where('target_key', $targetKey)->first();

            if ($record && is_array($record->menus)) {
                return $record->menus;
            }
        } catch (\Throwable $e) {
            // Fallback jika terjadi kendala akses database
        }

        // Jika belum ada di database, gunakan default berdasarkan role
        if ($tipe === 'role' && $role) {
            return self::getDefaultMenusForRole($role);
        }

        return [];
    }

    /**
     * Simpan perubahan hak akses menu untuk target tertentu.
     *
     * @param string $tipe
     * @param array $menus
     * @param string|null $role
     * @param int|null $jabatanId
     * @return HakAkses
     */
    public static function savePermissions(string $tipe, array $menus, ?string $role = null, ?int $jabatanId = null): HakAkses
    {
        $targetKey = HakAkses::makeTargetKey($tipe, $role, $jabatanId);

        // Safeguard: Role 'admin' tidak boleh kehilangan akses ke Hak Akses & Dashboard
        if ($tipe === 'role' && strtolower(trim($role ?? '')) === 'admin') {
            if (! in_array('pengaturan.hak-akses', $menus)) {
                $menus[] = 'pengaturan.hak-akses';
            }
            if (! in_array('pengaturan', $menus)) {
                $menus[] = 'pengaturan';
            }
            if (! in_array('dashboard', $menus)) {
                $menus[] = 'dashboard';
            }
        }

        // Normalisasi array
        $cleanMenus = array_values(array_unique(array_filter($menus)));

        return HakAkses::updateOrCreate(
            ['target_key' => $targetKey],
            [
                'tipe' => $tipe,
                'role' => $role,
                'jabatan_id' => $jabatanId,
                'menus' => $cleanMenus,
            ]
        );
    }

    /**
     * Ambil seluruh menu yang diizinkan untuk user yang sedang aktif.
     * Hierarki prioritas:
     * 1. Hak akses khusus Jabatan user (jika user memiliki jabatan dan dikonfigurasi).
     * 2. Hak akses Role user (admin / kasir).
     * 3. Default sistem.
     *
     * @param User|null $user
     * @return array
     */
    public static function getUserAllowedMenus(?User $user): array
    {
        if (! $user) {
            return [];
        }

        try {
            // 1. Cek konfigurasi jabatan jika user memiliki jabatan_id
            if ($user->jabatan_id) {
                $jabatanKey = HakAkses::makeTargetKey('jabatan', null, $user->jabatan_id);
                $jabatanRecord = HakAkses::where('target_key', $jabatanKey)->first();
                if ($jabatanRecord && is_array($jabatanRecord->menus) && count($jabatanRecord->menus) > 0) {
                    return $jabatanRecord->menus;
                }
            }

            // 2. Cek konfigurasi role
            $roleKey = HakAkses::makeTargetKey('role', $user->role, null);
            $roleRecord = HakAkses::where('target_key', $roleKey)->first();
            if ($roleRecord && is_array($roleRecord->menus)) {
                return $roleRecord->menus;
            }
        } catch (\Throwable $e) {
            // Fallback aman ke default role
        }

        // 3. Fallback ke default role
        return self::getDefaultMenusForRole($user->role ?? '');
    }

    /**
     * Cek apakah user berhak mengakses menu atau submenu tertentu.
     *
     * @param User|null $user
     * @param string $menuKey
     * @return bool
     */
    public static function canAccess(?User $user, string $menuKey): bool
    {
        if (! $user) {
            return false;
        }

        // Safeguard untuk Admin: menu Hak Akses selalu dapat diakses oleh admin
        if ($user->role === 'admin' && in_array($menuKey, ['pengaturan.hak-akses', 'pengaturan', 'dashboard'])) {
            return true;
        }

        $allowedMenus = self::getUserAllowedMenus($user);

        // Jika kunci cocok secara langsung
        if (in_array($menuKey, $allowedMenus, true)) {
            return true;
        }

        // Jika mengecek parent modul (misal: 'cafe', 'keuangan', 'master', 'laporan', 'kepegawaian', 'pengaturan')
        // Berikan izin jika salah satu submenu di dalamnya diizinkan
        foreach ($allowedMenus as $allowedKey) {
            if (str_starts_with($allowedKey, $menuKey . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolusi nama route Laravel ke kunci (key) menu di sistem hak akses.
     *
     * @param string|null $routeName
     * @return string|null
     */
    public static function resolveMenuKeyFromRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        if ($routeName === 'admin.dashboard') {
            return 'dashboard';
        }

        if ($routeName === 'kasir.dashboard' || $routeName === 'kasir.index') {
            return 'kasir_dashboard';
        }

        if ($routeName === 'kasir.kasir' || str_starts_with($routeName, 'kasir.')) {
            return 'kasir_pos';
        }

        if (str_starts_with($routeName, 'admin.kasir.')) {
            return 'kasir_pos';
        }

        if (str_starts_with($routeName, 'admin.')) {
            $stripped = substr($routeName, 6); // remove 'admin.'

            // Check exact matches or prefixes in all known keys
            $allKeys = self::getAllMenuKeys();
            if (in_array($stripped, $allKeys, true)) {
                return $stripped;
            }

            foreach ($allKeys as $key) {
                if (str_starts_with($stripped, $key . '.') || str_starts_with($stripped, $key)) {
                    return $key;
                }
            }

            if (str_starts_with($stripped, 'film.')) {
                return 'film.index';
            }
        }

        return null;
    }
}

