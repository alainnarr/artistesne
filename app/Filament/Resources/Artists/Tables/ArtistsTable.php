<?php

declare(strict_types=1);

namespace App\Filament\Resources\Artists\Tables;

use App\Database\Models\Artist;
use App\Enums\ArtistStatus;
use App\Filament\Exports\ArtistExporter;
use App\Notifications\ProfileReactivatedNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArtistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('artist_name')
                    ->label("Nom d'artiste")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('disciplineMain.label')
                    ->label('Domaine')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('enum_status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (ArtistStatus $state) => $state->label()),
                TextColumn::make('user.email')
                    ->label('Compte')
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('updated_at')
                    ->label('Mise à jour')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('enum_status')
                    ->label('Statut')
                    ->options(ArtistStatus::class),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Afficher')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Afficher cet artiste ?')
                    ->modalDescription('Le profil redeviendra visible publiquement.')
                    ->visible(fn (Artist $record): bool => ! $record->isPublished())
                    ->action(function (Artist $record): void {
                        // A previously-published artist being republished is a
                        // reactivation (e.g. after the semiannual auto-disable) —
                        // worth an email. A brand-new artist's first publication
                        // is not: `published_at` being already set is what tells
                        // them apart.
                        $isReactivation = $record->published_at !== null;

                        $record->update([
                            'enum_status' => ArtistStatus::PUBLISHED->value,
                            'published_at' => $record->published_at ?? now(),
                        ]);

                        if ($isReactivation && $record->user) {
                            $record->user->notify(new ProfileReactivatedNotification($record->artist_name));
                        }

                        Notification::make()->title('Artiste affiché')->success()->send();
                    }),

                Action::make('unpublish')
                    ->label('Masquer')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Masquer cet artiste ?')
                    ->modalDescription('Le profil ne sera plus visible publiquement.')
                    ->visible(fn (Artist $record): bool => $record->isPublished())
                    ->action(function (Artist $record): void {
                        $record->update(['enum_status' => ArtistStatus::DRAFT->value]);
                        Notification::make()->title('Artiste masqué')->success()->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(ArtistExporter::class)
                    ->label('Exporter'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('artist_name');
    }
}
