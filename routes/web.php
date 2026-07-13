<?php

use App\Http\Controllers\Admin\OidcController;
use App\Http\Controllers\Admin\RegistrationDocumentController;
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
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

//TODO : Remove this test route when the repository service is fully integrated into the application.
use App\Http\Controllers\RepositoryTestController;
Route::prefix('test-upload')->name('test-upload.')->group(function () {
    Route::get( '/', [RepositoryTestController::class, 'index'])->name('index');
    Route::post('/', [RepositoryTestController::class, 'store'])->name('store');
    Route::get( '/update', [RepositoryTestController::class, 'updateForm'])->name('updateForm');
    Route::post('/update', [RepositoryTestController::class, 'update'])->name('update');
});

//TODO : Remove this test route when the links service is fully integrated into the application.
use App\Http\Controllers\LinksTestController;
Route::prefix('test-links')->name('test-links.')->group(function () {
    Route::get(   '/', [LinksTestController::class, 'index'])->name('index');
    Route::post(  '/', [LinksTestController::class, 'store'])->name('store');
    Route::put(   '/', [LinksTestController::class, 'update'])->name('update');
    Route::delete('/', [LinksTestController::class, 'destroy'])->name('delete');
});

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

Route::get('/', Home::class)->name('home');

Route::view('/a-propos', 'about')->name('about');

Route::view('/conditions', 'conditions')->name('conditions');

Route::get('/contact', Contact::class)->name('contact');

Route::get('/demande-de-modification', RequestModification::class)->name('modification.request');

// Public artists listing page.
Route::get('/artistes', ArtistsIndex::class)->name('public.artists.index');

// Public artist profile page.
Route::get('/artistes/{artist:slug}', PublicArtistShow::class)->name('public.artist.show');

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

// Admin OIDC authentication (AD FS). No auth middleware — these initiate / receive the OIDC flow.
Route::prefix('admin/auth')->name('admin.auth.')->group(function () {
    Route::get('/redirect', [OidcController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [OidcController::class, 'callback'])->name('callback');

    // Local-only shortcut to log in as an admin without a real AD FS instance.
    // The route only exists in the local environment — not registered at all elsewhere.
    if (app()->environment('local')) {
        Route::get('/fake-login', [OidcController::class, 'fakeLogin'])->name('fake-login');
    }
});

// Admin file downloads (behind Filament's auth middleware).
Route::middleware(['auth', Authenticate::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get(
            '/registration-requests/{artistRegistrationRequest}/documents/{index}',
            RegistrationDocumentController::class,
        )->name('registration-requests.documents.download');
    });
