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
            // password: password (default UserFactory)
        ]);

        Article::factory(20)->for($admin)->create();  // artikel milik akun demo
        Article::factory(10)->create();               // artikel milik user lain
        // artikel tanpa penulis buat ngetes INNER JOIN Soal 2, sengaja published
        // supaya tidak kebuang duluan oleh filter status
        Article::factory(3)->create([
            'user_id' => null,
            'status' => Article::STATUS_PUBLISHED,
        ]);
    }
}
