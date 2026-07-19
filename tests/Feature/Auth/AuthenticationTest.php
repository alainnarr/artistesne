<?php

use App\Database\Models\User;

// This app has no password-based login: artists authenticate via magic link
// and admins via AD FS/Filament. There's no 'login' named route — the default
// `Authenticate` middleware is configured (see bootstrap/app.php) to send
// unauthenticated visitors straight to the artist magic-link page.
test('unauthenticated access to an auth-protected route redirects to the artist login page', function () {
    $response = $this->get(route('artist.profile-edit'));

    $response->assertRedirect(route('artist.login'));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('public.home'));

    $this->assertGuest();
});
