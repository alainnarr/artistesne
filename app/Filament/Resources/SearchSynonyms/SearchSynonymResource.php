<?php

namespace App\Filament\Resources\SearchSynonyms;

use App\Filament\Resources\SearchSynonyms\Pages\CreateSearchSynonym;
use App\Filament\Resources\SearchSynonyms\Pages\EditSearchSynonym;
use App\Filament\Resources\SearchSynonyms\Pages\ListSearchSynonyms;
use App\Filament\Resources\SearchSynonyms\Schemas\SearchSynonymForm;
use App\Filament\Resources\SearchSynonyms\Tables\SearchSynonymsTable;
use App\Models\SearchSynonym;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SearchSynonymResource extends Resource
{
    protected static ?string $model = SearchSynonym::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?string $navigationLabel = 'Synonymes';

    protected static ?string $pluralLabel = 'Synonymes de recherche';

    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return SearchSynonymForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SearchSynonymsTable::configure($table);
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
            'index' => ListSearchSynonyms::route('/'),
            'create' => CreateSearchSynonym::route('/create'),
            'edit' => EditSearchSynonym::route('/{record}/edit'),
        ];
    }
}
