<?php

declare(strict_types=1);

namespace App\Filament\Resources\Artists;

use App\Database\Models\Artist;
use App\Enums\ArtistStatus;
use App\Filament\Resources\Artists\Pages\EditArtist;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Filament\Resources\Artists\Schemas\ArtistForm;
use App\Filament\Resources\Artists\Tables\ArtistsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Artistes';

    protected static ?string $modelLabel = 'Artiste';

    protected static ?string $pluralModelLabel = 'Artistes';

    protected static ?string $navigationLabel = 'Artistes';

    protected static ?string $recordTitleAttribute = 'artist_name';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('enum_status', ArtistStatus::PUBLISHED->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return ArtistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtistsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtists::route('/'),
            'edit' => EditArtist::route('/{record}/edit'),
        ];
    }
}
