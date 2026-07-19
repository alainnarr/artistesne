<?php

declare(strict_types=1);

use App\Enums\ArtistChangeRequestStatus;
use App\Enums\ArtistStatus;
use App\Support\ReviewMessageTemplates;

it('exposes Filament label/color via the shared enum traits', function () {
    expect(ArtistChangeRequestStatus::APPROVED->getLabel())->toBe(ArtistChangeRequestStatus::APPROVED->label())
        ->and(ArtistChangeRequestStatus::APPROVED->getColor())->toBe(ArtistChangeRequestStatus::APPROVED->color())
        ->and(ArtistStatus::PUBLISHED->getLabel())->toBe(ArtistStatus::PUBLISHED->label())
        ->and(ArtistStatus::PUBLISHED->getColor())->toBe(ArtistStatus::PUBLISHED->color());
});

it('provides matching keys for review message templates', function () {
    $keys = array_keys(ReviewMessageTemplates::options());

    expect(array_keys(ReviewMessageTemplates::registration()))->toBe($keys)
        ->and(array_keys(ReviewMessageTemplates::change()))->toBe($keys)
        ->and(ReviewMessageTemplates::registration()['photo'])->toContain('photo')
        ->and(ReviewMessageTemplates::change()['biography'])->toContain('biographie');
});
