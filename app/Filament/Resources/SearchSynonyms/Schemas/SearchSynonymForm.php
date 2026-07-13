<?php

namespace App\Filament\Resources\SearchSynonyms\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SearchSynonymForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('term')
                    ->label('Terme principal')
                    ->required()
                    ->maxLength(255),
                TagsInput::make('synonyms')
                    ->label('Synonymes')
                    ->helperText('Appuyez sur Entrée ou virgule pour ajouter un synonyme.')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('one_way')
                    ->label('Sens unique')
                    ->helperText('Si activé, seul le terme principal trouve ses synonymes, mais pas l\'inverse.')
                    ->default(false),
            ]);
    }
}
