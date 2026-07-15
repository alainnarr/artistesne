<?php

declare(strict_types=1);

namespace App\Filament\Resources\Registrations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('real_name')
                                ->label('Nom complet (non public)')
                                ->state(fn ($record) => $record->real_name ?? '—'),
                            TextEntry::make('artist_name')
                                ->label("Nom d'artiste")
                                ->state(fn ($record) => $record->artist_name ?? '—'),
                            TextEntry::make('birth_date')
                                ->label('Date de naissance (non publique)')
                                ->state(fn ($record) => $record?->birth_date?->translatedFormat('j F Y') ?? '—'),
                            TextEntry::make('email')
                                ->label('E-mail de contact')
                                ->state(fn ($record) => $record->email ?? '—'),
                            TextEntry::make('phone')
                                ->label('Téléphone (non public)')
                                ->state(fn ($record) => $record->phone ?? '—'),
                        ]),
                    ]),

                Section::make('Territorialité')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('residence_location')
                                ->label('Lieu de résidence')
                                ->state(fn ($record) => $record->residence_location ?? '—'),
                            TextEntry::make('locality')
                                ->label('Localité (slug)')
                                ->state(fn ($record) => $record->locality ?? '—'),
                        ]),
                        TextEntry::make('canton_link')
                            ->label('Ancrage dans le tissu culturel neuchâtelois')
                            ->state(fn ($record) => $record->canton_link ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Domaine et activités')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('discipline_main')
                                ->label('Domaine principal')
                                ->state(fn ($record) => $record->disciplineMain->label ?? '—'),
                            TextEntry::make('discipline_secondary')
                                ->label('Domaine secondaire')
                                ->state(fn ($record) => $record->disciplineSecondary->label ?? '—'),
                        ]),
                        TextEntry::make('activities')
                            ->label('Activités')
                            ->state(fn ($record) => $record?->activities->pluck('label')->join(', ') ?: '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Documents joints')
                    ->schema([
                        TextEntry::make('documents')
                            ->label('CV / portfolio')
                            ->state(function ($record) {
                                $repositories = $record->repositories ?? collect();

                                if ($repositories->isEmpty()) {
                                    return 'Aucun document joint.';
                                }

                                return new HtmlString(
                                    $repositories->map(fn ($repository) => sprintf(
                                        '<a href="%s" target="_blank" rel="noopener" class="underline text-primary-600 hover:text-primary-500">%s</a> <span class="text-gray-400">(%s)</span>',
                                        e(route('admin.registrations.documents.download', [
                                            'registration' => $record,
                                            'repository' => $repository,
                                        ])),
                                        e($repository->name),
                                        e(Number::fileSize($repository->size))
                                    ))->implode('<br>')
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Professionnalisme')
                    ->schema([
                        TextEntry::make('training')
                            ->label('Formation artistique')
                            ->state(fn ($record) => $record->training ?? '—')
                            ->columnSpanFull(),
                        TextEntry::make('paid_work')
                            ->label('Activité rémunérée régulière')
                            ->state(fn ($record) => $record->paid_work ?? '—')
                            ->columnSpanFull(),
                        TextEntry::make('recognition')
                            ->label('Reconnaissance par le champ')
                            ->state(fn ($record) => $record->recognition ?? '—')
                            ->columnSpanFull(),
                        TextEntry::make('recent_achievements')
                            ->label('Réalisation professionnelle récente')
                            ->state(fn ($record) => $record->recent_achievements ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Temporalité')
                    ->schema([
                        TextEntry::make('last_work')
                            ->label('Dernière activité significative')
                            ->state(fn ($record) => $record->last_work ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Décision')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('enum_status')
                                ->label('Statut')
                                ->state(fn ($record) => $record?->enum_status?->label() ?? '—'),
                            TextEntry::make('reviewed_at')
                                ->label('Examinée le')
                                ->state(fn ($record) => $record?->reviewed_at?->translatedFormat('j F Y à H:i') ?? '—'),
                        ]),
                        TextEntry::make('review_notes')
                            ->label('Notes de révision')
                            ->state(fn ($record) => $record->review_notes ?? '—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
