<?php

namespace App\Providers;

use App\Models\Category;
use App\Support\CatalogCache;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $view->with('footerCategories', CatalogCache::remember('footer.categories', fn () => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower((string) $request->input('identity')).'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        ResetPassword::toMailUsing(fn (object $user, string $token) => (new MailMessage)
            ->subject('Reset password '.config('app.name'))
            ->line('Kami menerima permintaan reset password untuk akunmu.')
            ->action('Buat password baru', route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->line('Link ini berlaku 60 menit. Abaikan email ini bila kamu tidak memintanya.'));

        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
