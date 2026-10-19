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
            'name'  => 'Admin Demo',
            'email' => 'admin@example.com',
            // password default dari UserFactory: "password"
        ]);

        Article::factory(20)->for($admin)->create();  // artikel milik akun demo
        Article::factory(10)->create();               // artikel milik user lain
        Article::factory(3)->create(['user_id' => null]); // artikel tanpa penulis → uji INNER JOIN Soal 2
    }
}