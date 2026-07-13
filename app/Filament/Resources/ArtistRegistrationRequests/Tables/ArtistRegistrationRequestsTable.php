<?php

namespace App\Filament\Resources\ArtistRegistrationRequests\Tables;

use App\Actions\ApproveRegistrationRequest;
use App\Actions\RejectRegistrationRequest;
use App\Enums\ApprovalStatus;
use App\Models\ArtistRegistrationRequest;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArtistRegistrationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('artist_name')
                    ->label("Nom d'artiste")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Reçue')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ApprovalStatus::class)
                    ->default(ApprovalStatus::Pending->value),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Approuver la demande')
                    ->modalDescription('Un compte artiste sera créé et un lien de connexion lui sera envoyé par e-mail.')
                    ->schema([
                        Textarea::make('notes')->label('Message interne (optionnel)')->rows(3),
                    ])
                    ->visible(fn (ArtistRegistrationRequest $record): bool => $record->status->isPending())
                    ->action(function (array $data, ArtistRegistrationRequest $record): void {
                        app(ApproveRegistrationRequest::class)($record, auth()->user(), $data['notes'] ?? null);
                        Notification::make()
                            ->title('Demande approuvée')
                            ->body('Le compte artiste a été créé et un lien de connexion a été envoyé.')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Refuser')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Refuser la demande')
                    ->schema([
                        Textarea::make('notes')->label('Motif (optionnel)')->rows(3),
                    ])
                    ->visible(fn (ArtistRegistrationRequest $record): bool => $record->status->isPending())
                    ->action(function (array $data, ArtistRegistrationRequest $record): void {
                        app(RejectRegistrationRequest::class)($record, auth()->user(), $data['notes'] ?? null);
                        Notification::make()->title('Demande refusée')->success()->send();
                    }),

                EditAction::make()->label('Examiner'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
