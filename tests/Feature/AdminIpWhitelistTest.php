<?php

declare(strict_types=1);

use App\Database\Models\User;
use App\Http\Middleware\AdminIpWhitelist;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
|--------------------------------------------------------------------------
| AdminIpWhitelist middleware
|--------------------------------------------------------------------------
|
| Tests the IP-restriction gate for the admin panel.
| Unit-style assertions (direct middleware invocation) and HTTP assertions
| sit in the same file so both the logic and the wiring are covered.
|
*/

function makeAdminRequest(string $ip, string $path = '/admin'): Request
{
    $request = Request::create($path, 'GET');
    $request->server->set('REMOTE_ADDR', $ip);

    return $request;
}

function runAdminMiddleware(Request $request): int
{
    $middleware = new AdminIpWhitelist;
    $called = false;
    try {
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return new Response('OK', 200);
        });

        return $called ? 200 : $response->getStatusCode();
    } catch (HttpException $e) {
        return $e->getStatusCode();
    }
}

// ── Logic tests (direct middleware invocation) ──────────────────────────

it('allows all admin traffic when no whitelist is configured', function () {
    Config::set('app.admin_ip_whitelist', []);

    expect(runAdminMiddleware(makeAdminRequest('1.2.3.4')))->toBe(200);
});

it('allows a whitelisted exact IP', function () {
    Config::set('app.admin_ip_whitelist', ['203.0.113.5']);

    expect(runAdminMiddleware(makeAdminRequest('203.0.113.5')))->toBe(200);
});

it('blocks a non-whitelisted IP when whitelist is set', function () {
    Config::set('app.admin_ip_whitelist', ['203.0.113.5']);

    expect(runAdminMiddleware(makeAdminRequest('8.8.8.8')))->toBe(404);
});

it('allows an IP within a whitelisted CIDR range', function () {
    Config::set('app.admin_ip_whitelist', ['192.168.0.0/24']);

    expect(runAdminMiddleware(makeAdminRequest('192.168.0.100')))->toBe(200);
});

it('blocks an IP outside a whitelisted CIDR range', function () {
    Config::set('app.admin_ip_whitelist', ['192.168.0.0/24']);

    expect(runAdminMiddleware(makeAdminRequest('192.168.1.100')))->toBe(404);
});

it('allows multiple ranges and matches the correct one', function () {
    Config::set('app.admin_ip_whitelist', ['10.0.0.0/8', '148.196.0.0/16']);

    expect(runAdminMiddleware(makeAdminRequest('10.50.20.1')))->toBe(200);
    expect(runAdminMiddleware(makeAdminRequest('148.196.42.1')))->toBe(200);
    expect(runAdminMiddleware(makeAdminRequest('1.2.3.4')))->toBe(404);
});

it('blocks an empty string IP', function () {
    Config::set('app.admin_ip_whitelist', ['203.0.113.5']);

    expect(runAdminMiddleware(makeAdminRequest('')))->toBe(404);
});

// ── HTTP integration tests (wiring through Filament + bootstrap/app) ───

it('admin panel returns 404 for a blocked IP', function () {
    Config::set('app.admin_ip_whitelist', ['203.0.113.5']);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->get('/admin')
        ->assertNotFound();
});

it('admin panel is reachable for a whitelisted IP', function () {
    Config::set('app.admin_ip_whitelist', ['127.0.0.1']);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/admin')
        ->assertSuccessful();
});

it('admin auth route returns 404 for a blocked IP', function () {
    Config::set('app.admin_ip_whitelist', ['203.0.113.5']);

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->get('/admin/auth/redirect')
        ->assertNotFound();
});

it('admin routes are unrestricted when whitelist is empty', function () {
    Config::set('app.admin_ip_whitelist', []);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
        ->get('/admin')
        ->assertSuccessful();
});
