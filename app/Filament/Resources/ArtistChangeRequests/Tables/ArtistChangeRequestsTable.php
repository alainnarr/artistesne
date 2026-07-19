<?php

declare(strict_types=1);

namespace App\Filament\Resources\ArtistChangeRequests\Tables;

use App\Database\Models\ArtistChangeRequest;
use App\Enums\ArtistChangeRequestStatus;
use App\Notifications\ChangeRequestDecisionNotification;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArtistChangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('artist.artist_name')
                    ->label('Artiste')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitter.name')
                    ->label('Soumis par')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (ArtistChangeRequestStatus $state) => $state->label()),
                TextColumn::make('created_at')
                    ->label('Reçue')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ArtistChangeRequestStatus::class)
                    ->default(ArtistChangeRequestStatus::PENDING->value),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Appliquer ces modifications ?')
                    ->modalDescription("La page de l'artiste sera mise à jour immédiatement.")
                    ->schema([
                        Textarea::make('notes')->label('Message interne (optionnel)')->rows(3),
                    ])
                    ->visible(fn (ArtistChangeRequest $record): bool => $record->status->isPending())
                    ->action(function (array $data, ArtistChangeRequest $record): void {
                        $record->apply();
                        $record->approve(auth()->user(), $data['notes'] ?? null);
                        $record->artist->user?->notify(new ChangeRequestDecisionNotification($record));
                        Notification::make()->title('Modifications appliquées')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Refuser')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Refuser la modification ?')
                    ->schema([
                        Textarea::make('notes')->label('Motif (optionnel)')->rows(3),
                    ])
                    ->visible(fn (ArtistChangeRequest $record): bool => $record->status->isPending())
                    ->action(function (array $data, ArtistChangeRequest $record): void {
                        $record->reject(auth()->user(), $data['notes'] ?? null);
                        $record->artist->user?->notify(new ChangeRequestDecisionNotification($record));
                        Notification::make()->title('Modification refusée')->success()->send();
                    }),

                EditAction::make()->label('Examiner'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
