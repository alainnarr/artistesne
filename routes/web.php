<?php

use App\Http\Controllers\Admin\OidcController;
use App\Http\Controllers\Admin\RegistrationDocumentController;
use App\Http\Controllers\Admin\StoragePermissionsMaintenanceController;
use App\Http\Controllers\Artist\ConfirmationController;
use App\Http\Controllers\Artist\MagicLinkController;
use App\Livewire\Artist\Auth\RequestMagicLink;
use App\Livewire\Artist\Dashboard as ArtistDashboard;
use App\Livewire\Artist\EditProfile as ArtistEditProfile;
use App\Livewire\Artist\ProfileSetup as ArtistProfileSetup;
use App\Livewire\Dev\ComponentGallery;
use App\Livewire\Public\ArtistShow as PublicArtistShow;
use App\Livewire\Public\ArtistsIndex;
use App\Livewire\Public\Contact;
use App\Livewire\Public\Home;
use App\Livewire\Public\RegisterArtist;
use App\Livewire\Public\RequestModification;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::view('/a-propos', 'about')->name('about');

Route::view('/conditions', 'conditions')->name('conditions');

Route::view('/protection-des-donnees', 'privacy')->name('privacy');

Route::get('/contact', Contact::class)->name('contact');

Route::get('/demande-de-modification', RequestModification::class)->name('modification.request');

// Public artists listing + profile — gated by feature flag (disabled for V1).
// Enable via ARTISTS_LISTING=true in .env when the new data model is populated.
if (config('features.artists_listing', false)) {
    Route::get('/artistes', ArtistsIndex::class)->name('public.artists.index');
    Route::get('/artistes/{artist:slug}', PublicArtistShow::class)->name('public.artist.show');
} else {
    // Named routes must always exist so existing footer/nav links don't throw.
    Route::get('/artistes', fn () => redirect()->route('home'))->name('public.artists.index');
    Route::get('/artistes/{artist:slug}', fn () => redirect()->route('home'))->name('public.artist.show');
}

// Public artist registration request form.
Route::get('/devenir-artiste', RegisterArtist::class)
    ->name('artist.register');
Route::get('/register', fn () => redirect()->route('artist.login'))
    ->middleware('guest')
    ->name('register');

// Magic link auth for artists.
Route::middleware('guest')->group(function () {
    Route::get('/artiste/connexion', RequestMagicLink::class)->name('artist.login');
});

Route::get('/artiste/connexion/lien/{user}', [MagicLinkController::class, 'consume'])
    ->name('artist.magic-link.consume');

// Semiannual profile confirmation — signed URLs sent by email.
Route::get('/artiste/confirmer/{token}', [ConfirmationController::class, 'confirm'])
    ->name('artist.confirm-profile');
Route::get('/artiste/mettre-a-jour/{token}', [ConfirmationController::class, 'update'])
    ->name('artist.confirm-update');

// Artist portal placeholders (implemented in phase 3).
Route::middleware(['auth', 'artist'])->prefix('artiste')->name('artist.')->group(function () {
    Route::get('/dashboard', ArtistDashboard::class)->name('dashboard');
    Route::get('/profil', ArtistEditProfile::class)->name('profile.edit');
    Route::get('/creation-profil', ArtistProfileSetup::class)->name('profile.setup');
});

// Component gallery — local + testing only.
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/composants', ComponentGallery::class)->name('dev.gallery');
}

require __DIR__.'/settings.php';

// Admin OIDC authentication (AD FS). The IP whitelist is checked first so the
// login endpoints are also protected when APP_ADMIN_IP_WHITELIST is configured.
Route::prefix('admin/auth')->name('admin.auth.')->middleware('admin.ip')->group(function () {
    Route::get('/redirect', [OidcController::class, 'redirect'])->name('redirect');
    Route::match(['GET', 'POST'], '/callback', [OidcController::class, 'callback'])->name('callback');

    // Local-only shortcut to log in as an admin without a real AD FS instance.
    // The route only exists in the local environment — not registered at all elsewhere.
    if (app()->environment('local')) {
        Route::get('/fake-login', [OidcController::class, 'fakeLogin'])->name('fake-login');
    }
});

// Authenticated download of private registration documents (CV/portfolio) reviewed
// by admins on the registration review page. Access is gated by auth + isAdmin()
// in the controller since these files must never be reachable via a public/guessable URL.
Route::middleware(['admin.ip', 'auth'])
    ->get(
        'admin/registrations/{registration}/documents/{repository}/download',
        [RegistrationDocumentController::class, 'download']
    )
    ->name('admin.registrations.documents.download');

// TEMPORARY (added 2026-07-14): one-off fix for a server-side permissions issue
// where `shared/storage/app` is owned solely by www-data (mode 0700) and
// neither deploy SSH account can create app/private or the app/public symlink.
// Remove this route + StoragePermissionsMaintenanceController once ops fixes
// ownership properly on vdn-typos1/vdn-typop1.
Route::middleware(['admin.ip', 'auth'])
    ->get('admin/maintenance/fix-storage-permissions', [StoragePermissionsMaintenanceController::class, 'fix'])
    ->name('admin.maintenance.fix-storage-permissions');
