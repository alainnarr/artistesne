<?php

namespace App\Filament\Resources\TaxonomyTerms\Pages;

use App\Filament\Resources\TaxonomyTerms\TaxonomyTermResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxonomyTerm extends EditRecord
{
    protected static string $resource = TaxonomyTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
