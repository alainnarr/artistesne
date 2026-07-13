<?php

declare(strict_types=1);

use App\Enums\ApprovalStatus;
use App\Enums\ArtistStatus;
use App\Support\ReviewMessageTemplates;

it('exposes Filament label/color via the shared enum traits', function () {
    expect(ApprovalStatus::Approved->getLabel())->toBe(ApprovalStatus::Approved->label())
        ->and(ApprovalStatus::Approved->getColor())->toBe(ApprovalStatus::Approved->color())
        ->and(ArtistStatus::Published->getLabel())->toBe(ArtistStatus::Published->label())
        ->and(ArtistStatus::Published->getColor())->toBe(ArtistStatus::Published->color());
});

it('provides matching keys for review message templates', function () {
    $keys = array_keys(ReviewMessageTemplates::options());

    expect(array_keys(ReviewMessageTemplates::registration()))->toBe($keys)
        ->and(array_keys(ReviewMessageTemplates::change()))->toBe($keys)
        ->and(ReviewMessageTemplates::registration()['photo'])->toContain('photo')
        ->and(ReviewMessageTemplates::change()['biography'])->toContain('biographie');
});
