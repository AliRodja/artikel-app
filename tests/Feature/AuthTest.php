<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Feature test: mensimulasikan request HTTP sungguhan (GET/POST) ke aplikasi, lalu memeriksa respons & database.
// RefreshDatabase: menjalankan migration di database test (SQLite in-memory, lihat phpunit.xml) dan
// membungkus tiap test dalam transaksi yang di-rollback → setiap test mulai dari database bersih.
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Register ----------

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        // Password tidak disimpan sebagai teks asli, melainkan hash yang cocok dengan Hash::check().
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'budi@example.com', // sudah terdaftar
            'password' => 'rahasia123',
            'password_confirmation' => 'beda12345',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    // ---------- Login & Logout ----------

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(); // password default factory: "password"

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'salah-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_attempts(): void
    {
        $user = User::factory()->create();

        // Middleware throttle:5,1 → maksimal 5 request per menit; request ke-6 ditolak 429 Too Many Requests.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])->assertStatus(429);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    // ---------- Lupa & Reset Password ----------

    public function test_reset_link_is_sent_to_registered_email(): void
    {
        // Notification::fake(): notifikasi tidak benar-benar dikirim, hanya dicatat agar bisa diperiksa.
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_user_can_reset_password_and_token_cannot_be_reused(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        // Ambil token asli dari notifikasi (di database yang tersimpan hanya hash-nya).
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $payload = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ];

        $this->post('/reset-password', $payload)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('passwordbaru123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        // Token yang sama dipakai lagi → ditolak.
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token' => 'token-palsu',
            'email' => $user->email,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password)); // password lama tidak berubah
    }

    // ---------- Middleware auth & guest ----------

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/dashboard/articles')->assertRedirect(route('login'));
        $this->get('/dashboard/laporan-artikel')->assertRedirect(route('login'));
    }

    public function test_logged_in_user_cannot_open_login_or_register_page(): void
    {
        $user = User::factory()->create();

        // Middleware guest mengarahkan user yang sudah login ke dashboard.
        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/register')->assertRedirect(route('dashboard'));
    }
}
