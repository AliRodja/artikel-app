<?php

namespace App\Http\Controllers;

class ArticleReportController extends Controller
{
    // Kerangka sementara — query INNER JOIN Soal 2 diisi pada langkah 4.
    public function __invoke()
    {
        abort(501, 'Laporan Artikel belum dibuat.');
    }
}
