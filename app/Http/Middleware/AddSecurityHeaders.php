<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Sumber yang diizinkan memuat konten di halaman. Script & style inline masih
     * dipakai beberapa halaman (onclick, <script> kecil), jadi 'unsafe-inline' tetap
     * ada; yang dibatasi adalah ASAL berkas: hanya domain sendiri + Google Fonts.
     */
    private const CSP = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline'",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com data:",
        "img-src 'self' data: blob: https:",
        "connect-src 'self'",
        "media-src 'self' data: blob:",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
        'upgrade-insecure-requests',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Saat `npm run dev`, aset dimuat dari server Vite (localhost:5173); CSP hanya
        // dipasang untuk aset hasil build supaya development tidak terganggu.
        if (! is_file(public_path('hot'))) {
            $response->headers->set('Content-Security-Policy', implode('; ', self::CSP));
        }

        return $response;
    }
}
