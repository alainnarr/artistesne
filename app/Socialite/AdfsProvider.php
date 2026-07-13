<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

/**
 * Socialite driver for an on-premise AD FS instance using OpenID Connect.
 *
 * Required .env variables:
 *   ADFS_BASE_URL        – e.g. https://adfs.ne.ch
 *   ADFS_CLIENT_ID       – Relying Party client identifier
 *   ADFS_CLIENT_SECRET   – Client secret (confidential client)
 *   ADFS_REDIRECT_URI    – Must match the redirect registered in AD FS
 *   ADFS_ALLOWED_GROUP   – (optional) AD group name that grants admin access
 */
class AdfsProvider extends AbstractProvider
{
    /**
     * The AD FS base URL, e.g. https://adfs.ne.ch
     */
    protected string $adfsBaseUrl = '';

    /**
     * The scopes being requested.
     *
     * @var array<int, string>
     */
    protected $scopes = ['openid', 'profile', 'email'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * Stateless mode: skip the session-stored state check.
     *
     * Using a confidential client (client_secret) means the authorization code
     * can only be exchanged server-side, which provides equivalent CSRF protection
     * without relying on session persistence across the AD FS redirect.
     *
     * @var bool
     */
    protected $stateless = true;

    /** {@inheritDoc} */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            $this->baseUrl('/adfs/oauth2/authorize'),
            $state,
        );
    }

    /** {@inheritDoc} */
    protected function getTokenUrl(): string
    {
        return $this->baseUrl('/adfs/oauth2/token');
    }

    /** {@inheritDoc} */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            $this->baseUrl('/adfs/userinfo'),
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ],
        );

        return json_decode((string) $response->getBody(), associative: true);
    }

    /** {@inheritDoc} */
    protected function mapUserToObject(array $user): User
    {
        $name = $user['name']
            ?? trim(($user['given_name'] ?? '').' '.($user['family_name'] ?? ''))
            ?: null;

        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'sub'),
            'name' => $name,
            // AD FS may expose the address as 'email', 'upn', or 'unique_name'.
            'email' => Arr::get($user, 'email')
                ?? Arr::get($user, 'upn')
                ?? Arr::get($user, 'unique_name'),
        ]);
    }

    /**
     * Set the AD FS base URL and return the provider for chaining.
     * Called by AppServiceProvider after buildProvider().
     */
    public function setBaseUrl(string $url): static
    {
        $this->adfsBaseUrl = rtrim($url, '/');

        return $this;
    }

    /**
     * Return an absolute AD FS endpoint URL.
     */
    private function baseUrl(string $path): string
    {
        return $this->adfsBaseUrl.$path;
    }
}
