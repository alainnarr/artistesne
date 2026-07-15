<?php

declare(strict_types=1);

namespace App\Filament\Resources\Artists\Schemas;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Enums\DisciplineType;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                            TextInput::make('artist_name')
                                ->label("Nom d'artiste")
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            Select::make('city')
                                ->label('Commune')
                                ->options(function (?Artist $record): array {
                                    $options = [];

                                    foreach (config('localities.groups') as $group => $communes) {
                                        $options[$group] = array_combine($communes, $communes);
                                    }

                                    $currentValue = $record?->city;

                                    if (filled($currentValue) && ! collect($options)->flatten(1)->contains($currentValue)) {
                                        $options['Autre (valeur existante non listée)'] = [$currentValue => $currentValue];
                                    }

                                    return $options;
                                })
                                ->searchable()
                                ->native(false)
                                ->helperText('Liste fermée des communes neuchâteloises (+ "Hors canton"). Une valeur existante non répertoriée reste sélectionnable sous "Autre".')
                                ->columnSpan(1),
                            Select::make('enum_status')
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
                            Select::make('discipline_main_id')
                                ->label('Domaine principal')
                                ->options(fn (): array => Discipline::query()
                                    ->where('enum_type', DisciplineType::MAIN->value)
                                    ->orderBy('label')
                                    ->pluck('label', 'id')
                                    ->all())
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('activities', []))
                                ->columnSpan(1),
                            Select::make('discipline_secondary')
                                ->label('Domaine secondaire')
                                ->options(fn (): array => Discipline::query()
                                    ->where('enum_type', DisciplineType::MAIN->value)
                                    ->orderBy('label')
                                    ->pluck('label', 'id')
                                    ->all())
                                ->columnSpan(1),
                            Select::make('activities')
                                ->label('Activités principales')
                                ->multiple()
                                ->options(fn (Get $get): array => filled($get('discipline_main_id'))
                                    ? Activity::query()
                                        ->where('discipline_id', (int) $get('discipline_main_id'))
                                        ->orderBy('label')
                                        ->pluck('label', 'id')
                                        ->all()
                                    : [])
                                ->disabled(fn (Get $get): bool => blank($get('discipline_main_id')))
                                ->helperText('Sélectionnez d\'abord un domaine principal.')
                                ->dehydrateStateUsing(fn (?array $state): array => array_map('intval', $state ?? []))
                                ->columnSpan(1),
                            Select::make('secondary_activities')
                                ->label('Activités secondaires')
                                ->multiple()
                                ->options(fn (): array => Activity::query()
                                    ->whereRelation('discipline', 'enum_type', DisciplineType::SECONDARY->value)
                                    ->orderBy('label')
                                    ->pluck('label', 'id')
                                    ->all())
                                ->dehydrateStateUsing(fn (?array $state): array => array_map('intval', $state ?? []))
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
                                ->maxLength(125)
                                ->columnSpan(1),
                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->maxLength(30)
                                ->columnSpan(1),
                            Checkbox::make('enum_show_contact')
                                ->label('Afficher un bouton de contact sur la fiche publique')
                                ->formatStateUsing(fn (mixed $state): bool => $state instanceof ArtistShowContact
                                    ? $state->toBool()
                                    : (bool) $state)
                                ->dehydrateStateUsing(fn (mixed $state): int => ArtistShowContact::fromBool((bool) $state)->value)
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
