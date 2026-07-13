<?php

namespace App\Filament\Resources\ArtistChangeRequests\Pages;

use App\Filament\Resources\ArtistChangeRequests\ArtistChangeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListArtistChangeRequests extends ListRecords
{
    protected static string $resource = ArtistChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
