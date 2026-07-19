<?php

declare(strict_types=1);

namespace App\Filament\Resources\Registrations\Tables;

use App\Actions\ApproveRegistration;
use App\Actions\RejectRegistration;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\Registrations\RegistrationResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('artist_name')
                    ->label("Nom d'artiste")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('real_name')
                    ->label('Nom réel')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('disciplineMain.label')
                    ->label('Domaine')
                    ->sortable(),
                TextColumn::make('enum_status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (RegistrationStatus $state) => $state->label()),
                TextColumn::make('created_at')
                    ->label('Reçue le')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('enum_status')
                    ->label('Statut')
                    ->options(
                        collect(RegistrationStatus::cases())
                            ->mapWithKeys(fn (RegistrationStatus $s) => [$s->value => $s->label()])
                            ->all()
                    )
                    ->default(RegistrationStatus::OPEN->value),
            ])
            ->recordUrl(fn (Registration $record) => RegistrationResource::getUrl('view', ['record' => $record]))
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
                    ->visible(fn (Registration $record): bool => $record->enum_status === RegistrationStatus::OPEN)
                    ->action(function (array $data, Registration $record): void {
                        app(ApproveRegistration::class)($record, auth()->user(), $data['notes'] ?? null);
                        Notification::make()
                            ->title('Inscription approuvée')
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
                    ->visible(fn (Registration $record): bool => $record->enum_status === RegistrationStatus::OPEN)
                    ->action(function (array $data, Registration $record): void {
                        app(RejectRegistration::class)($record, auth()->user(), $data['notes'] ?? null);
                        Notification::make()->title('Demande refusée')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
