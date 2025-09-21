<?php

use App\Services\LocalizationHelper;
use App\Http\Middleware\LinkLocalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

describe('LocalizationHelper', function () {
    beforeEach(function () {
        // Reset locale and config before each test
        App::setLocale('en');
        Config::set('app.locale', 'en');
        Config::set('app.availableLocales', [
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spanish',
        ]);
        Config::set('app.publicServices', []);
    });

    it('sets locale when valid locale is provided', function () {
        $segments = ['fr', 'dashboard'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeNull();
        expect(App::getLocale())->toBe('fr');
    });

    it('redirects with default locale when invalid locale is provided', function () {
        $segments = ['invalid', 'dashboard'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($result->getTargetUrl())->toBe(localeUrl('/dashboard'));
    });

    it('redirects with default locale when empty segments array', function () {
        $segments = [];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($result->getTargetUrl())->toBe(localeUrl('/'));
    });

    it('redirects when first segment is a public service', function () {
        Config::set('app.allServicesAvailable', ['api', 'health']);

        $segments = ['api', 'users'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($result->getTargetUrl())->toBe(localeUrl('/api/users'));
    });

    it('handles public service with single segment', function () {
        Config::set('app.allServicesAvailable', ['health']);

        $segments = ['health'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($result->getTargetUrl())->toBe(localeUrl('health'));
    });

    it('handles multiple segments with valid locale', function () {
        $segments = ['es', 'user', 'profile', '123'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeNull();
        expect(App::getLocale())->toBe('es');
    });

    it('redirects with default locale for multiple invalid segments', function () {
        $segments = ['invalid', 'user', 'profile'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($result->getTargetUrl())->toBe(localeUrl('user/profile'));
    });

    it('handles case sensitivity correctly', function () {
        $segments = ['FR', 'dashboard'];
        $result = LocalizationHelper::setLocalization($segments);

        expect($result)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($result->getTargetUrl())->toBe(localeUrl('dashboard','fr'));
    });
});

describe('LinkLocalization Middleware', function () {
    beforeEach(function () {
        App::setLocale('en');
        Config::set('app.locale', 'en');
        Config::set('app.availableLocales', [
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spanish',
        ]);
        Config::set('app.allServicesAvailable', []);
    });

    it('passes request through when valid locale is provided', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/fr/dashboard', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('success');
        });

        expect($response->getContent())->toBe('success');
        expect(App::getLocale())->toBe('fr');
    });

    it('redirects when invalid locale is provided', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/invalid/dashboard', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('should not reach here');
        });

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/dashboard');
    });

    it('redirects when public service is accessed', function () {
        Config::set('app.allServicesAvailable', ['api']);

        $middleware = new LinkLocalization();
        $request = Request::create('/api/users', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('should not reach here');
        });

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/api/users');
    });

    it('handles root path correctly', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('should not reach here');
        });

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en');
    });

    it('preserves query parameters in redirects', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/invalid/dashboard?foo=bar&baz=qux', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('should not reach here');
        });

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        // Note: Laravel's redirect helper should preserve query parameters
        expect($response->getTargetUrl())->toBe('/en/dashboard');
    });

    it('handles POST requests correctly', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/fr/submit', 'POST', ['data' => 'test']);

        $response = $middleware->handle($request, function ($req) {
            return response('form submitted');
        });

        expect($response->getContent())->toBe('form submitted');
        expect(App::getLocale())->toBe('fr');
    });

    it('handles nested routes with valid locale', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/es/admin/users/create', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('admin panel');
        });

        expect($response->getContent())->toBe('admin panel');
        expect(App::getLocale())->toBe('es');
    });

    it('redirects nested routes with invalid locale', function () {
        $middleware = new LinkLocalization();
        $request = Request::create('/de/admin/users/create', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('should not reach here');
        });

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
        expect($response->getTargetUrl())->toBe('/en/admin/users/create');
    });
});
