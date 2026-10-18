<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\GenreFilm;
use Illuminate\Http\Request;

class GenreFilmController extends Controller
{
    public function index(Request $request)
    {
        $query = GenreFilm::query();

        if ($request->filled('search')) {
            $query->where('nama_genre', 'like', "%{$request->search}%");
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('admin.master.genre-film.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_genre' => 'required|string|max:255|unique:genre_films,nama_genre',
        ]);

        GenreFilm::create($validated);

        return redirect()->route('admin.master.genre-film.index')
            ->with('success', 'Genre film baru berhasil ditambahkan!');
    }

    public function update(Request $request, GenreFilm $genreFilm)
    {
        $validated = $request->validate([
            'nama_genre' => 'required|string|max:255|unique:genre_films,nama_genre,' . $genreFilm->id,
        ]);

        $genreFilm->update($validated);

        return redirect()->route('admin.master.genre-film.index')
            ->with('success', 'Genre film berhasil diperbarui!');
    }

    public function destroy(GenreFilm $genreFilm)
    {
        $genreFilm->delete();

        return redirect()->route('admin.master.genre-film.index')
            ->with('success', 'Genre film berhasil dihapus!');
    }
}
