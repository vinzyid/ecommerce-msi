<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_and_login_pages(): void
    {
        $this->get('/register')->assertOk()->assertSee('Daftar');
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_catalog_is_public(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_registration_requires_valid_data_with_indonesian_messages(): void
    {
        $response = $this->from('/register')->post('/register', []);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'username' => 'Username wajib diisi.',
                'email' => 'Email wajib diisi.',
                'password' => 'Password wajib diisi.',
            ]);
    }

    public function test_user_can_register_and_is_logged_in_automatically(): void
    {
        $response = $this->post('/register', [
            'username' => 'vinzy_store',
            'email' => 'vinzy@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $user = User::where('email', 'vinzy@example.com')->firstOrFail();
        $this->assertSame('vinzy_store', $user->username);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertNotSame('rahasia123', $user->password);
    }

    public function test_username_and_email_must_be_unique(): void
    {
        User::factory()->create([
            'username' => 'member_satu',
            'email' => 'member@example.com',
        ]);

        $response = $this->from('/register')->post('/register', [
            'username' => 'member_satu',
            'email' => 'member@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/register')
            ->assertSessionHasErrors([
                'username' => 'Username sudah terdaftar.',
                'email' => 'Email sudah terdaftar.',
            ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_login_with_email_or_username(): void
    {
        $user = User::factory()->create([
            'username' => 'member_satu',
            'email' => 'member@example.com',
            'password' => Hash::make('rahasia'),
        ]);

        $this->post('/login', [
            'identity' => $user->email,
            'password' => 'rahasia',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');

        $this->post('/login', [
            'identity' => $user->username,
            'password' => 'rahasia',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_uses_a_generic_error_message(): void
    {
        User::factory()->create([
            'username' => 'member_satu',
            'password' => Hash::make('rahasia'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'identity' => 'member_satu',
            'password' => 'password-salah',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors([
                'identity' => 'Email/username atau password salah.',
            ]);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_attempts(): void
    {
        User::factory()->create([
            'username' => 'member_satu',
            'password' => Hash::make('rahasia123'),
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'identity' => 'member_satu',
                'password' => 'password-salah',
            ])->assertRedirect('/login');
        }

        $this->from('/login')->post('/login', [
            'identity' => 'member_satu',
            'password' => 'rahasia123',
        ])->assertStatus(429);
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/register', [
                'username' => 'spammer_'.$attempt,
                'email' => "spammer{$attempt}@example.com",
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ]);
        }

        $this->post('/register', [
            'username' => 'spammer_terakhir',
            'email' => 'spammer-terakhir@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(429);
    }
}
