<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
            // password default dari UserFactory: "password"
        ]);

        Article::factory(20)->for($admin)->create();  // artikel milik akun demo
        Article::factory(10)->create();               // artikel milik user lain
        // Artikel tanpa penulis → uji INNER JOIN Soal 2. Sengaja Published: kalau Draft, artikel ini sudah
        // tersaring oleh WHERE status = 1 sehingga tidak membuktikan bahwa JOIN-lah yang membuangnya.
        Article::factory(3)->create([
            'user_id' => null,
            'status' => Article::STATUS_PUBLISHED,
        ]);
    }
}
