<?php

namespace App\Providers;

use App\Socialite\AdfsProvider;
use Carbon\CarbonImmutable;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerSocialiteDrivers();
        $this->configureMailOverrides();
    }

    /**
     * Register custom Socialite OAuth2 drivers.
     */
    protected function registerSocialiteDrivers(): void
    {
        $socialite = $this->app->make(SocialiteFactory::class);

        $socialite->extend('adfs', function () use ($socialite) {
            $config = config('services.adfs');

            return $socialite->buildProvider(AdfsProvider::class, $config)
                ->setBaseUrl((string) $config['base_url']);
        });
    }

    /**
     * Configure mail overrides for staging environments (redirect + subject prefix).
     */
    protected function configureMailOverrides(): void
    {
        $redirectTo = config('mail.to.address');
        $subjectPrefix = config('mail.subject_prefix');

        if (! $redirectTo && ! $subjectPrefix) {
            return;
        }

        Event::listen(MessageSending::class, function (MessageSending $event) use ($redirectTo, $subjectPrefix): void {
            if ($subjectPrefix) {
                $event->message->subject($subjectPrefix.$event->message->getSubject());
            }

            if ($redirectTo) {
                $event->message->to(new Address($redirectTo));
                $event->message->cc();
                $event->message->bcc();
            }
        });
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
