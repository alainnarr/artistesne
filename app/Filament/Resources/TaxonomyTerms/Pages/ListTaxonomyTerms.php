<?php

namespace App\Filament\Resources\TaxonomyTerms\Pages;

use App\Filament\Resources\TaxonomyTerms\TaxonomyTermResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxonomyTerms extends ListRecords
{
    protected static string $resource = TaxonomyTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
