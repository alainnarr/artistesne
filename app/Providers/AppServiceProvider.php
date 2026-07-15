<?php

namespace App\Providers;

use App\Filament\Auth\Responses\LogoutResponse as FilamentLogoutResponse;
use Carbon\CarbonImmutable;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FilamentLogoutResponseContract::class, FilamentLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMailOverrides();
        $this->configureMailLogging();
    }

    /**
     * Configure mail overrides for staging environments (redirect + subject prefix).
     */
    protected function configureMailOverrides(): void
    {
        $redirectAddresses = config('mail.to.addresses', []);
        $redirectName = config('mail.to.name');
        $subjectPrefix = config('mail.subject_prefix');

        if (empty($redirectAddresses) && ! $subjectPrefix) {
            return;
        }

        Event::listen(MessageSending::class, function (MessageSending $event) use ($redirectAddresses, $redirectName, $subjectPrefix): void {
            if ($subjectPrefix) {
                $event->message->subject($subjectPrefix.$event->message->getSubject());
            }

            if (! empty($redirectAddresses)) {
                $event->message->to(...array_map(
                    fn (string $address): Address => new Address($address, $redirectName),
                    $redirectAddresses
                ));
                $event->message->cc();
                $event->message->bcc();
            }
        });
    }

    /**
     * Log every outgoing mail attempt and its SMTP acceptance, so delivery
     * issues (silent failures, misconfigured relay, empty recipient lists)
     * show up in the logs instead of just "the email never arrived".
     */
    protected function configureMailLogging(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event): void {
            Log::info('Mail sending', [
                'to' => $this->addressesToStrings($event->message->getTo()),
                'cc' => $this->addressesToStrings($event->message->getCc()),
                'bcc' => $this->addressesToStrings($event->message->getBcc()),
                'subject' => $event->message->getSubject(),
                'mailer' => config('mail.default'),
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            Log::info('Mail sent', [
                'to' => $this->addressesToStrings($event->message->getTo()),
                'subject' => $event->message->getSubject(),
                'message_id' => $event->message->getHeaders()->get('Message-ID')?->getBodyAsString(),
            ]);
        });
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return array<int, string>
     */
    protected function addressesToStrings(array $addresses): array
    {
        return array_map(fn (Address $address): string => $address->toString(), $addresses);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
