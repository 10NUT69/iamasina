<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackVisit
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 0. Nu logăm erorile de tip 500
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 500) {
            return $response;
        }

        /*
         |=======================================================
         |   EXCLUDERI CRITICE
         |=======================================================
         */

        // 1. EXCLUDERE COMPLETĂ ADMIN
        // Dacă utilizatorul e logat ȘI este admin, nu contorizăm nimic, indiferent pe ce pagină e.
        if (Auth::check() && Auth::user()->is_admin) {
            return $response;
        }

        // 2. Nu logăm rutele de admin (pentru siguranță, în caz că nu e logat dar încearcă URL-uri)
        if ($request->is('admin') || $request->is('admin/*')) {
            return $response;
        }

        // 3. Nu logăm AJAX
        if ($request->ajax()) {
            return $response;
        }

        // 4. Nu logăm asset-uri sau fișiere statice
        if (
            $request->is('images/*') ||
            $request->is('storage/*') ||
            $request->is('css/*') ||
            $request->is('js/*') ||
            $request->is('vendor/*') ||
            $request->is('livewire/*')  // Excludem și request-urile Livewire dacă există
        ) {
            return $response;
        }

        // 5. Nu logăm boți cunoscuți
        $ua = $request->userAgent() ?? '';
        if ($this->isBot($ua)) {
            return $response;
        }

        /*
         |=======================================================
         |   COLECTARE DATE
         |=======================================================
         */

        $device  = $this->detectDevice($ua);
        $browser = $this->detectBrowser($ua);

        // Referer clean
        $referer = $request->headers->get('referer');
        if ($referer) {
            $host = parse_url($referer, PHP_URL_HOST);
            $referer = $host ? str_replace('www.', '', $host) : null;
            
            // Opțional: Dacă referer-ul e chiar site-ul nostru, îl ignorăm (navigare internă)
            if ($referer == $request->getHost()) {
                $referer = null; 
            }
        }

        try {
            Visit::create([
                'url'        => '/' . ltrim($request->path(), '/'),
                'ip'         => $request->ip(),
                'user_agent' => $ua,
                'referer'    => $referer,
                'device'     => $device,
                'browser'    => $browser,
                'country'    => null, // Se completează automat de Cron Job
                'city'       => null,
                'user_id'    => Auth::id(), // Salvăm ID-ul userului (dacă e logat și nu e admin)
            ]);
        } catch (\Throwable $e) {
            // Logăm silențios
        }

        return $response;
    }

    private function isBot(string $ua): bool
    {
        if (empty($ua) || $ua === '-' || $ua === 'unknown') return false;
        
        $ua = strtolower($ua);
        $bots = [
            'bot', 'crawl', 'spider', 'slurp', 'curl', 'python', 'wget', 
            'scrapy', 'facebook', 'google', 'bing', 'yandex', 'ahrefs', 
            'semrush', 'mj12', 'dotbot', 'uptime', 'monitor'
        ];

        foreach ($bots as $bot) {
            if (strpos($ua, $bot) !== false) return true;
        }
        return false;
    }

    private function detectDevice(string $ua): string
    {
        if (preg_match('/mobile|iphone|android/i', $ua)) return 'mobile';
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        $ua = strtolower($ua);
        if (strpos($ua, 'chrome') !== false && strpos($ua, 'edge') === false) return 'Chrome';
        if (strpos($ua, 'firefox') !== false) return 'Firefox';
        if (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) return 'Safari';
        if (strpos($ua, 'edge') !== false) return 'Edge';
        if (strpos($ua, 'opera') !== false || strpos($ua, 'opr') !== false) return 'Opera';
        return 'Other';
    }
}