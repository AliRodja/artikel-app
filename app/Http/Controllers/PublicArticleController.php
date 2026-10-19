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
            // eager load penulis, hindari N+1
            ->with('user:id,name')
            // dibungkus closure supaya draft tidak ikut lolos lewat orWhere
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.index', compact('articles', 'search'));
    }

    public function show(Article $article): View
    {
        // draft tidak boleh dibuka publik
        abort_unless($article->isPublished(), 404);

        $article->load('user:id,name');

        return view('public.show', compact('article'));
    }
}
