<?php

namespace App\Filament\Resources\TaxonomyTerms\Tables;

use App\Models\TaxonomyTerm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TaxonomyTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain')
                    ->label('Domaine')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'domain' => 'Domaine artistique',
                        'main_activities' => 'Activité principale',
                        'secondary_activities' => 'Activité secondaire',
                        'keywords' => 'Mot-clé',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Terme')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('domain')
                    ->label('Domaine')
                    ->options(fn (): array => TaxonomyTerm::domainSlugOptions()),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'domain' => 'Domaine artistique',
                        'main_activities' => 'Activité principale',
                        'secondary_activities' => 'Activité secondaire',
                        'keywords' => 'Mot-clé',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('domain');
    }
}
