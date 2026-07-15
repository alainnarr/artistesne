<?php

use App\Database\Models\User;
use App\Enums\UserRole;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Build a Socialite user stub returned by Socialite::driver('adfs')->user().
 *
 * @param  array<string, mixed>  $overrides
 */
function fakeSocialiteUser(array $overrides = []): SocialiteUser
{
    $socialiteUser = new SocialiteUser;
    $socialiteUser->map(array_merge([
        'id' => 'adfs-sub-abc123',
        'name' => 'Jean Dupont',
        'email' => 'jean.dupont@ne.ch',
    ], $overrides));
    $socialiteUser->setRaw(array_merge([
        'sub' => 'adfs-sub-abc123',
        'name' => 'Jean Dupont',
        'email' => 'jean.dupont@ne.ch',
        'groups' => [],
    ], $overrides['raw'] ?? []));

    return $socialiteUser;
}

/**
 * Stub the Socialite driver so it returns $socialiteUser without hitting AD FS.
 */
function mockSocialite(SocialiteUser $socialiteUser): void
{
    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')->with('adfs')->andReturn($provider);
}

// ---------------------------------------------------------------------------
// redirect endpoint
// ---------------------------------------------------------------------------

test('/admin/auth/redirect starts the OIDC flow', function () {
    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('redirect')->andReturn(redirect('https://adfs.ne.ch/adfs/oauth2/authorize?...'));

    Socialite::shouldReceive('driver')->with('adfs')->andReturn($provider);

    $this->get(route('admin.auth.redirect'))
        ->assertRedirect();
});

test('/admin/login auto-redirects to AD FS by default when configured', function () {
    // Auto-redirect only kicks in when ADFS_BASE_URL is set (e.g. staging/prod);
    // in local dev without a real AD FS instance it's skipped (see AdminLogin::mount()).
    config(['services.adfs.base_url' => 'https://adfs.ne.ch']);

    $provider = Mockery::mock(AbstractProvider::class);
    $provider->shouldReceive('redirect')->andReturn(redirect('https://adfs.ne.ch/adfs/oauth2/authorize?...'));

    Socialite::shouldReceive('driver')->with('adfs')->andReturn($provider);

    $this->get(route('filament.admin.auth.login'))
        ->assertRedirect(route('admin.auth.redirect'));
});

test('/admin/login does not auto-redirect when AD FS base URL is not configured', function () {
    config(['services.adfs.base_url' => null]);

    $this->get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee('Se connecter avec AD FS');
});

test('/admin/login manual mode shows AD FS button and hides local credentials form', function () {
    $response = $this->get(route('filament.admin.auth.login', ['manual' => 1]));

    $response
        ->assertOk()
        ->assertSee('Se connecter avec AD FS')
        ->assertDontSee('data[email]')
        ->assertDontSee('data[password]');
});

// ---------------------------------------------------------------------------
// callback — success paths
// ---------------------------------------------------------------------------

test('callback logs in a pre-existing admin by adfs_id', function () {
    $admin = User::factory()->admin()->withAdfsId('adfs-sub-abc123')->create([
        'email' => 'jean.dupont@ne.ch',
    ]);

    mockSocialite(fakeSocialiteUser());

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('callback logs in a pre-existing admin by email and sets adfs_id', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'jean.dupont@ne.ch',
        'adfs_id' => null,
    ]);

    mockSocialite(fakeSocialiteUser());

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $this->assertAuthenticatedAs($admin);
    expect($admin->fresh()->adfs_id)->toBe('adfs-sub-abc123');
});

test('callback provisions a new admin via JIT when enabled', function () {
    config(['services.adfs.jit_provisioning' => true]);

    mockSocialite(fakeSocialiteUser());

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $user = User::where('email', 'jean.dupont@ne.ch')->first();
    expect($user)
        ->not->toBeNull()
        ->role->toBe(UserRole::Admin)
        ->adfs_id->toBe('adfs-sub-abc123')
        ->password->toBeNull();

    $this->assertAuthenticatedAs($user);
});

