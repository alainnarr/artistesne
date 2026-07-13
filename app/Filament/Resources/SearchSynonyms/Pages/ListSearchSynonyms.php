<?php

namespace App\Filament\Resources\SearchSynonyms\Pages;

use App\Filament\Resources\SearchSynonyms\SearchSynonymResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListSearchSynonyms extends ListRecords
{
    protected static string $resource = SearchSynonymResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncMeilisearch')
                ->label('Synchroniser Meilisearch')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Synchroniser les synonymes')
                ->modalDescription('Ceci pousse tous les synonymes de la base de données vers Meilisearch. Continuer ?')
                ->action(function (): void {
                    $exitCode = Artisan::call('scout:sync-synonyms');

                    if ($exitCode === 0) {
                        Notification::make()
                            ->title('Synonymes synchronisés avec succès.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Échec de la synchronisation. Vérifiez les logs.')
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
