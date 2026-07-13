<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

/**
 * Custom Filament login page for the admin panel.
 *
 * Replaces the default email/password form with an immediate redirect to the
 * canton's AD FS identity provider via OIDC. Admins no longer authenticate
 * with a local password — all authentication is delegated to AD FS.
 */
class AdminLogin extends BaseLogin
{
    public function mount(): void
    {
        // If already authenticated and authorised, send to the dashboard.
        if (filament()->auth()->check()) {
            $this->redirect(filament()->getUrl(), navigate: false);

            return;
        }

        // Auto-redirect to AD FS by default so users with an active AD FS
        // session are signed in immediately.
        // Use ?manual=1 to render this page without auto-redirect.
        // Skipped entirely when ADFS_BASE_URL isn't configured (local dev
        // without a real AD FS instance) — redirecting would just fail.
        if (
            ! request()->boolean('manual')
            && ! session('errors')?->has('adfs')
            && filled(config('services.adfs.base_url'))
        ) {
            $this->redirect(route('admin.auth.redirect'), navigate: false);

            return;
        }

        // Keep the default form state initialized for Filament page lifecycle.
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        // Hide email/password fields: admin authentication is AD FS only.
        return $schema->components([]);
    }

    protected function getFormActions(): array
    {
        $actions = [
            Action::make('connectWithAdfs')
                ->label(__('Se connecter avec AD FS'))
                ->icon('heroicon-o-arrow-right-end-on-rectangle')
                ->url(route('admin.auth.redirect')),
        ];

        // Local-only shortcut: logs in an existing admin without a real AD FS
        // instance. Never available outside the local environment (route is
        // not even registered elsewhere — see routes/web.php).
        if (app()->environment('local')) {
            $actions[] = Action::make('fakeAdfsLogin')
                ->label(__('Connexion locale (démo, sans AD FS)'))
                ->color('gray')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(route('admin.auth.fake-login'));
        }

        return $actions;
    }

    public function getHeading(): string|HtmlString|null
    {
        return __('Connexion administration');
    }

    public function getSubheading(): string|HtmlString|null
    {
        $adfsError = session('errors')?->first('adfs');

        if (filled($adfsError)) {
            return new HtmlString('<span class="text-danger-600">'.e($adfsError).'</span>');
        }

        return __('Utilisez votre compte cantonal (AD FS) pour acceder a l\'administration.');
    }
}