// ---------------------------------------------------------------------------
// callback — rejection paths
// ---------------------------------------------------------------------------

test('callback rejects an artist account even if email matches', function () {
    User::factory()->artist()->create(['email' => 'jean.dupont@ne.ch']);

    mockSocialite(fakeSocialiteUser());

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.auth.login'));

    $this->assertGuest();
});

test('callback rejects when user does not exist and JIT is disabled', function () {
    config(['services.adfs.jit_provisioning' => false]);

    mockSocialite(fakeSocialiteUser());

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.auth.login'));

    $this->assertGuest();
});

test('callback rejects when user is not in the required AD group', function () {
    config(['services.adfs.allowed_group' => 'Admins-Inventaire']);

    User::factory()->admin()->withAdfsId('adfs-sub-abc123')->create([
        'email' => 'jean.dupont@ne.ch',
    ]);

    mockSocialite(fakeSocialiteUser([
        'raw' => ['groups' => ['Other-Group']],
    ]));

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.auth.login'));

    $this->assertGuest();
});

test('callback allows when user is in the required AD group', function () {
    config(['services.adfs.allowed_group' => 'Admins-Inventaire']);

    $admin = User::factory()->admin()->withAdfsId('adfs-sub-abc123')->create([
        'email' => 'jean.dupont@ne.ch',
    ]);

    mockSocialite(fakeSocialiteUser([
        'raw' => ['groups' => ['Admins-Inventaire', 'Other-Group']],
    ]));

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('callback handles AD FS error parameter gracefully', function () {
    $this->get(route('admin.auth.callback', [
        'error' => 'access_denied',
        'error_description' => 'The user denied the request.',
    ]))->assertRedirect(route('filament.admin.auth.login'));

    $this->assertGuest();
});

test('callback handles missing authorization code gracefully', function () {
    $this->get(route('admin.auth.callback'))
        ->assertRedirect(route('filament.admin.auth.login'));

    $this->assertGuest();
});

test('JIT provisioning succeeds when AD FS omits name claims and name is derived from UPN', function () {
    // AD FS on-premise often omits name/given_name/family_name claims.
    // The provider falls back to the UPN username segment as the display name.
    config(['services.adfs.jit_provisioning' => true]);

    mockSocialite(fakeSocialiteUser([
        'name' => 'BianchiAl',   // derived from upn BianchiAl@ne.ch by AdfsProvider
        'email' => 'BianchiAl@ne.ch',
        'id' => 'adfs-sub-bianchi',
        'raw' => [
            'sub' => 'adfs-sub-bianchi',
            'upn' => 'BianchiAl@ne.ch',
            'unique_name' => 'BianchiAl@ne.ch',
        ],
    ]));

    $this->get(route('admin.auth.callback', ['code' => 'auth-code-123']))
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $user = User::where('email', 'BianchiAl@ne.ch')->first();
    expect($user)
        ->not->toBeNull()
        ->name->toBe('BianchiAl')
        ->adfs_id->toBe('adfs-sub-bianchi')
        ->role->toBe(UserRole::Admin);
});

test('admin logout redirects to AD FS end-session endpoint when configured', function () {
    config(['services.adfs.base_url' => 'https://adfs.ne.ch']);

    $admin = User::factory()->admin()->create();

    $expected = 'https://adfs.ne.ch/adfs/oauth2/logout?'.http_build_query([
        'post_logout_redirect_uri' => route('filament.admin.auth.login', ['manual' => 1]),
    ]);

    $this->actingAs($admin)
        ->post(route('filament.admin.auth.logout'))
        ->assertRedirect($expected);

    $this->assertGuest();
});

test('admin logout redirects to manual admin login when AD FS is not configured', function () {
    config(['services.adfs.base_url' => null]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('filament.admin.auth.logout'))
        ->assertRedirect(route('filament.admin.auth.login', ['manual' => 1]));

    $this->assertGuest();
});
