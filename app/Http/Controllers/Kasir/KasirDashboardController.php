<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\KasirShift;
use App\Models\Room;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasirDashboardController extends Controller
{
    /**
     * Tampilkan antarmuka Dashboard Kasir.
     * Menampilkan metrik utama, riwayat pembelian/transaksi hari ini,
     * serta status monitoring room studio (room terpakai & sisa waktunya).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeShift = KasirShift::getActiveShift($user->id);

        // 1. Tentukan Toko Cabang Kasir
        $tokoCabangs = TokoCabang::orderBy('nama_cabang')->get();
        $selectedCabangId = $request->get('toko_cabang_id');

        if ($selectedCabangId && $selectedCabangId !== 'all') {
            $activeCabangId = $selectedCabangId;
        } elseif ($activeShift && $activeShift->toko_cabang_id) {
            $activeCabangId = $activeShift->toko_cabang_id;
        } elseif ($user->toko_cabang_id) {
            $activeCabangId = $user->toko_cabang_id;
        } else {
            $activeCabangId = $tokoCabangs->first()?->id;
        }

        $currentCabang = $activeCabangId ? TokoCabang::find($activeCabangId) : null;
        $namaCabang = $currentCabang?->nama_cabang ?? 'Semua Outlet';

        $today = Carbon::today();
        $now = Carbon::now();

        // 2. Metrik KPI Hari Ini (Today)
        $trxTodayQuery = Transaksi::whereDate('waktu_transaksi', $today);
        if ($activeCabangId) {
            $trxTodayQuery->where('toko_cabang_id', $activeCabangId);
        }

        $totalTransaksiToday = (clone $trxTodayQuery)->count();
        $totalPendapatanToday = (float) (clone $trxTodayQuery)->sum('total_bayar');
        $totalFilmToday = (clone $trxTodayQuery)->whereNotNull('film_id')->count();

        // Total F&B / Cafe items terjual hari ini
        $totalCafeQtyToday = (float) DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->whereDate('transaksis.waktu_transaksi', $today)
            ->when($activeCabangId, fn($q) => $q->where('transaksis.toko_cabang_id', $activeCabangId))
            ->sum('transaksi_details.qty');

        // 3. Status Room Terpakai dan Sisa Waktunya
        $roomsQuery = Room::query();
        if ($activeCabangId) {
            $roomsQuery->where('toko_cabang_id', $activeCabangId);
        }
        $rooms = $roomsQuery->orderBy('nama')->get();

        $roomStatuses = $rooms->map(function ($room) use ($now, $today) {
            // Ambil transaksi terakhir pada room ini yang terjadi hari ini
            $latestTrx = Transaksi::with(['film', 'user', 'tipePembayaran'])
                ->where('room_id', $room->id)
                ->where('status', 'selesai')
                ->whereDate('waktu_transaksi', $today)
                ->latest('waktu_transaksi')
                ->first();

            $isOccupied = false;
            $waktuMulai = null;
            $waktuSelesai = null;
            $durasiMenit = 0;
            $sisaMenit = 0;
            $sisaDetik = 0;
            $persentase = 0;
            $sisaWaktuTeks = 'Tersedia';

            if ($latestTrx) {
                $mulai = $latestTrx->waktu_mulai_film 
                    ? Carbon::parse($latestTrx->waktu_mulai_film) 
                    : Carbon::parse($latestTrx->waktu_transaksi);
                $durasi = (int) ($latestTrx->durasi_menit ?: ($latestTrx->film?->durasi_menit ?? 120));
                $selesai = $mulai->copy()->addMinutes($durasi);

                // Room terpakai jika waktu sekarang berada di antara waktu mulai dan waktu selesai
                if ($now->greaterThanOrEqualTo($mulai) && $now->lessThan($selesai)) {
                    $isOccupied = true;
                    $waktuMulai = $mulai;
                    $waktuSelesai = $selesai;
                    $durasiMenit = $durasi;

                    $sisaMenit = (int) $now->diffInMinutes($selesai, false);
                    $sisaDetik = (int) $now->diffInSeconds($selesai, false);
                    $berjalanMenit = (int) $mulai->diffInMinutes($now);
                    $persentase = $durasi > 0 ? min(100, max(0, round(($berjalanMenit / $durasi) * 100))) : 0;
                    $sisaWaktuTeks = self::formatSisaWaktu($sisaMenit);
                }
            }

            return (object) [
                'id' => $room->id,
                'nama' => $room->nama,
                'kapasitas' => $room->kapasitas,
                'is_occupied' => $isOccupied,
                'transaksi' => $isOccupied ? $latestTrx : null,
                'film_judul' => $isOccupied ? ($latestTrx->film?->judul ?? 'Film Studio') : null,
                'film_poster' => $isOccupied ? $latestTrx->film?->poster : null,
                'nama_pelanggan' => $isOccupied ? $latestTrx->nama_pelanggan : null,
                'nomor_transaksi' => $isOccupied ? $latestTrx->nomor_transaksi : null,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'durasi_menit' => $durasiMenit,
                'sisa_menit' => max(0, $sisaMenit),
                'sisa_detik' => max(0, $sisaDetik),
                'persentase' => $persentase,
                'sisa_waktu_teks' => $sisaWaktuTeks,
            ];
        });

        $totalRoom = $roomStatuses->count();
        $totalRoomTerpakai = $roomStatuses->where('is_occupied', true)->count();
        $totalRoomTersedia = $totalRoom - $totalRoomTerpakai;

        // 4. Riwayat Pembelian / Transaksi Hari Ini (Today)
        $riwayatPembelianToday = Transaksi::with([
                'film',
                'room',
                'tipePembayaran',
                'details.barang',
                'details.satuanBarang',
                'user'
            ])
            ->whereDate('waktu_transaksi', $today)
            ->when($activeCabangId, fn($q) => $q->where('toko_cabang_id', $activeCabangId))
            ->latest('waktu_transaksi')
            ->get();

        // Response JSON jika diminta melalui AJAX (untuk live countdown & update status)
        if (($request->ajax() || $request->wantsJson()) && $request->get('mode') === 'rooms_status') {
            return response()->json([
                'total_room_terpakai' => $totalRoomTerpakai,
                'total_room_tersedia' => $totalRoomTersedia,
                'rooms' => $roomStatuses,
            ]);
        }

        return view('kasir.index', compact(
            'user',
            'activeShift',
            'tokoCabangs',
            'activeCabangId',
            'currentCabang',
            'namaCabang',
            'totalTransaksiToday',
            'totalPendapatanToday',
            'totalFilmToday',
            'totalCafeQtyToday',
            'totalRoom',
            'totalRoomTerpakai',
            'totalRoomTersedia',
            'roomStatuses',
            'riwayatPembelianToday'
        ));
    }

    /**
     * Format sisa menit ke teks yang mudah dibaca (misal: "1 Jam 15 Menit" atau "45 Menit").
     */
    public static function formatSisaWaktu(int $sisaMenit): string
    {
        if ($sisaMenit <= 0) {
            return 'Waktu Habis';
        }

        $jam = intdiv($sisaMenit, 60);
        $menit = $sisaMenit % 60;

        if ($jam > 0 && $menit > 0) {
            return "{$jam} Jam {$menit} Menit";
        } elseif ($jam > 0) {
            return "{$jam} Jam";
        } else {
            return "{$menit} Menit";
        }
    }
}
