<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request and set the application locale.
     *
     * Priority order:
     * 1. URL query parameter (?lang=it)
     * 2. Session
     * 3. Cookie
     * 4. Accept-Language header
     * 5. Default locale from config
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->detectLocale($request);

        // Validate and set locale
        if ($this->isValidLocale($locale)) {
            App::setLocale($locale);
        } else {
            $locale = config('languages.default', 'en');
            App::setLocale($locale);
        }

        // Store locale in session for future requests
        Session::put('locale', $locale);

        // Get response from next middleware
        $response = $next($request);

        // Set locale cookie (1 year expiry)
        Cookie::queue('locale', $locale, 525600);

        return $response;
    }

    /**
     * Detect locale from request using priority order.
     */
    protected function detectLocale(Request $request): string
    {
        // 1. Check URL query parameter
        if ($request->has('lang')) {
            $locale = strtolower($request->input('lang'));
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 2. Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 3. Check cookie
        if ($request->hasCookie('locale')) {
            $locale = strtolower($request->cookie('locale'));
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 4. Check Accept-Language header
        $locale = $this->parseAcceptLanguage($request);
        if ($locale && $this->isValidLocale($locale)) {
            return $locale;
        }

        // 5. Fall back to default
        return config('languages.default', 'en');
    }

    /**
     * Parse Accept-Language header and return best matching locale.
     */
    protected function parseAcceptLanguage(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (! $acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,de;q=0.8")
        $languages = [];

        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';q=', $lang);
            $locale = trim($parts[0]);
            $quality = isset($parts[1]) ? (float) $parts[1] : 1.0;

            // Extract language code from locale (e.g., "en-US" -> "en")
            if (str_contains($locale, '-')) {
                $locale = explode('-', $locale)[0];
            }

            $languages[strtolower($locale)] = $quality;
        }

        // Sort by quality (highest first)
        arsort($languages);

        // Find first supported language
        foreach (array_keys($languages) as $lang) {
            if ($this->isValidLocale($lang)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Check if locale is supported.
     */
    protected function isValidLocale(string $locale): bool
    {
        $supportedLocales = array_keys(config('languages.supported', []));

        return in_array(strtolower($locale), array_map('strtolower', $supportedLocales));
    }
}
