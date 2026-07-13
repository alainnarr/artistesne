<?php

namespace App\Filament\Resources\ArtistRegistrationRequests;

use App\Enums\ApprovalStatus;
use App\Filament\Resources\ArtistRegistrationRequests\Pages\EditArtistRegistrationRequest;
use App\Filament\Resources\ArtistRegistrationRequests\Pages\ListArtistRegistrationRequests;
use App\Filament\Resources\ArtistRegistrationRequests\Schemas\ArtistRegistrationRequestForm;
use App\Filament\Resources\ArtistRegistrationRequests\Tables\ArtistRegistrationRequestsTable;
use App\Models\ArtistRegistrationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArtistRegistrationRequestResource extends Resource
{
    protected static ?string $model = ArtistRegistrationRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Validation';

    protected static ?string $modelLabel = "Demande d'inscription";

    protected static ?string $pluralModelLabel = "Demandes d'inscription";

    protected static ?string $navigationLabel = "Demandes d'inscription";

    protected static ?string $recordTitleAttribute = 'artist_name';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('status', ApprovalStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return ArtistRegistrationRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtistRegistrationRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtistRegistrationRequests::route('/'),
            'edit' => EditArtistRegistrationRequest::route('/{record}/edit'),
        ];
    }
}
