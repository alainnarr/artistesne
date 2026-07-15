<?php

declare(strict_types=1);

namespace App\Filament\Resources\Registrations\Pages;

use App\Actions\ApproveRegistration;
use App\Actions\RejectRegistration;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\Registrations\RegistrationResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRegistration extends ViewRecord
{
    protected static string $resource = RegistrationResource::class;

    public function getTitle(): string
    {
        return "Examiner l'inscription";
    }

    protected function getHeaderActions(): array
    {
        /** @var Registration $record */
        $record = $this->getRecord();

        if ($record->enum_status !== RegistrationStatus::OPEN) {
            return [];
        }

        return [
            Action::make('approve')
                ->label('Approuver et créer le compte')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading("Approuver l'inscription")
                ->modalDescription('Un compte artiste sera créé et un lien de connexion lui sera envoyé par e-mail.')
                ->schema([
                    Textarea::make('notes')->label('Message interne (optionnel)')->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    app(ApproveRegistration::class)($record, auth()->user(), $data['notes'] ?? null);

                    Notification::make()
                        ->title('Inscription approuvée')
                        ->body('Le compte artiste a été créé et un lien de connexion a été envoyé.')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            Action::make('reject')
                ->label('Refuser')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading("Refuser l'inscription")
                ->schema([
                    Textarea::make('notes')->label('Motif (optionnel)')->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    app(RejectRegistration::class)($record, auth()->user(), $data['notes'] ?? null);

                    Notification::make()->title('Demande refusée')->success()->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
