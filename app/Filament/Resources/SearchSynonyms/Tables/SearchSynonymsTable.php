<?php

namespace App\Filament\Resources\SearchSynonyms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SearchSynonymsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('term')
                    ->label('Terme principal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('synonyms')
                    ->label('Synonymes')
                    ->formatStateUsing(fn (?array $state): string => implode(', ', $state ?? []))
                    ->wrap(),
                IconColumn::make('one_way')
                    ->label('Sens unique')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Mise à jour')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('term');
    }
}
