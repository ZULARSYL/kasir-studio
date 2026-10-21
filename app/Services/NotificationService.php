<?php

namespace App\Services;

use App\Models\FakturBarang;
use App\Models\KasirShift;
use App\Models\Pengeluaran;
use App\Models\Promo;
use App\Models\Room;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class NotificationService
{
    /**
     * Ambil seluruh notifikasi dinamis berdasarkan role user yang sedang login.
     *
     * @param User|null $user
     * @return array
     */
    public function getNotifications(?User $user): array
    {
        if (! $user) {
            return [
                'unread_count' => 0,
                'total_count' => 0,
                'items' => [],
            ];
        }

        $readIds = session()->get('read_notification_ids', []);
        $items = [];

        if ($user->role === 'admin') {
            $items = $this->getAdminNotifications($user);
        } else {
            $items = $this->getKasirNotifications($user);
        }

        // Tentukan status is_read untuk setiap item
        $unreadCount = 0;
        foreach ($items as &$item) {
            $isRead = in_array($item['id'], $readIds);
            $item['is_read'] = $isRead;
            if (! $isRead) {
                $unreadCount++;
            }
        }
        unset($item);

        return [
            'unread_count' => $unreadCount,
            'total_count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Tandai semua notifikasi saat ini sebagai sudah dibaca.
     *
     * @param User|null $user
     * @return void
     */
    public function markAllAsRead(?User $user): void
    {
        $notifications = $this->getNotifications($user);
        $currentIds = array_column($notifications['items'], 'id');
        $existing = session()->get('read_notification_ids', []);
        $merged = array_unique(array_merge($existing, $currentIds));
        session()->put('read_notification_ids', $merged);
    }

    /**
     * Tandai notifikasi spesifik sebagai sudah dibaca.
     *
     * @param string $id
     * @return void
     */
    public function markAsRead(string $id): void
    {
        $existing = session()->get('read_notification_ids', []);
        if (! in_array($id, $existing)) {
            $existing[] = $id;
            session()->put('read_notification_ids', $existing);
        }
    }

    /**
     * Hasilkan notifikasi spesifik dan urgent untuk role ADMIN (Tindakan Hari Ini Saja):
     * 1. Buka Kasir (Shift kasir buka hari ini / Outlet belum buka)
     * 2. Tutup Kasir (Shift kasir tutup hari ini yang perlu verifikasi)
     * 3. Notifikasi Stok (< 10 unit)
     *
     * @param User $admin
     * @return array
     */
    protected function getAdminNotifications(User $admin): array
    {
        $items = [];
        $now = Carbon::now();
        $today = Carbon::today();

        // 1. FITUR BUKA KASIR (Aktivitas Buka Kasir Hari Ini)
        $bukaShifts = KasirShift::with(['user', 'tokoCabang'])
            ->where('status', 'buka')
            ->whereDate('waktu_buka', $today)
            ->orderBy('waktu_buka', 'desc')
            ->get();

        foreach ($bukaShifts as $shift) {
            $kasirName = $shift->user?->name ?? 'Kasir';
            $cabangName = $shift->tokoCabang?->nama_cabang ?? 'Outlet';
            $jamBuka = $shift->waktu_buka ? $shift->waktu_buka->format('H:i') : '';
            $modalAwal = number_format($shift->modal_awal ?? 0, 0, ',', '.');

            $items[] = [
                'id' => 'admin_buka_kasir_' . $shift->id,
                'role' => 'admin',
                'category' => 'Buka Kasir',
                'title' => "Buka Kasir: {$kasirName}",
                'message' => "Kasir {$kasirName} telah membuka kasir di {$cabangName} pukul {$jamBuka} WIB (Modal Kas: Rp {$modalAwal}).",
                'icon' => 'bi-shop',
                'icon_bg' => 'bg-success-subtle text-success',
                'badge_text' => 'Buka Kasir',
                'badge_class' => 'bg-success-subtle text-success',
                'time_ago' => $shift->waktu_buka ? $shift->waktu_buka->locale('id')->diffForHumans() : 'Hari ini',
                'url' => Route::has('admin.laporan.penjualan') ? route('admin.laporan.penjualan') : url('/admin/laporan/penjualan'),
                'created_at' => $shift->waktu_buka ?? $now,
            ];
        }

        // Cek cabang yang belum buka kasir hari ini (Perlu tindakan buka toko hari ini)
        $cabangsWithShiftToday = KasirShift::whereDate('created_at', $today)
            ->pluck('toko_cabang_id')
            ->unique();
        $cabangsBelumBuka = \App\Models\TokoCabang::whereNotIn('id', $cabangsWithShiftToday)->get();
        foreach ($cabangsBelumBuka as $cabang) {
            $items[] = [
                'id' => 'admin_outlet_not_open_' . $cabang->id . '_' . $today->format('Ymd'),
                'role' => 'admin',
                'category' => 'Buka Kasir',
                'title' => "Outlet Belum Buka: {$cabang->nama_cabang}",
                'message' => "Belum ada kasir yang membuka shift hari ini di {$cabang->nama_cabang}. Harap pastikan outlet siap melayani.",
                'icon' => 'bi-exclamation-circle-fill',
                'icon_bg' => 'bg-warning-subtle text-warning',
                'badge_text' => 'Belum Buka',
                'badge_class' => 'bg-warning-subtle text-warning',
                'time_ago' => 'Hari Ini',
                'url' => Route::has('admin.kasir.index') ? route('admin.kasir.index') : url('/admin/kasir'),
                'created_at' => $now,
            ];
        }

        // 2. FITUR TUTUP KASIR (Aktivitas Tutup Kasir Hari Ini)
        $tutupShifts = KasirShift::with(['user', 'tokoCabang'])
            ->where('status', 'tutup')
            ->whereDate('waktu_tutup', $today)
            ->orderBy('waktu_tutup', 'desc')
            ->get();

        foreach ($tutupShifts as $shift) {
            $kasirName = $shift->user?->name ?? 'Kasir';
            $cabangName = $shift->tokoCabang?->nama_cabang ?? 'Outlet';
            $totalSales = number_format($shift->total_penjualan ?? 0, 0, ',', '.');
            $modalAkhir = number_format($shift->modal_akhir ?? 0, 0, ',', '.');

            $items[] = [
                'id' => 'admin_tutup_kasir_' . $shift->id,
                'role' => 'admin',
                'category' => 'Tutup Kasir',
                'title' => "Tutup Kasir: {$kasirName}",
                'message' => "Kasir {$kasirName} telah menutup kasir di {$cabangName} (Penjualan: Rp {$totalSales}, Setoran Kas: Rp {$modalAkhir}). Perlu verifikasi rekap.",
                'icon' => 'bi-door-closed-fill',
                'icon_bg' => 'bg-primary-subtle text-primary',
                'badge_text' => 'Tutup Kasir',
                'badge_class' => 'bg-primary-subtle text-primary',
                'time_ago' => $shift->waktu_tutup ? $shift->waktu_tutup->locale('id')->diffForHumans() : 'Hari ini',
                'url' => Route::has('admin.laporan.penjualan') ? route('admin.laporan.penjualan') : url('/admin/laporan/penjualan'),
                'created_at' => $shift->waktu_tutup ?? $now,
            ];
        }

        // 3. FITUR NOTIFIKASI STOK (Khusus Stok Kurang Dari 10)
        $lowStockQuery = DB::table('faktur_barang_details')
            ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
            ->join('barangs', 'faktur_barang_details.barang_id', '=', 'barangs.id')
            ->join('toko_cabang', 'faktur_barangs.toko_cabang_id', '=', 'toko_cabang.id')
            ->leftJoin('satuan_barangs', 'faktur_barang_details.satuan_barang_id', '=', 'satuan_barangs.id')
            ->select([
                'faktur_barangs.toko_cabang_id',
                'faktur_barang_details.barang_id',
                'barangs.nama_barang',
                'toko_cabang.nama_cabang',
                'satuan_barangs.nama_satuan',
                DB::raw('SUM(faktur_barang_details.qty) as total_stok'),
            ])
            ->groupBy(
                'faktur_barangs.toko_cabang_id',
                'faktur_barang_details.barang_id',
                'barangs.nama_barang',
                'toko_cabang.nama_cabang',
                'satuan_barangs.nama_satuan'
            )
            ->having('total_stok', '<', 10)
            ->orderBy('total_stok', 'asc')
            ->limit(10)
            ->get();

        foreach ($lowStockQuery as $item) {
            $stok = (int) $item->total_stok;
            $satuan = $item->nama_satuan ?: 'Unit';
            $isCritical = $stok <= 3;

            $items[] = [
                'id' => "admin_low_stock_{$item->toko_cabang_id}_{$item->barang_id}",
                'role' => 'admin',
                'category' => 'Notifikasi Stok',
                'title' => "Stok Menipis (< 10): {$item->nama_barang}",
                'message' => "Stok {$item->nama_barang} tersisa {$stok} {$satuan} di {$item->nama_cabang}. Segera lakukan pemesanan barang.",
                'icon' => 'bi-exclamation-triangle-fill',
                'icon_bg' => $isCritical ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning',
                'badge_text' => "Sisa {$stok} Unit",
                'badge_class' => $isCritical ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning',
                'url' => Route::has('admin.laporan.stok') ? route('admin.laporan.stok') : url('/admin/laporan/stok'),
                'created_at' => $now,
            ];
        }

        return $items;
    }

    /**
     * Hasilkan notifikasi spesifik untuk role KASIR:
     * HANYA fitur ROOM dan PROMO saja.
     *
     * @param User $kasir
     * @return array
     */
    protected function getKasirNotifications(User $kasir): array
    {
        $items = [];
        $now = Carbon::now();
        $today = Carbon::today();

        $activeShift = KasirShift::getActiveShift($kasir->id);
        $activeCabangId = $activeShift?->toko_cabang_id ?: $kasir->toko_cabang_id;

        // 1. FITUR ROOM (Monitoring room studio cabang kasir hari ini)
        if ($activeCabangId) {
            $rooms = Room::where('toko_cabang_id', $activeCabangId)->get();

            foreach ($rooms as $room) {
                $latestTrx = Transaksi::with('film')
                    ->where('room_id', $room->id)
                    ->where('status', 'selesai')
                    ->whereDate('waktu_transaksi', $today)
                    ->latest('waktu_transaksi')
                    ->first();

                if ($latestTrx) {
                    $mulai = Carbon::parse($latestTrx->waktu_transaksi);
                    $durasi = (int) ($latestTrx->durasi_menit ?: ($latestTrx->film?->durasi_menit ?? 120));
                    $selesai = $mulai->copy()->addMinutes($durasi);
                    $filmJudul = $latestTrx->film?->judul ?? 'Film Studio';

                    // Sisa waktu
                    $sisaMenit = (int) $now->diffInMinutes($selesai, false);

                    // Kondisi A: Sedang tayang dan sisa waktu <= 15 menit (Urgent)
                    if ($now->greaterThanOrEqualTo($mulai) && $now->lessThan($selesai) && $sisaMenit <= 15) {
                        $items[] = [
                            'id' => "kasir_room_expiring_{$room->id}_{$latestTrx->id}",
                            'role' => 'kasir',
                            'category' => 'Room Studio',
                            'title' => "Room {$room->nama} Hampir Selesai",
                            'message' => "Penayangan film '{$filmJudul}' tersisa {$sisaMenit} menit lagi. Persiapkan pembersihan room.",
                            'icon' => 'bi-clock-history',
                            'icon_bg' => 'bg-warning-subtle text-warning',
                            'badge_text' => "{$sisaMenit} Mnt",
                            'badge_class' => 'bg-warning-subtle text-warning',
                            'time_ago' => "Sisa {$sisaMenit} Menit",
                            'url' => Route::has('kasir.dashboard') ? route('kasir.dashboard') : url('/kasir'),
                            'created_at' => $now,
                        ];
                    }
                    // Kondisi B: Waktu baru saja habis / selesai (dalam 60 menit terakhir)
                    elseif ($now->greaterThanOrEqualTo($selesai) && $now->diffInMinutes($selesai) <= 60) {
                        $items[] = [
                            'id' => "kasir_room_ended_{$room->id}_{$latestTrx->id}",
                            'role' => 'kasir',
                            'category' => 'Room Studio',
                            'title' => "Room {$room->nama} Waktu Selesai",
                            'message' => "Penayangan film '{$filmJudul}' telah berakhir. Harap segera bersihkan room untuk sesi berikutnya.",
                            'icon' => 'bi-bell-fill',
                            'icon_bg' => 'bg-danger-subtle text-danger',
                            'badge_text' => 'Waktu Habis',
                            'badge_class' => 'bg-danger-subtle text-danger',
                            'time_ago' => $selesai->locale('id')->diffForHumans(),
                            'url' => Route::has('kasir.dashboard') ? route('kasir.dashboard') : url('/kasir'),
                            'created_at' => $selesai,
                        ];
                    }
                    // Kondisi C: Sedang tayang (sisa waktu > 15 menit)
                    elseif ($now->greaterThanOrEqualTo($mulai) && $now->lessThan($selesai)) {
                        $items[] = [
                            'id' => "kasir_room_occupied_{$room->id}_{$latestTrx->id}",
                            'role' => 'kasir',
                            'category' => 'Room Studio',
                            'title' => "Room {$room->nama} Sedang Terpakai",
                            'message' => "Sedang menayangkan film '{$filmJudul}' (Sisa {$sisaMenit} menit tayang).",
                            'icon' => 'bi-display',
                            'icon_bg' => 'bg-info-subtle text-info',
                            'badge_text' => 'Sedang Tayang',
                            'badge_class' => 'bg-info-subtle text-info',
                            'time_ago' => "Sisa {$sisaMenit} Mnt",
                            'url' => Route::has('kasir.dashboard') ? route('kasir.dashboard') : url('/kasir'),
                            'created_at' => $now,
                        ];
                    }
                }
            }
        }

        // 2. FITUR PROMO (Promo & Voucher Aktif Hari Ini)
        $activePromos = Promo::where('status', 'aktif')->latest('id')->limit(5)->get();
        foreach ($activePromos as $promo) {
            $items[] = [
                'id' => 'kasir_promo_' . $promo->id,
                'role' => 'kasir',
                'category' => 'Promo Voucher',
                'title' => "Promo Aktif: {$promo->nama_promo}",
                'message' => "Gunakan kode voucher '{$promo->kode_voucher}' ({$promo->nilai}) untuk diskon pelanggan hari ini.",
                'icon' => 'bi-percent',
                'icon_bg' => 'bg-success-subtle text-success',
                'badge_text' => 'Promo Hari Ini',
                'badge_class' => 'bg-success-subtle text-success',
                'time_ago' => 'Aktif',
                'url' => Route::has('kasir.kasir') ? route('kasir.kasir') : url('/kasir/kasir'),
                'created_at' => $now,
            ];
        }

        return $items;
    }
}
