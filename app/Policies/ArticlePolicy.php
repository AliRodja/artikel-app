<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

// Policy: aturan "siapa boleh melakukan apa" terhadap satu model.
// Laravel menemukannya otomatis karena namanya mengikuti pola App\Models\Article → App\Policies\ArticlePolicy.
// Dipanggil di controller dengan Gate::authorize('update', $article); jika false → 403 Forbidden.
class ArticlePolicy
{
    // Melihat detail artikel di area dashboard (termasuk Draft) hanya untuk pemiliknya.
    // Pengunjung umum melihat artikel Published lewat PublicArticleController, bukan lewat sini.
    public function view(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }
}
