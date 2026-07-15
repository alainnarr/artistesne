<?php

declare(strict_types=1);

namespace App\Filament\Resources\ArtistChangeRequests;

use App\Database\Models\ArtistChangeRequest;
use App\Enums\ApprovalStatus;
use App\Filament\Resources\ArtistChangeRequests\Pages\EditArtistChangeRequest;
use App\Filament\Resources\ArtistChangeRequests\Pages\ListArtistChangeRequests;
use App\Filament\Resources\ArtistChangeRequests\Schemas\ArtistChangeRequestForm;
use App\Filament\Resources\ArtistChangeRequests\Tables\ArtistChangeRequestsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArtistChangeRequestResource extends Resource
{
    protected static ?string $model = ArtistChangeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Validation';

    protected static ?string $modelLabel = 'Modification';

    protected static ?string $pluralModelLabel = 'Modifications';

    protected static ?string $navigationLabel = 'Modifications';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('status', ApprovalStatus::Pending->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return ArtistChangeRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtistChangeRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtistChangeRequests::route('/'),
            'edit' => EditArtistChangeRequest::route('/{record}/edit'),
        ];
    }
}
