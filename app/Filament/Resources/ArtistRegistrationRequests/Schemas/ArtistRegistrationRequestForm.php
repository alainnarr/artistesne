<?php

namespace App\Filament\Resources\ArtistRegistrationRequests\Schemas;

use App\Enums\ApprovalStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ArtistRegistrationRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('full_name')
                                ->label('Nom complet (non public)')
                                ->content(fn ($record) => $record?->full_name ?? '—'),
                            Placeholder::make('artist_name')
                                ->label("Nom d'artiste")
                                ->content(fn ($record) => $record?->artist_name ?? '—'),
                            Placeholder::make('show_artist_name')
                                ->label("Afficher le nom d'artiste")
                                ->content(fn ($record) => $record?->show_artist_name ? 'Oui' : 'Non'),
                            Placeholder::make('birth_date')
                                ->label('Date de naissance (non publique)')
                                ->content(fn ($record) => $record?->birth_date?->translatedFormat('j F Y') ?? '—'),
                            Placeholder::make('email')
                                ->label('E-mail de contact')
                                ->content(fn ($record) => $record?->email),
                            Placeholder::make('display_contact_button')
                                ->label('Bouton contact souhaité')
                                ->content(fn ($record) => $record?->display_contact_button ? 'Oui' : 'Non'),
                            Placeholder::make('phone')
                                ->label('Téléphone (non public)')
                                ->content(fn ($record) => $record?->phone ?? '—'),
                        ]),
                    ]),

                Section::make('Territorialité')
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('locality')
                                ->label('Lieu de résidence')
                                ->content(fn ($record) => $record?->locality ?? $record?->residence_location ?? '—'),
                            Placeholder::make('commune')
                                ->label('Commune (hors canton)')
                                ->content(fn ($record) => $record?->commune ?? '—'),
                        ]),
                        Placeholder::make('canton_link')
                            ->label('Ancrage dans le tissu culturel neuchâtelois')
                            ->content(fn ($record) => $record?->canton_link ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Domaine et activités')
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('main_domain')
                                ->label('Domaine principal')
                                ->content(fn ($record) => $record?->main_domain ?? '—'),
                            Placeholder::make('main_activity')
                                ->label('Activité principale')
                                ->content(fn ($record) => $record?->main_activity ?? '—'),
                        ]),
                    ]),

                Section::make('Professionnalisme')
                    ->schema([
                        Placeholder::make('training')
                            ->label('Formation artistique')
                            ->content(fn ($record) => $record?->training ?? '—')
                            ->columnSpanFull(),
                        Placeholder::make('paid_activity')
                            ->label('Activité rémunérée régulière')
                            ->content(fn ($record) => $record?->paid_activity ?? '—')
                            ->columnSpanFull(),
                        Placeholder::make('recognition')
                            ->label('Reconnaissance par le champ')
                            ->content(fn ($record) => $record?->recognition ?? '—')
                            ->columnSpanFull(),
                        Placeholder::make('recent_achievement')
                            ->label('Réalisation professionnelle récente')
                            ->content(fn ($record) => $record?->recent_achievement ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Temporalité et documents')
                    ->schema([
                        Placeholder::make('last_activity')
                            ->label('Dernière activité significative')
                            ->content(fn ($record) => $record?->last_activity ?? '—')
                            ->columnSpanFull(),
                        Placeholder::make('links')
                            ->label('Liens')
                            ->content(fn ($record) => filled($record?->links) ? implode(' · ', $record->links) : '—')
                            ->columnSpanFull(),
                        Placeholder::make('documents')
                            ->label('Documents transmis')
                            ->content(function ($record): HtmlString|string {
                                if (blank($record?->documents)) {
                                    return '—';
                                }

                                $links = array_map(function (array $doc, int $index) use ($record): string {
                                    $name = e($doc['name'] ?? basename($doc['path'] ?? "Document {$index}"));
                                    $url = route('admin.registration-requests.documents.download', [
                                        'artistRegistrationRequest' => $record->id,
                                        'index' => $index,
                                    ]);

                                    return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener\" class=\"underline hover:no-underline\">{$name}</a>";
                                }, $record->documents, array_keys($record->documents));

                                return new HtmlString('<ul class="space-y-1 list-none">'.implode('', array_map(fn (string $l): string => "<li>{$l}</li>", $links)).'</ul>');
                            })
                            ->columnSpanFull(),
                        Placeholder::make('documents_info')
                            ->label('Informations complémentaires')
                            ->content(fn ($record) => $record?->documents_info ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Statut')
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('status')
                                ->label('Statut actuel')
                                ->content(fn ($record) => $record?->status?->label() ?? '—'),
                        ]),
                    ]),

                Section::make('Notes internes')
                    ->visible(fn ($record) => $record && ! $record->status->isPending())
                    ->schema([
                        Grid::make(2)->schema([
                            Placeholder::make('reviewed_by')
                                ->label('Examiné par')
                                ->content(fn ($record) => $record?->reviewer?->name ?? '—'),
                            Placeholder::make('reviewed_at')
                                ->label('Examiné le')
                                ->content(fn ($record) => $record?->reviewed_at?->translatedFormat('j F Y H:i') ?? '—'),
                        ]),
                        Placeholder::make('review_notes')
                            ->label('Notes')
                            ->content(fn ($record) => $record?->review_notes ?? '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes (édition)')
                    ->visible(fn ($record) => $record && $record->status === ApprovalStatus::Pending)
                    ->schema([
                        Textarea::make('review_notes')
                            ->label("Notes (visibles uniquement par l'administration)")
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
