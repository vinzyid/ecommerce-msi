<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleLoginController extends Controller
{
    private const FAILED = 'Gagal masuk dengan Google. Coba lagi atau masuk dengan password.';

    public function redirect(): SymfonyRedirectResponse
    {
        if (blank(config('services.google.client_id'))) {
            return redirect()->route('login')->withErrors(['identity' => 'Masuk dengan Google belum diaktifkan.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors(['identity' => self::FAILED]);
        }

        // Email hanya dipakai untuk mencocokkan akun bila Google sudah memverifikasinya.
        if (blank($google->getEmail()) || ! data_get($google->user, 'email_verified')) {
            return redirect()->route('login')->withErrors(['identity' => 'Email Google kamu belum terverifikasi.']);
        }

        $user = $this->resolveUser($google->getId(), $google->getEmail());

        if (! $user) {
            return redirect()->route('login')->withErrors(['identity' => self::FAILED]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Cari akun lewat ID Google, lalu lewat email; buat akun baru bila belum ada.
     */
    private function resolveUser(string $googleId, string $email): ?User
    {
        $user = User::query()->where('google_id', $googleId)->first()
            ?? User::query()->whereRaw('lower(email) = ?', [Str::lower($email)])->first();

        if (! $user) {
            $user = new User([
                'username' => $this->uniqueUsername($email),
                'email' => $email,
                // Password acak; pengguna bisa membuat password sendiri lewat "Lupa password".
                'password' => Str::random(40),
            ]);
        } elseif ($user->google_id !== null && $user->google_id !== $googleId) {
            return null;
        }

        $user->forceFill([
            'google_id' => $googleId,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return $user;
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::limit(Str::slug(Str::before($email, '@'), '_'), 45, '');
        $base = mb_strlen($base) >= 3 ? $base : 'user_'.$base;
        $username = $base;
        $number = 2;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.'_'.$number++;
        }

        return $username;
    }
}
