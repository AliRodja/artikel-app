<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $articles = Article::query()
            ->published()
            // Eager loading: penulis semua artikel diambil dalam 1 query tambahan (mencegah N+1 query).
            ->with('user:id,name')
            // Kondisi search dibungkus closure → WHERE status = 1 AND (title LIKE ? OR content LIKE ?).
            // Tanpa closure, orWhere akan ikut menampilkan artikel Draft yang cocok dengan kata kunci.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString(); // kata kunci search tetap terbawa saat pindah halaman

        return view('public.index', compact('articles', 'search'));
    }

    // Route model binding: {article} dicari lewat kolom slug (getRouteKeyName di model).
    public function show(Article $article): View
    {
        // Draft tidak boleh diakses publik walaupun slug-nya diketahui.
        abort_unless($article->isPublished(), 404);

        $article->load('user:id,name');

        return view('public.show', compact('article'));
    }
}
