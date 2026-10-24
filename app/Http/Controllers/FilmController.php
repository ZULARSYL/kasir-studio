<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\FilmStokCabang;
use App\Models\GenreFilm;
use App\Models\TokoCabang;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FilmController extends Controller
{
    protected ImageCompressionService $compressionService;

    public function __construct(ImageCompressionService $compressionService)
    {
        $this->compressionService = $compressionService;
    }

    /**
     * Tampilkan daftar katalog film dengan filter dan pagination.
     */
    public function index(Request $request)
    {
        $query = Film::with(['genres', 'stokCabangs']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('sutradara', 'like', "%{$search}%")
                  ->orWhere('sinopsis', 'like', "%{$search}%");
            });
        }

        // Filter Genre
        if ($request->filled('genre_id')) {
            $genreId = $request->genre_id;
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genre_films.id', $genreId);
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Rating Usia
        if ($request->filled('usia_rating')) {
            $query->where('usia_rating', $request->usia_rating);
        }

        $films = $query->latest()->paginate(10)->withQueryString();
        $genres = GenreFilm::orderBy('nama_genre', 'asc')->get();
        $cabangs = TokoCabang::orderBy('nama_cabang', 'asc')->get();

        return view('admin.film.index', compact('films', 'genres', 'cabangs'));
    }

    /**
     * Simpan data film baru beserta upload poster dan relasi genre.
     */
    public function store(Request $request)
    {
        // 1. Validasi Ketat Sisi Server (Termasuk proteksi Inspect DevTools pada opsi Select)
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'durasi_menit' => 'required|integer|min:1|max:1440',
            'limit_penggunaan' => 'nullable|integer|min:1|max:100',
            'stok_cabang' => 'nullable|array',
            'stok_cabang.*' => 'nullable|integer|min:0|max:100',
            'harga_per_menit' => 'required|numeric|min:0',
            'tahun_rilis' => 'nullable|integer|min:1900|max:2099',
            'sutradara' => 'nullable|string|max:255',
            'sinopsis' => 'nullable|string|max:3000',
            'status' => [
                'required',
                'string',
                Rule::in(['tersedia', 'tidak_tersedia']),
            ],
            'usia_rating' => [
                'required',
                'string',
                Rule::in(['SU', '13+', '17+', '21+']),
            ],
            'genres' => 'required|array|min:1',
            'genres.*' => [
                'required',
                'integer',
                Rule::exists('genre_films', 'id'),
            ],
            'poster' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:20480', // Maksimal 20MB awal, kemudian autokompresi ke <= 500KB
        ], [
            // Pesan Error Penolakan Khusus jika Select dimanipulasi via DevTools Inspect Element
            'genres.*.exists' => 'Opsi genre film yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'genres.required' => 'Wajib memilih minimal satu genre film.',
            'status.in' => 'Opsi status yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'usia_rating.in' => 'Opsi rating usia yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'poster.mimes' => 'Format berkas poster harus berupa gambar asli (JPG, JPEG, PNG, WEBP).',
            'poster.max' => 'Ukuran berkas cover maksimal 20 MB sebelum proses autokompresi.',
        ]);

        // 2. Kalkulasi harga per menit secara presisi di server
        $durasiMenit = (int) $validated['durasi_menit'];
        $hargaPerMenit = (float) $validated['harga_per_menit'];
        $totalHarga = round($durasiMenit * $hargaPerMenit, 2);

        // 3. Proses Poster Cover Film dengan Validasi Anti-Rename & Autokompres <= 500KB
        $posterPath = null;
        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $posterPath = $this->compressionService->compressAndStore($file, 'films');
        }

        // 4. Simpan Record Film, Stok Terpisah Tiap Outlet, & Sinkronisasi Genre (Many-to-Many)
        DB::transaction(function () use ($validated, $durasiMenit, $hargaPerMenit, $totalHarga, $posterPath, $request) {
            $stokCabangInputs = $request->input('stok_cabang', []);
            $defaultLimit = !empty($validated['limit_penggunaan']) 
                ? (int) $validated['limit_penggunaan'] 
                : (!empty($stokCabangInputs) ? (int) max($stokCabangInputs) : 1);

            $film = Film::create([
                'judul' => $validated['judul'],
                'poster' => $posterPath,
                'sinopsis' => $validated['sinopsis'] ?? null,
                'durasi_menit' => $durasiMenit,
                'limit_penggunaan' => $defaultLimit,
                'harga_per_menit' => $hargaPerMenit,
                'total_harga' => $totalHarga,
                'tahun_rilis' => $validated['tahun_rilis'] ?? null,
                'sutradara' => $validated['sutradara'] ?? null,
                'usia_rating' => $validated['usia_rating'],
                'status' => $validated['status'],
            ]);

            // Filter dan buang duplikat ID genre jika ada
            $uniqueGenreIds = array_values(array_unique($validated['genres']));
            $film->genres()->sync($uniqueGenreIds);

            // Simpan stok terpisah untuk masing-masing outlet
            $cabangs = TokoCabang::all();
            foreach ($cabangs as $cabang) {
                $stok = isset($stokCabangInputs[$cabang->id]) 
                    ? (int) $stokCabangInputs[$cabang->id] 
                    : $defaultLimit;

                FilmStokCabang::updateOrCreate(
                    ['film_id' => $film->id, 'toko_cabang_id' => $cabang->id],
                    ['stok' => $stok]
                );
            }
        });

        return redirect()->route('admin.film.index')
            ->with('success', 'Film baru berhasil ditambahkan! Cover telah diverifikasi dan diautokompres.');
    }

    /**
     * Perbarui data film, cover, dan genre.
     */
    public function update(Request $request, Film $film)
    {
        // 1. Validasi Ketat Sisi Server
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'durasi_menit' => 'required|integer|min:1|max:1440',
            'limit_penggunaan' => 'nullable|integer|min:1|max:100',
            'stok_cabang' => 'nullable|array',
            'stok_cabang.*' => 'nullable|integer|min:0|max:100',
            'harga_per_menit' => 'required|numeric|min:0',
            'tahun_rilis' => 'nullable|integer|min:1900|max:2099',
            'sutradara' => 'nullable|string|max:255',
            'sinopsis' => 'nullable|string|max:3000',
            'status' => [
                'required',
                'string',
                Rule::in(['tersedia', 'tidak_tersedia']),
            ],
            'usia_rating' => [
                'required',
                'string',
                Rule::in(['SU', '13+', '17+', '21+']),
            ],
            'genres' => 'required|array|min:1',
            'genres.*' => [
                'required',
                'integer',
                Rule::exists('genre_films', 'id'),
            ],
            'poster' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:20480',
        ], [
            'genres.*.exists' => 'Opsi genre film yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'genres.required' => 'Wajib memilih minimal satu genre film.',
            'status.in' => 'Opsi status yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'usia_rating.in' => 'Opsi rating usia yang Anda pilih tidak valid atau telah dimodifikasi melalui inspect devtools browser!',
            'poster.mimes' => 'Format berkas poster harus berupa gambar asli (JPG, JPEG, PNG, WEBP).',
        ]);

        $durasiMenit = (int) $validated['durasi_menit'];
        $hargaPerMenit = (float) $validated['harga_per_menit'];
        $totalHarga = round($durasiMenit * $hargaPerMenit, 2);

        $posterPath = $film->poster;

        // Jika user mengunggah cover poster baru
        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $newPosterPath = $this->compressionService->compressAndStore($file, 'films');

            // Hapus file cover lama dari storage jika ada
            if ($film->poster && Storage::disk('public')->exists($film->poster)) {
                Storage::disk('public')->delete($film->poster);
            }

            $posterPath = $newPosterPath;
        }

        DB::transaction(function () use ($film, $validated, $durasiMenit, $hargaPerMenit, $totalHarga, $posterPath, $request) {
            $stokCabangInputs = $request->input('stok_cabang', []);
            $defaultLimit = !empty($validated['limit_penggunaan']) 
                ? (int) $validated['limit_penggunaan'] 
                : (!empty($stokCabangInputs) ? (int) max($stokCabangInputs) : ($film->limit_penggunaan ?? 1));

            $film->update([
                'judul' => $validated['judul'],
                'poster' => $posterPath,
                'sinopsis' => $validated['sinopsis'] ?? null,
                'durasi_menit' => $durasiMenit,
                'limit_penggunaan' => $defaultLimit,
                'harga_per_menit' => $hargaPerMenit,
                'total_harga' => $totalHarga,
                'tahun_rilis' => $validated['tahun_rilis'] ?? null,
                'sutradara' => $validated['sutradara'] ?? null,
                'usia_rating' => $validated['usia_rating'],
                'status' => $validated['status'],
            ]);

            $uniqueGenreIds = array_values(array_unique($validated['genres']));
            $film->genres()->sync($uniqueGenreIds);

            // Simpan / perbarui stok film terpisah untuk setiap outlet (toko_cabang)
            $cabangs = TokoCabang::all();
            foreach ($cabangs as $cabang) {
                if (isset($stokCabangInputs[$cabang->id])) {
                    FilmStokCabang::updateOrCreate(
                        ['film_id' => $film->id, 'toko_cabang_id' => $cabang->id],
                        ['stok' => (int) $stokCabangInputs[$cabang->id]]
                    );
                } elseif (!empty($validated['limit_penggunaan'])) {
                    FilmStokCabang::updateOrCreate(
                        ['film_id' => $film->id, 'toko_cabang_id' => $cabang->id],
                        ['stok' => (int) $validated['limit_penggunaan']]
                    );
                }
            }
        });

        return redirect()->route('admin.film.index')
            ->with('success', 'Data film dan genre berhasil diperbarui!');
    }

    /**
     * Hapus film beserta berkas poster terkait.
     */
    public function destroy(Film $film)
    {
        DB::transaction(function () use ($film) {
            if ($film->poster && Storage::disk('public')->exists($film->poster)) {
                Storage::disk('public')->delete($film->poster);
            }

            $film->genres()->detach();
            $film->delete();
        });

        return redirect()->route('admin.film.index')
            ->with('success', 'Film berhasil dihapus dari sistem!');
    }
}
