<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Film;
use App\Models\MenuCafe;
use App\Models\Pengeluaran;
use App\Models\TokoCabang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard Administrator.
     */
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // ---------------------------------------------------------------------
        // 1. CARD 1: JUMLAH PENJUALAN FILM PER BULAN
        // ---------------------------------------------------------------------
        $filmSalesThisMonthQuery = Transaksi::where('status', 'selesai')
            ->whereBetween('waktu_transaksi', [$startOfMonth, $endOfMonth])
            ->whereNotNull('film_id');

        $jumlahPenjualanFilm = (int) (clone $filmSalesThisMonthQuery)->count();
        $totalPenontonFilm = (int) (clone $filmSalesThisMonthQuery)->sum('jumlah_penonton');
        $nominalPenjualanFilm = (float) (clone $filmSalesThisMonthQuery)->sum('harga_film')
            ?: (float) (clone $filmSalesThisMonthQuery)->sum('subtotal_film_room');

        $filmSalesLastMonth = (int) Transaksi::where('status', 'selesai')
            ->whereBetween('waktu_transaksi', [$startOfLastMonth, $endOfLastMonth])
            ->whereNotNull('film_id')
            ->count();

        $filmSalesGrowth = $filmSalesLastMonth > 0
            ? round((($jumlahPenjualanFilm - $filmSalesLastMonth) / $filmSalesLastMonth) * 100, 1)
            : ($jumlahPenjualanFilm > 0 ? 100 : 0);

        // ---------------------------------------------------------------------
        // 2. CARD 2: JUMLAH BARANG YANG ADA DI CAFE
        // ---------------------------------------------------------------------
        $jumlahBarangCafe = Barang::count();
        $jumlahKategoriCafe = MenuCafe::count();

        $totalStokMasuk = (float) DB::table('faktur_barang_details')->sum('qty');
        $totalStokKeluar = (float) DB::table('transaksi_details')->sum('qty');
        $totalStokFisik = max(0, $totalStokMasuk - $totalStokKeluar);

        // ---------------------------------------------------------------------
        // 3. CARD 3: LABA PERBULAN (BULAN BERJALAN)
        // Laba Bersih = Pendapatan Usaha - HPP Cafe - Beban Pengeluaran Operasional
        // ---------------------------------------------------------------------
        $labaBulanIni = $this->calculateLabaBersih($startOfMonth, $endOfMonth);
        $labaBulanLalu = $this->calculateLabaBersih($startOfLastMonth, $endOfLastMonth);

        $labaGrowth = $labaBulanLalu > 0
            ? round((($labaBulanIni - $labaBulanLalu) / $labaBulanLalu) * 100, 1)
            : ($labaBulanIni > 0 ? 100 : 0);

        // ---------------------------------------------------------------------
        // 4. CARD 4: PENGELUARAN PERBULAN
        // ---------------------------------------------------------------------
        $pengeluaranBulanIni = (float) Pengeluaran::whereBetween('tanggal', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])->sum('nominal');

        $pengeluaranBulanIniCount = (int) Pengeluaran::whereBetween('tanggal', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])->count();

        $pengeluaranBulanLalu = (float) Pengeluaran::whereBetween('tanggal', [
            $startOfLastMonth->toDateString(),
            $endOfLastMonth->toDateString(),
        ])->sum('nominal');

        $pengeluaranGrowth = $pengeluaranBulanLalu > 0
            ? round((($pengeluaranBulanIni - $pengeluaranBulanLalu) / $pengeluaranBulanLalu) * 100, 1)
            : ($pengeluaranBulanIni > 0 ? 100 : 0);

        // ---------------------------------------------------------------------
        // 5. TOKO CABANG: OUTLET A & OUTLET B
        // ---------------------------------------------------------------------
        $cabangs = TokoCabang::orderBy('id', 'asc')->get();
        $cabangA = $cabangs->get(0);
        $cabangB = $cabangs->get(1) ?? $cabangA;

        $namaOutletA = $cabangA ? $cabangA->nama_cabang : 'Outlet A (Sudirman)';
        $namaOutletB = ($cabangB && $cabangs->count() > 1) ? $cabangB->nama_cabang : 'Outlet B (Panam)';

        // ---------------------------------------------------------------------
        // 6. SALES STATISTICS AREA CHART: PERHARI, PERMINGGU, PERBULAN
        // ---------------------------------------------------------------------
        $chartData = $this->generateSalesChartData($cabangA?->id, $cabangB?->id);

        // ---------------------------------------------------------------------
        // 7. SECTION TOP 5 FILM TERLARIS: OUTLET A (KIRI) & OUTLET B (KANAN)
        // ---------------------------------------------------------------------
        $topFilmsOutletA = $this->getTop5FilmsByCabang($cabangA?->id);
        $topFilmsOutletB = $this->getTop5FilmsByCabang($cabangB?->id);

        return view('admin.index', compact(
            'jumlahPenjualanFilm',
            'totalPenontonFilm',
            'nominalPenjualanFilm',
            'filmSalesGrowth',
            'jumlahBarangCafe',
            'jumlahKategoriCafe',
            'totalStokFisik',
            'labaBulanIni',
            'labaGrowth',
            'pengeluaranBulanIni',
            'pengeluaranBulanIniCount',
            'pengeluaranGrowth',
            'cabangA',
            'cabangB',
            'namaOutletA',
            'namaOutletB',
            'chartData',
            'topFilmsOutletA',
            'topFilmsOutletB'
        ));
    }

    /**
     * Hitung total Laba Bersih (Net Profit) dalam rentang tanggal tertentu.
     */
    protected function calculateLabaBersih(Carbon $start, Carbon $end): float
    {
        // 1. Pendapatan Transaksi Selesai
        $transaksiQuery = Transaksi::where('status', 'selesai')
            ->whereBetween('waktu_transaksi', [$start, $end]);

        $pendapatanFilmRoom = (float) (clone $transaksiQuery)->sum('subtotal_film_room');
        $pendapatanCafe = (float) (clone $transaksiQuery)->sum('subtotal_cafe');
        $diskon = (float) (clone $transaksiQuery)->sum('diskon');
        $totalPendapatan = ($pendapatanFilmRoom + $pendapatanCafe) - $diskon;

        // 2. HPP Makanan & Minuman Cafe
        $trxIds = (clone $transaksiQuery)->pluck('id');
        $hppCafe = 0;

        if ($trxIds->isNotEmpty()) {
            $transaksiDetails = TransaksiDetail::whereIn('transaksi_id', $trxIds)->get();
            $barangIds = $transaksiDetails->pluck('barang_id')->unique();

            $hargaAwalMap = [];
            if ($barangIds->isNotEmpty()) {
                $fakturs = DB::table('faktur_barang_details')
                    ->join('faktur_barangs', 'faktur_barang_details.faktur_barang_id', '=', 'faktur_barangs.id')
                    ->whereIn('faktur_barang_details.barang_id', $barangIds)
                    ->select('faktur_barang_details.barang_id', 'faktur_barang_details.harga_awal')
                    ->orderBy('faktur_barangs.tanggal_faktur', 'desc')
                    ->orderBy('faktur_barang_details.id', 'desc')
                    ->get();

                foreach ($fakturs as $fkt) {
                    if (!isset($hargaAwalMap[$fkt->barang_id])) {
                        $hargaAwalMap[$fkt->barang_id] = (float) $fkt->harga_awal;
                    }
                }
            }

            foreach ($transaksiDetails as $td) {
                $hppCafe += ((float) $td->qty * ($hargaAwalMap[$td->barang_id] ?? 0));
            }
        }

        // 3. Beban Pengeluaran Operasional
        $bebanPengeluaran = (float) Pengeluaran::whereBetween('tanggal', [
            $start->toDateString(),
            $end->toDateString(),
        ])->sum('nominal');

        return $totalPendapatan - $hppCafe - $bebanPengeluaran;
    }

    /**
     * Generate data series komparasi penjualan antara Outlet A dan Outlet B
     * untuk periode: Perhari, Perminggu, dan Perbulan.
     */
    protected function generateSalesChartData(?int $cabangAId, ?int $cabangBId): array
    {
        $now = Carbon::now();

        // ---------------------------------------------------------------------
        // A. PERHARI (7 Hari Terakhir: H-6 s/d Hari Ini)
        // ---------------------------------------------------------------------
        $dailyLabels = [];
        $dailyOutletA = [];
        $dailyOutletB = [];
        $dailyTotal = 0;

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateStr = $date->toDateString();
            $label = $date->locale('id')->isoFormat('D MMM');

            $dailyLabels[] = $label;

            $salesA = (float) Transaksi::where('status', 'selesai')
                ->when($cabangAId, fn($q) => $q->where('toko_cabang_id', $cabangAId))
                ->whereDate('waktu_transaksi', $dateStr)
                ->sum('total_bayar');

            $salesB = (float) Transaksi::where('status', 'selesai')
                ->when($cabangBId && $cabangBId !== $cabangAId, fn($q) => $q->where('toko_cabang_id', $cabangBId))
                ->whereDate('waktu_transaksi', $dateStr)
                ->sum('total_bayar');

            $dailyOutletA[] = $salesA;
            $dailyOutletB[] = $salesB;
            $dailyTotal += ($salesA + $salesB);
        }

        // ---------------------------------------------------------------------
        // B. PERMINGGU (4 Minggu Terakhir)
        // ---------------------------------------------------------------------
        $weeklyLabels = [];
        $weeklyOutletA = [];
        $weeklyOutletB = [];
        $weeklyTotal = 0;

        for ($w = 3; $w >= 0; $w--) {
            $startWeek = $now->copy()->subWeeks($w)->startOfWeek();
            $endWeek = $now->copy()->subWeeks($w)->endOfWeek();

            $label = 'Minggu ' . (4 - $w) . ' (' . $startWeek->isoFormat('D MMM') . ')';
            $weeklyLabels[] = $label;

            $salesA = (float) Transaksi::where('status', 'selesai')
                ->when($cabangAId, fn($q) => $q->where('toko_cabang_id', $cabangAId))
                ->whereBetween('waktu_transaksi', [$startWeek, $endWeek])
                ->sum('total_bayar');

            $salesB = (float) Transaksi::where('status', 'selesai')
                ->when($cabangBId && $cabangBId !== $cabangAId, fn($q) => $q->where('toko_cabang_id', $cabangBId))
                ->whereBetween('waktu_transaksi', [$startWeek, $endWeek])
                ->sum('total_bayar');

            $weeklyOutletA[] = $salesA;
            $weeklyOutletB[] = $salesB;
            $weeklyTotal += ($salesA + $salesB);
        }

        // ---------------------------------------------------------------------
        // C. PERBULAN (12 Bulan di Tahun Ini)
        // ---------------------------------------------------------------------
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyOutletA = [];
        $monthlyOutletB = [];
        $monthlyTotal = 0;
        $currentYear = $now->year;

        for ($m = 1; $m <= 12; $m++) {
            $salesA = (float) Transaksi::where('status', 'selesai')
                ->when($cabangAId, fn($q) => $q->where('toko_cabang_id', $cabangAId))
                ->whereYear('waktu_transaksi', $currentYear)
                ->whereMonth('waktu_transaksi', $m)
                ->sum('total_bayar');

            $salesB = (float) Transaksi::where('status', 'selesai')
                ->when($cabangBId && $cabangBId !== $cabangAId, fn($q) => $q->where('toko_cabang_id', $cabangBId))
                ->whereYear('waktu_transaksi', $currentYear)
                ->whereMonth('waktu_transaksi', $m)
                ->sum('total_bayar');

            $monthlyOutletA[] = $salesA;
            $monthlyOutletB[] = $salesB;
            $monthlyTotal += ($salesA + $salesB);
        }

        return [
            'perhari' => [
                'labels' => $dailyLabels,
                'outlet_a' => $dailyOutletA,
                'outlet_b' => $dailyOutletB,
                'total_formatted' => 'Rp ' . number_format($dailyTotal, 0, ',', '.'),
                'total_a_formatted' => 'Rp ' . number_format(array_sum($dailyOutletA), 0, ',', '.'),
                'total_b_formatted' => 'Rp ' . number_format(array_sum($dailyOutletB), 0, ',', '.'),
                'period_text' => 'Total 7 Hari Terakhir',
            ],
            'perminggu' => [
                'labels' => $weeklyLabels,
                'outlet_a' => $weeklyOutletA,
                'outlet_b' => $weeklyOutletB,
                'total_formatted' => 'Rp ' . number_format($weeklyTotal, 0, ',', '.'),
                'total_a_formatted' => 'Rp ' . number_format(array_sum($weeklyOutletA), 0, ',', '.'),
                'total_b_formatted' => 'Rp ' . number_format(array_sum($weeklyOutletB), 0, ',', '.'),
                'period_text' => 'Total 4 Minggu Terakhir',
            ],
            'perbulan' => [
                'labels' => $monthlyLabels,
                'outlet_a' => $monthlyOutletA,
                'outlet_b' => $monthlyOutletB,
                'total_formatted' => 'Rp ' . number_format($monthlyTotal, 0, ',', '.'),
                'total_a_formatted' => 'Rp ' . number_format(array_sum($monthlyOutletA), 0, ',', '.'),
                'total_b_formatted' => 'Rp ' . number_format(array_sum($monthlyOutletB), 0, ',', '.'),
                'period_text' => 'Total Tahun ' . $currentYear,
            ],
        ];
    }

    /**
     * Ambil data Top 5 Film Terlaris untuk cabang tertentu.
     * Mengembalikan list 5 film dengan ranking, total tayang, penonton, dan omset.
     */
    protected function getTop5FilmsByCabang(?int $cabangId): array
    {
        $topList = [];

        // 1. Ambil transaksi film selesai per cabang
        $trxFilms = Transaksi::where('status', 'selesai')
            ->when($cabangId, fn($q) => $q->where('toko_cabang_id', $cabangId))
            ->whereNotNull('film_id')
            ->select(
                'film_id',
                DB::raw('COUNT(*) as total_tayang'),
                DB::raw('SUM(COALESCE(jumlah_penonton, 1)) as total_penonton'),
                DB::raw('SUM(COALESCE(harga_film, subtotal_film_room, 0)) as total_omset')
            )
            ->groupBy('film_id')
            ->orderByDesc('total_tayang')
            ->orderByDesc('total_omset')
            ->limit(5)
            ->with(['film.genres'])
            ->get();

        $maxTayang = $trxFilms->max('total_tayang') ?: 1;
        $usedFilmIds = [];

        $rank = 1;
        foreach ($trxFilms as $item) {
            $film = $item->film;
            if (!$film) {
                continue;
            }

            $usedFilmIds[] = $film->id;
            $genreNames = $film->genres->pluck('nama')->implode(', ') ?: 'Umum';

            $topList[] = [
                'rank' => $rank++,
                'film_id' => $film->id,
                'judul' => $film->judul,
                'poster_url' => $film->poster_url,
                'durasi' => $film->durasi_menit . ' Menit',
                'usia_rating' => $film->usia_rating ?: 'SU',
                'genres' => $genreNames,
                'total_tayang' => (int) $item->total_tayang,
                'total_penonton' => (int) $item->total_penonton,
                'total_omset' => (float) $item->total_omset,
                'total_omset_formatted' => 'Rp ' . number_format($item->total_omset, 0, ',', '.'),
                'popularity_percent' => min(100, round(($item->total_tayang / $maxTayang) * 100)),
            ];
        }

        // 2. Jika transaksi kurang dari 5, lengkapi dari data master Film
        if (count($topList) < 5) {
            $remainingCount = 5 - count($topList);
            $otherFilms = Film::whereNotIn('id', $usedFilmIds)
                ->with('genres')
                ->orderBy('id', 'asc')
                ->limit($remainingCount)
                ->get();

            foreach ($otherFilms as $film) {
                $genreNames = $film->genres->pluck('nama')->implode(', ') ?: 'Umum';
                $topList[] = [
                    'rank' => $rank++,
                    'film_id' => $film->id,
                    'judul' => $film->judul,
                    'poster_url' => $film->poster_url,
                    'durasi' => $film->durasi_menit . ' Menit',
                    'usia_rating' => $film->usia_rating ?: 'SU',
                    'genres' => $genreNames,
                    'total_tayang' => 0,
                    'total_penonton' => 0,
                    'total_omset' => 0,
                    'total_omset_formatted' => 'Rp 0',
                    'popularity_percent' => 5, // minimal indicator bar
                ];
            }
        }

        return $topList;
    }
}
