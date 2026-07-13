<?php

use App\Enums\ApprovalStatus;
use App\Enums\ArtistStatus;
use App\Enums\UserRole;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\ArtistRegistrationRequest;
use App\Models\SearchSynonym;
use App\Models\TaxonomyTerm;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Artisan;

test('demo seeder creates a complete staging dataset', function () {
    Artisan::call('db:seed', ['--class' => DemoSeeder::class]);

    expect(User::query()->where('role', UserRole::Admin->value)->count())->toBe(3)
        ->and(User::query()->where('role', UserRole::Artist->value)->count())->toBe(23)
        ->and(Artist::query()->count())->toBe(23)
        ->and(Artist::query()->where('status', ArtistStatus::Published->value)->count())->toBe(18)
        ->and(Artist::query()->where('status', ArtistStatus::Draft->value)->count())->toBe(5)
        ->and(ArtistRegistrationRequest::query()->count())->toBe(7)
        ->and(ArtistRegistrationRequest::query()->where('status', ApprovalStatus::Pending->value)->count())->toBe(4)
        ->and(ArtistRegistrationRequest::query()->where('status', ApprovalStatus::Approved->value)->count())->toBe(1)
        ->and(ArtistRegistrationRequest::query()->where('status', ApprovalStatus::Rejected->value)->count())->toBe(1)
        ->and(ArtistRegistrationRequest::query()->where('status', ApprovalStatus::ChangesRequested->value)->count())->toBe(1)
        ->and(ArtistChangeRequest::query()->count())->toBe(7)
        ->and(ArtistChangeRequest::query()->where('status', ApprovalStatus::Pending->value)->count())->toBe(4)
        ->and(ArtistChangeRequest::query()->where('status', ApprovalStatus::Approved->value)->count())->toBe(1)
        ->and(ArtistChangeRequest::query()->where('status', ApprovalStatus::Rejected->value)->count())->toBe(1)
        ->and(ArtistChangeRequest::query()->where('status', ApprovalStatus::ChangesRequested->value)->count())->toBe(1)
        ->and(SearchSynonym::query()->count())->toBe(5)
        ->and(TaxonomyTerm::query()->where('type', 'keywords')->count())->toBeGreaterThan(0);

    expect(ArtistChangeRequest::query()->whereNull('submitted_by')->exists())->toBeFalse();
});
