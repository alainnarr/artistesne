<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Registration;
use App\Enums\ArtistChangeRequestStatus;
use App\Enums\ArtistStatus;
use App\Enums\RegistrationStatus;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = "Aperçu de l'inventaire";

    protected function getStats(): array
    {
        $pendingRegistrations = Registration::query()
            ->whereIn('enum_status', [RegistrationStatus::OPEN->value, RegistrationStatus::PENDING->value])
            ->count();

        $pendingChanges = ArtistChangeRequest::query()
            ->where('status', ArtistChangeRequestStatus::PENDING->value)
            ->count();

        $publishedArtists = Artist::query()
            ->where('enum_status', ArtistStatus::PUBLISHED->value)
            ->count();

        $totalArtists = Artist::query()->count();

        return [
            Stat::make("Demandes d'inscription en attente", $pendingRegistrations)
                ->description($pendingRegistrations > 0 ? 'À examiner' : 'Aucune en attente')
                ->descriptionIcon(Heroicon::OutlinedInbox)
                ->color($pendingRegistrations > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.registrations.index')),

            Stat::make('Modifications en attente', $pendingChanges)
                ->description($pendingChanges > 0 ? 'À examiner' : 'Aucune en attente')
                ->descriptionIcon(Heroicon::OutlinedPencilSquare)
                ->color($pendingChanges > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.artist-change-requests.index')),

            Stat::make('Artistes publiés', $publishedArtists)
                ->description("{$totalArtists} au total")
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('primary')
                ->url(route('filament.admin.resources.artists.index')),
        ];
    }
}
