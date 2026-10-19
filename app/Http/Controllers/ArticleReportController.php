<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Soal 2: daftar artikel Published beserta nama & email penulis memakai INNER JOIN articles × users.
class ArticleReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        // Query Builder dipakai agar JOIN tertulis eksplisit. Hasil SQL-nya kurang lebih:
        //   SELECT ... FROM articles
        //   INNER JOIN users ON articles.user_id = users.id
        //   WHERE articles.status = 1 [AND (articles.title LIKE ? OR users.name LIKE ? OR users.email LIKE ?)]
        //   ORDER BY articles.created_at DESC
        //   LIMIT 10 OFFSET ...
        $articles = DB::table('articles')
            // join() = INNER JOIN: hanya baris yang punya pasangan di kedua tabel.
            // Artikel dengan user_id NULL tidak punya pasangan di users → otomatis tidak ikut.
            ->join('users', 'articles.user_id', '=', 'users.id')
            // Kolom ditulis lengkap dengan nama tabel karena articles & users sama-sama punya id dan created_at
            // (tanpa prefix MySQL error "column is ambiguous"). Alias "as" agar nama di Blade jelas.
            ->select([
                'articles.id',
                'articles.title',
                'articles.slug',
                'articles.status',
                'articles.created_at',
                'users.name as author_name',
                'users.email as author_email',
            ])
            ->where('articles.status', Article::STATUS_PUBLISHED)
            // Bonus search. Closure menghasilkan tanda kurung: status = 1 AND (... OR ... OR ...).
            // Tanpa closure jadinya status = 1 AND title LIKE ? OR name LIKE ? → Draft bisa ikut lolos lewat OR.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('articles.title', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('articles.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('articles.report', compact('articles', 'search'));
    }
}
