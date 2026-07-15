<?php

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Registration;
use App\Database\Models\SearchSynonym;
use App\Database\Models\User;
use App\Enums\ApprovalStatus;
use App\Enums\ArtistStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Artisan;

test('demo seeder creates a complete staging dataset', function () {
    Artisan::call('db:seed', ['--class' => DemoSeeder::class]);

    expect(User::query()->where('role', UserRole::Admin->value)->count())->toBe(3)
        ->and(User::query()->where('role', UserRole::Artist->value)->count())->toBe(8)
        ->and(Artist::query()->count())->toBe(8)
        ->and(Artist::query()->where('enum_status', ArtistStatus::Published->value)->count())->toBe(5)
        ->and(Artist::query()->where('enum_status', ArtistStatus::Draft->value)->count())->toBe(3)
        ->and(Registration::query()->where('email', 'sonia.petris@inventaire.test')->where('enum_status', RegistrationStatus::OPEN->value)->exists())->toBeTrue()
        ->and(Registration::query()->where('email', 'ruben.wyss@inventaire.test')->where('enum_status', RegistrationStatus::REJECTED->value)->exists())->toBeTrue()
        ->and(ArtistChangeRequest::query()->count())->toBe(2)
        ->and(ArtistChangeRequest::query()->where('status', ApprovalStatus::Pending->value)->count())->toBe(1)
        ->and(ArtistChangeRequest::query()->where('status', ApprovalStatus::Approved->value)->count())->toBe(1)
        ->and(SearchSynonym::query()->count())->toBe(5);

    expect(ArtistChangeRequest::query()->whereNull('submitted_by')->exists())->toBeFalse();
});
