<?php

namespace App\Filament\Resources\ArtistRegistrationRequests\Pages;

use App\Actions\ApproveRegistrationRequest;
use App\Actions\RejectRegistrationRequest;
use App\Filament\Resources\ArtistRegistrationRequests\ArtistRegistrationRequestResource;
use App\Mail\AdminContactMail;
use App\Models\ArtistRegistrationRequest;
use App\Support\ReviewMessageTemplates;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditArtistRegistrationRequest extends EditRecord
{
    protected static string $resource = ArtistRegistrationRequestResource::class;

    public function getTitle(): string
    {
        return 'Examiner la demande';
    }

    protected function getHeaderActions(): array
    {
        /** @var ArtistRegistrationRequest $record */
        $record = $this->getRecord();

        if (! $record->status->isPending()) {
            return [];
        }

        return [
            Action::make('approve')
                ->label('Approuver et créer le compte')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Approuver la demande')
                ->modalDescription('Un compte artiste sera créé et un lien de connexion lui sera envoyé par e-mail.')
                ->schema([
                    Textarea::make('notes')->label('Message interne (optionnel)')->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    $this->approve($record, $data['notes'] ?? null);
                }),

            Action::make('requestChanges')
                ->label('Demander des modifications')
                ->color('info')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Select::make('template')
                        ->label('Modèle de message')
                        ->options(ReviewMessageTemplates::options())
                        ->placeholder('— Choisir un modèle (optionnel) —')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set): void {
                            $templates = ReviewMessageTemplates::registration();

                            if ($state && isset($templates[$state])) {
                                $set('notes', $templates[$state]);
                            }
                        }),
                    Textarea::make('notes')->label('Message à transmettre')->required()->rows(6),
                ])
                ->action(function (array $data) use ($record): void {
                    $record->requestChanges(auth()->user(), $data['notes']);
                    Notification::make()->title('Modifications demandées')->success()->send();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            Action::make('reject')
                ->label('Refuser')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('notes')->label('Motif (optionnel)')->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    app(RejectRegistrationRequest::class)($record, auth()->user(), $data['notes'] ?? null);

                    Notification::make()->title('Demande refusée')->success()->send();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            Action::make('contactApplicant')
                ->label('Contacter le demandeur')
                ->color('gray')
                ->icon('heroicon-o-envelope')
                ->schema([
                    TextInput::make('subject')->label('Sujet')->required(),
                    Textarea::make('body')->label('Message')->required()->rows(6),
                ])
                ->action(function (array $data) use ($record): void {
                    Mail::to($record->email)->send(new AdminContactMail(
                        recipientName: $record->artist_name,
                        subject: $data['subject'],
                        body: $data['body'],
                    ));
                    Notification::make()->title('Message envoyé')->success()->send();
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function approve(ArtistRegistrationRequest $request, ?string $notes): void
    {
        app(ApproveRegistrationRequest::class)($request, auth()->user(), $notes);

        Notification::make()
            ->title('Demande approuvée')
            ->body('Le compte artiste a été créé et un lien de connexion a été envoyé.')
            ->success()
            ->send();

        $this->redirect(static::getResource()::getUrl('index'));
    }
}
