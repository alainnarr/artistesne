<?php

declare(strict_types=1);

namespace App\Filament\Resources\ArtistChangeRequests\Pages;

use App\Database\Models\ArtistChangeRequest;
use App\Filament\Resources\ArtistChangeRequests\ArtistChangeRequestResource;
use App\Notifications\ChangeRequestDecisionNotification;
use App\Support\ReviewMessageTemplates;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditArtistChangeRequest extends EditRecord
{
    protected static string $resource = ArtistChangeRequestResource::class;

    public function getTitle(): string
    {
        return 'Examiner la modification';
    }

    protected function getHeaderActions(): array
    {
        /** @var ArtistChangeRequest $record */
        $record = $this->getRecord();

        if (! $record->status->isPending()) {
            return [];
        }

        return [
            Action::make('approve')
                ->label('Approuver et appliquer')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Appliquer ces modifications ?')
                ->modalDescription("La page de l'artiste sera mise à jour immédiatement.")
                ->schema([
                    Textarea::make('notes')->label('Message interne (optionnel)')->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    $record->apply();
                    $record->approve(auth()->user(), $data['notes'] ?? null);
                    $record->artist->user?->notify(new ChangeRequestDecisionNotification($record));
                    Notification::make()->title('Modifications appliquées')->success()->send();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            Action::make('requestChanges')
                ->label('Demander des ajustements')
                ->color('info')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Select::make('template')
                        ->label('Modèle de message')
                        ->options(ReviewMessageTemplates::options())
                        ->placeholder('— Choisir un modèle (optionnel) —')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set): void {
                            $templates = ReviewMessageTemplates::change();

                            if ($state && isset($templates[$state])) {
                                $set('notes', $templates[$state]);
                            }
                        }),
                    Textarea::make('notes')->label('Message à transmettre')->required()->rows(6),
                ])
                ->action(function (array $data) use ($record): void {
                    $record->requestChanges(auth()->user(), $data['notes']);
                    $record->artist->user?->notify(new ChangeRequestDecisionNotification($record));
                    Notification::make()->title('Ajustements demandés')->success()->send();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            Action::make('reject')
                ->label('Refuser')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('notes')->label('Motif (optionnel)')->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    $record->reject(auth()->user(), $data['notes'] ?? null);
                    $record->artist->user?->notify(new ChangeRequestDecisionNotification($record));
                    Notification::make()->title('Modification refusée')->success()->send();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
