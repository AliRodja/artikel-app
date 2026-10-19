<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Invokable: controller dengan satu aksi saja, dipanggil lewat __invoke().
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Setiap statistik memanggil $user->articles() baru, karena query builder bersifat mutable:
        // memakai satu builder untuk published() lalu draft() akan menumpuk kedua kondisi WHERE.
        return view('dashboard', [
            'totalCount'     => $user->articles()->count(),
            'publishedCount' => $user->articles()->published()->count(),
            'draftCount'     => $user->articles()->draft()->count(),
            'latestArticles' => $user->articles()->latest()->take(5)->get(),
        ]);
    }
}
