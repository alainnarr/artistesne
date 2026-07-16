<?php

use App\Http\Controllers\Admin\OidcController;
use App\Http\Controllers\Admin\RegistrationDocumentController;
use App\Http\Controllers\Artist\ConfirmationController;
use App\Http\Controllers\Artist\MagicLinkController;
use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Artist\Auth\RequestMagicLink;
use App\Livewire\Artist\EditProfile as ArtistEditProfile;
use App\Livewire\Dev\ComponentGallery;
use App\Livewire\Public\ArtistShow as PublicArtistShow;
use App\Livewire\Public\ArtistsIndex;
use App\Livewire\Public\Contact;
use App\Livewire\Public\Home;
use App\Livewire\Public\RegisterArtist;
use App\Livewire\Public\RequestModification;
use App\Livewire\Public\RequestReactivation;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('public.home');

Route::view('/a-propos', 'about')->name('public.about');

//TODO : Remove this test route when the registrations service is fully integrated into the application.
use App\Http\Controllers\RegistrationsTestController;
Route::prefix('test-registration')->name('test-registration.')->group(function () {
    Route::get( '/', [RegistrationsTestController::class, 'index'])->name('index');
    Route::post('/', [RegistrationsTestController::class, 'store'])->name('store');
    Route::post('/{registration}/status', [RegistrationsTestController::class, 'changeStatus'])->name('status');
});

//TODO : Remove this test route when the synonyms service is fully integrated into the application.
use App\Http\Controllers\SynonymsTestController;
Route::prefix('test-synonyms')->name('test-synonyms.')->group(function () {
    Route::get(   '/', [SynonymsTestController::class, 'index'])->name('index');
    Route::post(  '/', [SynonymsTestController::class, 'store'])->name('store');
    Route::put(   '/', [SynonymsTestController::class, 'update'])->name('update');
    Route::delete('/', [SynonymsTestController::class, 'destroy'])->name('delete');
});

// TODO : Remove this test route when the keywords service is fully integrated into the application.
use App\Http\Controllers\KeywordsTestController;
Route::prefix('test-keywords')->name('test-keywords.')->group(function () {
    Route::get('/', [KeywordsTestController::class, 'index'])->name('index');
    Route::post('/attach', [KeywordsTestController::class, 'attach'])->name('attach');
    Route::post('/detach', [KeywordsTestController::class, 'detach'])->name('detach');
});

// TODO : Remove this test route when the keywords service is fully integrated into the application.
use App\Http\Controllers\ArtistChangeRequestTestController;
Route::prefix('test-changes')->name('test-changes.')->group(function () {
    Route::get('/', [ArtistChangeRequestTestController::class, 'index'])->name('index');
    Route::post('/', [ArtistChangeRequestTestController::class, 'store'])->name('store');
});


Route::view('/conditions', 'conditions')->name('public.conditions');

Route::view('/protection-des-donnees', 'privacy')->name('public.privacy');

Route::get('/contact', Contact::class)->name('public.contact');

Route::get('/demande-de-modification', RequestModification::class)->name('public.modification-request');
Route::get('/demande-de-reactivation', RequestReactivation::class)->name('public.reactivation-request');

// Public artists listing + profile — gated by feature flag (disabled for V1).
// Enable via ARTISTS_LISTING=true in .env when the new data model is populated.
if (config('features.artists_listing', false)) {
    Route::get('/artistes', ArtistsIndex::class)->name('public.artists.index');
    Route::get('/artistes/{artist:slug}', PublicArtistShow::class)->name('public.artist.show');
} else {
    // Named routes must always exist so existing footer/nav links don't throw.
    Route::get('/artistes', fn () => redirect()->route('public.home'))->name('public.artists.index');
    Route::get('/artistes/{artist:slug}', fn () => redirect()->route('public.home'))->name('public.artist.show');
}

// Public artist registration request form.
Route::get('/devenir-artiste', RegisterArtist::class)
    ->name('public.artist-registration');

// Session logout for the artist portal (Filament admin has its own, separate
// logout flow). Replaces Fortify's AuthenticatedSessionController@destroy.
Route::post('/logout', LogoutController::class)->name('logout');

// Magic link auth for artists — this is also the single "Espace Artistes" hub
// page (référencement, régénérer un lien, demande de modification/suppression,
// demande de réactivation), so it must stay reachable for already-authenticated
// artists too — no `guest` middleware here.
Route::get('/artiste/connexion', RequestMagicLink::class)->name('artist.login');

Route::get('/artiste/connexion/lien/{user}', [MagicLinkController::class, 'consume'])
    ->name('artist.magic-link-consume');

// Semiannual profile confirmation — signed URLs sent by email.
Route::get('/artiste/confirmer/{token}', [ConfirmationController::class, 'confirm'])
    ->name('artist.confirm-profile');
Route::get('/artiste/mettre-a-jour/{token}', [ConfirmationController::class, 'update'])
    ->name('artist.confirm-update');

// Artist portal — editing the profile content always requires the magic link.
Route::middleware(['auth', 'artist'])->prefix('artiste')->name('artist.')->group(function () {
    Route::get('/profil', ArtistEditProfile::class)->name('profile-edit');
});

// Component gallery — local + testing only.
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/composants', ComponentGallery::class)->name('dev.gallery');
}

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
