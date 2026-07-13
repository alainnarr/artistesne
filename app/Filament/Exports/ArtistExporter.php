<?php

namespace App\Filament\Exports;

use App\Models\Artist;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ArtistExporter extends Exporter
{
    protected static ?string $model = Artist::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Nom'),
            ExportColumn::make('discipline')->label('Domaine principal'),
            ExportColumn::make('secondary_discipline')->label('Domaine secondaire'),
            ExportColumn::make('city')->label('Commune'),
            ExportColumn::make('biography')->label('Biographie'),
            ExportColumn::make('email')->label('E-mail'),
            ExportColumn::make('website')->label('Site web'),
            ExportColumn::make('instagram')->label('Instagram'),
            ExportColumn::make('facebook')->label('Facebook'),
            ExportColumn::make('youtube')->label('YouTube'),
            ExportColumn::make('linkedin')->label('LinkedIn'),
            ExportColumn::make('vimeo')->label('Vimeo'),
            ExportColumn::make('status')->label('Statut')
                ->formatStateUsing(fn ($state) => $state?->label() ?? $state),
            ExportColumn::make('published_at')->label('Publié le'),
            ExportColumn::make('last_confirmed_at')->label('Dernière confirmation'),
            ExportColumn::make('created_at')->label('Créé le'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'L\'export des artistes est terminé. '
            .Number::format($export->successful_rows).' '
            .str('ligne')->plural($export->successful_rows).' exportée(s).';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' ligne(s) en échec.';
        }

        return $body;
    }
}
