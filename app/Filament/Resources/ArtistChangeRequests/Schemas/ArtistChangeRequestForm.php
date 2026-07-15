<?php

declare(strict_types=1);

namespace App\Filament\Resources\ArtistChangeRequests\Schemas;

use App\Enums\ApprovalStatus;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtistChangeRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contexte')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('artist')
                                ->label('Artiste')
                                ->state(fn ($record) => $record?->artist?->artist_name),
                            TextEntry::make('submitter')
                                ->label('Soumis par')
                                ->state(fn ($record) => $record?->submitter->name ?? '—'),
                            TextEntry::make('created_at')
                                ->label('Reçue')
                                ->state(fn ($record) => $record?->created_at?->translatedFormat('j F Y H:i') ?? '—'),
                        ]),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->state(fn ($record) => $record?->status?->label() ?? '—'),
                    ]),

                Section::make('Modifications proposées')
                    ->schema([
                        TextEntry::make('changes')
                            ->hiddenLabel()
                            ->state(fn ($record) => $record ? view('filament.change-requests.diff-summary', [
                                'artist' => $record->artist,
                                'payload' => $record->payload,
                            ]) : '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->visible(fn ($record) => $record && $record->status === ApprovalStatus::Pending)
                    ->schema([
                        Textarea::make('review_notes')
                            ->label("Notes (visibles uniquement par l'administration)")
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Décision')
                    ->visible(fn ($record) => $record && ! $record->status->isPending())
                    ->schema([
                        TextEntry::make('reviewer')
                            ->label('Examiné par')
                            ->state(fn ($record) => $record?->reviewer->name ?? '—'),
                        TextEntry::make('reviewed_at')
                            ->label('Examiné le')
                            ->state(fn ($record) => $record?->reviewed_at?->translatedFormat('j F Y H:i') ?? '—'),
                        TextEntry::make('review_notes')
                            ->label('Notes')
                            ->state(fn ($record) => $record->review_notes ?? '—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
