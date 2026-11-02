<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google' => ['client_id' => 'id-uji', 'client_secret' => 'rahasia-uji', 'redirect' => '/auth/google/callback']]);
    }

    public function test_button_is_hidden_until_google_is_configured(): void
    {
        $this->get('/login')->assertSee('Lanjutkan dengan Google');
        $this->get('/register')->assertSee('Lanjutkan dengan Google');

        config(['services.google.client_id' => null]);

        $this->get('/login')->assertDontSee('Lanjutkan dengan Google');
        $this->get('/auth/google')->assertRedirect('/login')->assertSessionHasErrors('identity');
    }

    public function test_redirect_goes_to_google_with_callback_url(): void
    {
        $this->get('/auth/google')
            ->assertRedirectContains('accounts.google.com')
            ->assertRedirectContains(urlencode('/auth/google/callback'));
    }

    public function test_new_google_user_gets_an_account_and_is_logged_in(): void
    {
        $this->fakeGoogle('g-1', 'budi.santoso@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $user = User::query()->where('email', 'budi.santoso@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('g-1', $user->google_id);
        $this->assertSame('budisantoso', $user->username);
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse((bool) $user->is_admin);
    }

    public function test_existing_account_is_linked_by_email_and_keeps_password_and_role(): void
    {
        $user = User::factory()->create(['email' => 'Rafi@Example.test', 'password' => 'lama12345', 'is_admin' => true]);
        $this->fakeGoogle('g-2', 'rafi@example.test');

        $this->get('/auth/google/callback')->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('g-2', $user->fresh()->google_id);
        $this->assertTrue(Hash::check('lama12345', $user->fresh()->password));
        $this->assertTrue((bool) $user->fresh()->is_admin);
    }

    public function test_returning_user_is_found_by_google_id_even_after_changing_email(): void
    {
        $user = User::factory()->create(['email' => 'lama@example.test', 'google_id' => 'g-3']);
        $this->fakeGoogle('g-3', 'baru@example.test');

        $this->get('/auth/google/callback')->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_username_collision_gets_a_suffix(): void
    {
        User::factory()->create(['username' => 'budi', 'email' => 'lain@example.test']);
        $this->fakeGoogle('g-4', 'budi@gmail.com');

        $this->get('/auth/google/callback')->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'budi@gmail.com', 'username' => 'budi_2']);
    }

    public function test_unverified_google_email_is_rejected_and_cannot_take_over_account(): void
    {
        $user = User::factory()->create(['email' => 'korban@example.test']);
        $this->fakeGoogle('g-5', 'korban@example.test', verified: false);

        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHasErrors('identity');

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_account_linked_to_another_google_id_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'rafi@example.test', 'google_id' => 'g-asli']);
        $this->fakeGoogle('g-lain', 'rafi@example.test');

        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHasErrors('identity');

        $this->assertGuest();
        $this->assertSame('g-asli', $user->fresh()->google_id);
    }

    public function test_google_failure_returns_to_login_with_message(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andThrow(new RuntimeException('state tidak valid'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')->assertRedirect('/login')->assertSessionHasErrors('identity');
        $this->assertGuest();
    }

    private function fakeGoogle(string $id, string $email, bool $verified = true): void
    {
        $googleUser = (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => $id, 'email' => $email]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
