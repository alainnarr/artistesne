<?php

namespace App\Filament\Resources\ArtistChangeRequests\Schemas;

use App\Enums\ApprovalStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
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
                            Placeholder::make('artist')
                                ->label('Artiste')
                                ->content(fn ($record) => $record?->artist?->name),
                            Placeholder::make('submitter')
                                ->label('Soumis par')
                                ->content(fn ($record) => $record?->submitter?->name ?? '—'),
                            Placeholder::make('created_at')
                                ->label('Reçue')
                                ->content(fn ($record) => $record?->created_at?->translatedFormat('j F Y H:i') ?? '—'),
                        ]),
                        Placeholder::make('status')
                            ->label('Statut')
                            ->content(fn ($record) => $record?->status?->label() ?? '—'),
                    ]),

                Section::make('Modifications proposées')
                    ->schema([
                        Placeholder::make('changes')
                            ->hiddenLabel()
                            ->content(fn ($record) => $record ? view('filament.change-requests.diff-summary', [
                                'artist' => $record->artist,
                                'payload' => $record->payload,
                            ]) : '—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->visible(fn ($record) => $record && $record->status === ApprovalStatus::Pending)
                    ->schema([
                        Textarea::make('review_notes')
                            ->label('Notes (visibles uniquement par l\'administration)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Décision')
                    ->visible(fn ($record) => $record && ! $record->status->isPending())
                    ->schema([
                        Placeholder::make('reviewer')
                            ->label('Examiné par')
                            ->content(fn ($record) => $record?->reviewer?->name ?? '—'),
                        Placeholder::make('reviewed_at')
                            ->label('Examiné le')
                            ->content(fn ($record) => $record?->reviewed_at?->translatedFormat('j F Y H:i') ?? '—'),
                        Placeholder::make('review_notes')
                            ->label('Notes')
                            ->content(fn ($record) => $record?->review_notes ?? '—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
