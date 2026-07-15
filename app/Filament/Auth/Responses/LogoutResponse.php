<?php

namespace App\Filament\Auth\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $loginUrl = route('filament.admin.auth.login', ['manual' => 1]);
        $adfsBaseUrl = rtrim((string) config('services.adfs.base_url'), '/');

        if ($adfsBaseUrl === '') {
            return redirect()->to($loginUrl);
        }

        $query = http_build_query([
            'post_logout_redirect_uri' => $loginUrl,
        ]);

        return redirect()->away("{$adfsBaseUrl}/adfs/oauth2/logout?{$query}");
    }
}
