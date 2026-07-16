<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Database\Models\Artist;
use App\Database\Models\User;
use App\Notifications\ExportCompletedNotification;
use Filament\Actions\Action;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;

class ArtistExporter extends Exporter
{
    protected static ?string $model = Artist::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('artist_name')->label('Nom'),
            ExportColumn::make('disciplineMain.label')->label('Domaine principal'),
            ExportColumn::make('disciplineSecondary.label')->label('Domaine secondaire'),
            ExportColumn::make('city')->label('Commune'),
            ExportColumn::make('biography')->label('Biographie'),
            ExportColumn::make('email')->label('E-mail'),
            ExportColumn::make('phone')->label('Téléphone'),
            ExportColumn::make('enum_status')->label('Statut')
                ->formatStateUsing(fn ($state) => $state?->label() ?? $state),
            ExportColumn::make('published_at')->label('Publié le'),
            ExportColumn::make('last_confirmed_at')->label('Dernière confirmation'),
            ExportColumn::make('created_at')->label('Créé le'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "L'export des artistes est terminé. "
            .Number::format($export->successful_rows).' '
            .str('ligne')->plural($export->successful_rows).' exportée(s).';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' ligne(s) en échec.';
        }

        return $body;
    }

    /**
     * The admin panel has no database-notifications bell, so also email the
     * user who triggered the export — otherwise a completed export (and its
     * download link) would never be seen.
     */
    public static function modifyCompletedNotification(Notification $notification, Export $export): Notification
    {
        $downloadLinks = [];

        foreach ($notification->getActions() as $action) {
            if (! $action instanceof Action) {
                continue;
            }

            $url = (string) $action->getUrl();

            if (blank($url)) {
                continue;
            }

            $downloadLinks[] = [
                'label' => (string) $action->getLabel(),
                // Filament signs the download route as a relative URL; make it absolute for the email.
                'url' => url($url),
            ];
        }

        if ($downloadLinks && $export->user) {
            /** @var User $user */
            $user = $export->user;

            $user->notify(new ExportCompletedNotification(
                title: (string) $notification->getTitle(),
                body: (string) $notification->getBody(),
                downloadLinks: $downloadLinks,
            ));
        }

        return $notification;
    }
}
