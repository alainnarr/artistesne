<?php

namespace App\Filament\Resources\Artists\Pages;

use App\Filament\Resources\Artists\ArtistResource;
use App\Mail\AdminContactMail;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class EditArtist extends EditRecord
{
    protected static string $resource = ArtistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPublicPage')
                ->label('Voir la page publique')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn () => route('public.artist.show', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record?->isPublished() ?? false),

            Action::make('contactArtist')
                ->label('Contacter l\'artiste')
                ->color('gray')
                ->icon('heroicon-o-envelope')
                ->visible(fn () => filled($this->record?->email ?? $this->record?->user?->email))
                ->schema([
                    TextInput::make('subject')->label('Sujet')->required(),
                    Textarea::make('body')->label('Message')->required()->rows(6),
                ])
                ->action(function (array $data): void {
                    $artist = $this->record;
                    $email = $artist->email ?? $artist->user?->email;
                    $name = $artist->name;

                    Mail::to($email)->send(new AdminContactMail(
                        recipientName: $name,
                        subject: $data['subject'],
                        body: $data['body'],
                    ));
                    Notification::make()->title('Message envoyé')->success()->send();
                }),

            DeleteAction::make(),
        ];
    }
}
