<?php

namespace App\Filament\Resources\ArtistRegistrationRequests\Pages;

use App\Filament\Resources\ArtistRegistrationRequests\ArtistRegistrationRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListArtistRegistrationRequests extends ListRecords
{
    protected static string $resource = ArtistRegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
