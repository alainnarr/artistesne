<?php

namespace App\Filament\Resources\TaxonomyTerms;

use App\Filament\Resources\TaxonomyTerms\Pages\CreateTaxonomyTerm;
use App\Filament\Resources\TaxonomyTerms\Pages\EditTaxonomyTerm;
use App\Filament\Resources\TaxonomyTerms\Pages\ListTaxonomyTerms;
use App\Filament\Resources\TaxonomyTerms\Schemas\TaxonomyTermForm;
use App\Filament\Resources\TaxonomyTerms\Tables\TaxonomyTermsTable;
use App\Models\TaxonomyTerm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TaxonomyTermResource extends Resource
{
    protected static ?string $model = TaxonomyTerm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Taxonomies';

    protected static ?string $pluralLabel = 'Termes de taxonomie';

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return TaxonomyTermForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxonomyTermsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxonomyTerms::route('/'),
            'create' => CreateTaxonomyTerm::route('/create'),
            'edit' => EditTaxonomyTerm::route('/{record}/edit'),
        ];
    }
}
