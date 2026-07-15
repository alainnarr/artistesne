<?php

declare(strict_types=1);

use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Livewire\Public\RegisterArtist;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('stores registration documents on the private disk, not public', function () {
    Storage::fake('private');
    Storage::fake('public');

    $musique = Discipline::where('code', 'musique')->firstOrFail();
    $activity = $musique->activities()->firstOrFail();

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Jane Doe')
        ->set('birth_date', now()->subYears(30)->toDateString())
        ->set('email', 'jane-doc-test@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', (string) $musique->id)
        ->set('main_activity', (string) $activity->id)
        ->set('recent_achievement', 'Exposition 2025 à la galerie X.')
        ->set('attests', true)
        ->set('documents', [UploadedFile::fake()->create('portfolio.pdf', 100)])
        ->call('submit');

    $registration = Registration::where('email', 'jane-doc-test@example.com')->firstOrFail();
    $repository = $registration->repositories()->firstOrFail();

    expect($repository->enum_disk->value)->toBe('private');
    Storage::disk('private')->assertExists($repository->path);
    Storage::disk('public')->assertMissing($repository->path);
});
