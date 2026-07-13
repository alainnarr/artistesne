<?php

namespace App\Filament\Resources\Artists\Schemas;

use App\Enums\ArtistStatus;
use App\Models\TaxonomyTerm;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label("Nom d'artiste")
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('city')
                                ->label('Commune')
                                ->maxLength(120)
                                ->columnSpan(1),
                            Select::make('status')
                                ->label('Statut')
                                ->options(ArtistStatus::class)
                                ->required()
                                ->default(ArtistStatus::Draft)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Domaines et activités')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('discipline')
                                ->label('Domaine principal')
                                ->options(fn (): array => TaxonomyTerm::domainOptions())
                                ->columnSpan(1),
                            Select::make('secondary_discipline')
                                ->label('Domaine secondaire')
                                ->options(fn (): array => TaxonomyTerm::domainOptions())
                                ->columnSpan(1),
                            TagsInput::make('activities')
                                ->label('Activités principales')
                                ->placeholder('Ajouter une activité')
                                ->columnSpan(1),
                            TagsInput::make('secondary_activities')
                                ->label('Activités secondaires')
                                ->placeholder('Ajouter une activité')
                                ->columnSpan(1),
                            TagsInput::make('keywords')
                                ->label('Mots-clés')
                                ->placeholder('Ajouter un mot-clé')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Coordonnées')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->email()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->maxLength(50)
                                ->columnSpan(1),
                            Toggle::make('display_contact_button')
                                ->label('Afficher le bouton de contact')
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Biographie')
                    ->schema([
                        RichEditor::make('biography')
                            ->label('')
                            ->columnSpanFull(),
                    ]),

                Section::make('Liens personnels')
                    ->schema([
                        Repeater::make('links')
                            ->label('')
                            ->schema([
                                TextInput::make('label')->label('Libellé')->required(),
                                TextInput::make('url')->url()->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Ajouter un lien')
                            ->columnSpanFull(),
                    ]),

                Section::make('Collaborations')
                    ->schema([
                        Repeater::make('collaborations')
                            ->label('')
                            ->schema([
                                TextInput::make('name')->label('Nom')->required(),
                                TextInput::make('url')->url()->label('URL'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Ajouter une collaboration')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
