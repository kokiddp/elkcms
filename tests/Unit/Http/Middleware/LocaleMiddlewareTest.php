<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LocaleMiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    protected LocaleMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new LocaleMiddleware();
    }

    public function test_sets_locale_from_url_parameter(): void
    {
        $request = Request::create('/page?lang=it', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            $this->assertEquals('it', App::getLocale());

            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_sets_locale_from_session(): void
    {
        Session::put('locale', 'de');

        $request = Request::create('/page', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('de', App::getLocale());

            return response('OK');
        });
    }

    public function test_sets_locale_from_cookie(): void
    {
        $request = Request::create('/page', 'GET');
        $request->cookies->set('locale', 'fr');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('fr', App::getLocale());

            return response('OK');
        });
    }

    public function test_sets_locale_from_accept_language_header(): void
    {
        $request = Request::create('/page', 'GET');
        $request->headers->set('Accept-Language', 'es-ES,es;q=0.9');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('es', App::getLocale());

            return response('OK');
        });
    }

    public function test_url_parameter_takes_precedence_over_session(): void
    {
        Session::put('locale', 'de');

        $request = Request::create('/page?lang=it', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('it', App::getLocale());

            return response('OK');
        });
    }

    public function test_session_takes_precedence_over_cookie(): void
    {
        Session::put('locale', 'de');

        $request = Request::create('/page', 'GET');
        $request->cookies->set('locale', 'fr');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('de', App::getLocale());

            return response('OK');
        });
    }

    public function test_cookie_takes_precedence_over_header(): void
    {
        $request = Request::create('/page', 'GET');
        $request->cookies->set('locale', 'fr');
        $request->headers->set('Accept-Language', 'es-ES,es;q=0.9');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('fr', App::getLocale());

            return response('OK');
        });
    }

    public function test_falls_back_to_default_locale_for_unsupported_language(): void
    {
        $request = Request::create('/page?lang=xx', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    public function test_stores_locale_in_session(): void
    {
        $request = Request::create('/page?lang=it', 'GET');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('it', Session::get('locale'));
    }

    public function test_sets_cookie_with_locale(): void
    {
        $request = Request::create('/page?lang=it', 'GET');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Check if cookie was queued (Cookie::queue stores it for later)
        $queuedCookies = Cookie::getQueuedCookies();
        $this->assertNotEmpty($queuedCookies);

        $localeCookie = collect($queuedCookies)->first(fn ($cookie) => $cookie->getName() === 'locale');
        $this->assertNotNull($localeCookie);
        $this->assertEquals('it', $localeCookie->getValue());
    }

    public function test_parses_complex_accept_language_header(): void
    {
        $request = Request::create('/page', 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9,de;q=0.8,it;q=0.7');

        $this->middleware->handle($request, function ($req) {
            // Should pick 'en' which is the first supported locale
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    public function test_handles_case_insensitive_locale_codes(): void
    {
        $request = Request::create('/page?lang=IT', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('it', App::getLocale());

            return response('OK');
        });
    }

    public function test_extracts_language_from_locale_code(): void
    {
        $request = Request::create('/page', 'GET');
        $request->headers->set('Accept-Language', 'it-IT');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('it', App::getLocale());

            return response('OK');
        });
    }

    public function test_uses_default_locale_when_no_preference_provided(): void
    {
        $request = Request::create('/page', 'GET');

        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    public function test_middleware_continues_to_next_middleware(): void
    {
        $request = Request::create('/page', 'GET');
        $nextCalled = false;

        $this->middleware->handle($request, function ($req) use (&$nextCalled) {
            $nextCalled = true;

            return response('OK');
        });

        $this->assertTrue($nextCalled);
    }
}
