<?php

namespace App\Filament\Resources\TaxonomyTerms\Schemas;

use App\Models\TaxonomyTerm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TaxonomyTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Type de terme')
                    ->options([
                        'domain' => 'Domaine artistique',
                        'main_activities' => 'Activité principale',
                        'secondary_activities' => 'Activité secondaire',
                        'keywords' => 'Mot-clé',
                    ])
                    ->required()
                    ->default('main_activities')
                    ->live(),
                Select::make('domain')
                    ->label('Domaine artistique')
                    ->options(fn (): array => TaxonomyTerm::domainSlugOptions())
                    ->visible(fn (Get $get): bool => $get('type') !== 'domain')
                    ->required(fn (Get $get): bool => ! in_array($get('type'), ['domain', 'keywords'], true))
                    ->placeholder(fn (Get $get): ?string => $get('type') === 'keywords' ? 'Tous les domaines' : null),
                TextInput::make('name')
                    ->label('Terme')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if ($get('type') === 'domain' && blank($get('slug'))) {
                            $set('slug', Str::slug($state ?? '', '_'));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Identifiant (slug)')
                    ->helperText('Valeur technique stable utilisée par les profils artistes ; évitez de la modifier après création.')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->visible(fn (Get $get): bool => $get('type') === 'domain')
                    ->required(fn (Get $get): bool => $get('type') === 'domain'),
                TextInput::make('position')
                    ->label('Position')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
