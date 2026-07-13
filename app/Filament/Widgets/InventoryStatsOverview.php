<?php

namespace App\Filament\Widgets;

use App\Enums\ApprovalStatus;
use App\Enums\ArtistStatus;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\ArtistRegistrationRequest;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = "Aperçu de l'inventaire";

    protected function getStats(): array
    {
        $pendingRegistrations = ArtistRegistrationRequest::query()
            ->where('status', ApprovalStatus::Pending)
            ->count();

        $pendingChanges = ArtistChangeRequest::query()
            ->where('status', ApprovalStatus::Pending)
            ->count();

        $publishedArtists = Artist::query()
            ->where('status', ArtistStatus::Published)
            ->count();

        $totalArtists = Artist::query()->count();

        return [
            Stat::make("Demandes d'inscription en attente", $pendingRegistrations)
                ->description($pendingRegistrations > 0 ? 'À examiner' : 'Aucune en attente')
                ->descriptionIcon(Heroicon::OutlinedInbox)
                ->color($pendingRegistrations > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.artist-registration-requests.index')),

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
