<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

// Resource controller: 7 aksi standar (index, create, store, show, edit, update, destroy)
// yang dipetakan otomatis oleh Route::resource('articles', ...) di routes/web.php.
class ArticleController extends Controller
{
    // Daftar artikel MILIK user yang login, dengan search judul + filter status.
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status'); // '' / null = semua, '0' = Draft, '1' = Published

        $articles = $request->user()->articles()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            // in_array: hanya terima '0' atau '1' agar nilai aneh dari URL diabaikan.
            ->when(in_array($status, ['0', '1'], true), fn ($query) => $query->where('status', (int) $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('articles.index', compact('articles', 'search', 'status'));
    }

    public function create(): View
    {
        return view('articles.create');
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        // safe()->except(): ambil data yang lolos validasi, kecuali field yang diolah manual.
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            // Simpan ke storage/app/public/articles dengan nama acak; yang disimpan di DB hanya path-nya.
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        // Lewat relasi: user_id otomatis diisi id user yang login, tidak pernah dari input form.
        $article = $request->user()->articles()->create($data);

        return redirect()->route('articles.show', $article)->with('success', 'Artikel berhasil dibuat.');
    }

    // Route model binding: {article} di URL (slug) otomatis diubah jadi objek Article, 404 jika tidak ada.
    public function show(Article $article): View
    {
        Gate::authorize('view', $article); // bukan pemilik → 403

        return view('articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        Gate::authorize('update', $article);

        return view('articles.edit', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);

        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            // Ganti gambar: hapus file lama dulu agar tidak menumpuk file yatim di storage.
            $this->deleteImage($article);
            $data['image'] = $request->file('image')->store('articles', 'public');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($article);
            $data['image'] = null;
        }

        $article->update($data);

        return redirect()->route('articles.show', $article)->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $this->deleteImage($article);
        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    // Dipakai di update() & destroy() agar logika hapus file tidak ditulis berulang.
    private function deleteImage(Article $article): void
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
    }
}
