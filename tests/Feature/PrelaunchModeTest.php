<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Helpers shared across all describe blocks.
function withWhitelistedIp(): void
{
    // 127.0.0.1 is the default test client IP — add it to the whitelist.
    config(['app.prelaunch_ip_whitelist' => ['127.0.0.1']]);
}

function withNoWhitelist(): void
{
    config(['app.prelaunch_ip_whitelist' => []]);
}

describe('PrelaunchMode — strict', function (): void {
    beforeEach(function (): void {
        config(['app.prelaunch_mode' => 'strict']);
        withNoWhitelist();
    });

    it('returns 503 coming-soon on the homepage for non-whitelisted clients', function (): void {
        $this->get('/')->assertStatus(503)->assertViewIs('coming-soon');
    });

    it('returns 503 even on the registration page for non-whitelisted clients', function (): void {
        $this->get('/devenir-artiste')->assertStatus(503);
    });

    it('returns 503 on the artist portal for non-whitelisted clients', function (): void {
        $this->get('/artiste/connexion')->assertStatus(503);
    });

    it('always passes the health endpoint regardless of IP', function (): void {
        $this->get('/up')->assertOk();
    });

    it('grants full access to whitelisted IPs', function (): void {
        withWhitelistedIp();
        $this->get('/')->assertOk();
    });

    it('grants access to registration for whitelisted IPs', function (): void {
        withWhitelistedIp();
        $this->get('/devenir-artiste')->assertOk();
    });
});

describe('PrelaunchMode — v1', function (): void {
    beforeEach(function (): void {
        config(['app.prelaunch_mode' => 'v1']);
        withNoWhitelist();

        Route::any('/livewire/update', fn () => response('ok'));
        Route::any('/livewire-abc123/update', fn () => response('ok'));
    });

    it('returns 503 on the homepage for non-whitelisted clients', function (): void {
        $this->get('/')->assertStatus(503)->assertViewIs('coming-soon');
    });

    it('returns 503 on the artist directory for non-whitelisted clients', function (): void {
        $this->get('/artistes')->assertStatus(503);
    });

    it('allows the artist registration page for everyone', function (): void {
        $this->get('/devenir-artiste')->assertOk();
    });

    it('allows the artist portal login for everyone', function (): void {
        $this->get('/artiste/connexion')->assertOk();
    });

    it('allows classic livewire update endpoints for everyone', function (): void {
        $this->post('/livewire/update')->assertOk();
    });

    it('allows hashed livewire update endpoints for everyone', function (): void {
        $this->post('/livewire-abc123/update')->assertOk();
    });

    it('allows logout for everyone', function (): void {
        Route::post('/logout', fn () => response('ok'))->name('logout.test-stub');
        $this->post('/logout')->assertOk();
    });

    it('allows filament core routes (e.g. signed export download links) for everyone', function (): void {
        Route::get('/filament/test-stub', fn () => response('ok'));
        $this->get('/filament/test-stub')->assertOk();
    });

    it('always passes the health endpoint', function (): void {
        $this->get('/up')->assertOk();
    });

    it('grants whitelisted IPs full access including the public homepage', function (): void {
        withWhitelistedIp();
        $this->get('/')->assertOk();
    });

    it('grants whitelisted IPs access to the artist directory', function (): void {
        withWhitelistedIp();
        $this->get('/artistes')->assertOk();
    });
});

describe('PrelaunchMode — off', function (): void {
    it('serves the full site with no restriction', function (): void {
        config(['app.prelaunch_mode' => 'off']);
        $this->get('/')->assertOk();
    });
});
