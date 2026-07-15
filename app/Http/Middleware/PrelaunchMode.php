<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrelaunchMode
{
    /**
     * Three-tier access gate controlled by APP_PRELAUNCH_MODE:
     *
     *   'off'    — No restriction. All traffic passes through.
     *
     *   'strict' — PrePreLaunch: only IPs within APP_PRELAUNCH_IP_WHITELIST
     *              get through. Everyone else sees the coming-soon page (503).
     *
     *   'v1'     — PreLaunch: whitelisted IPs have full access; all others may
     *              only reach the paths listed in `prelaunch_allowed_paths`
     *              (artist registration, artist portal, admin). Anything else
     *              returns the coming-soon page (503).
     *
     * The /up health endpoint always bypasses this check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = (string) config('app.prelaunch_mode', 'off');

        if ($mode === 'off') {
            return $next($request);
        }

        // Health endpoint always passes through.
        if ($request->is('up')) {
            return $next($request);
        }

        // Whitelisted IPs always get unrestricted access.
        $ranges = (array) config('app.prelaunch_ip_whitelist', []);

        if ($this->isWhitelisted($this->resolveClientIp($request), $ranges)) {
            return $next($request);
        }

        // In v1 mode, a limited set of paths is open to everyone.
        if ($mode === 'v1') {
            $allowedPaths = (array) config('app.prelaunch_allowed_paths', []);

            if ($request->is($allowedPaths)) {
                return $next($request);
            }
        }

        // strict mode: all non-whitelisted traffic → coming-soon.
        // v1 mode:     non-whitelisted + non-allowed path → coming-soon.
        return response()
            ->view('coming-soon', [], 503)
            ->header('Retry-After', '86400');
    }

    /**
     * Resolve the real originating client IP.
     *
     * Behind multi-hop proxies (e.g. client → cantonal proxy → nginx → PHP),
     * $request->ip() may resolve to an intermediate proxy. The leftmost IP in
     * X-Forwarded-For is always the original client, so we prefer that when
     * APP_TRUSTED_PROXIES is set (meaning the proxy chain is known and trusted).
     */
    private function resolveClientIp(Request $request): string
    {
        if (config('app.trusted_proxies') && $request->header('X-Forwarded-For')) {
            $ips = array_map('trim', explode(',', (string) $request->header('X-Forwarded-For')));

            return $ips[0];
        }

        return (string) $request->ip();
    }

    /**
     * @param  array<int, string>  $ranges  CIDR strings (e.g. '148.196.0.0/16') or exact IPs.
     */
    private function isWhitelisted(string $ip, array $ranges): bool
    {
        if ($ip === '') {
            return false;
        }

        foreach ($ranges as $range) {
            $range = trim($range);
            if ($range === '') {
                continue;
            }

            if (str_contains($range, '/')) {
                if ($this->ipInCidr($ip, $range)) {
                    return true;
                }
            } elseif ($ip === $range) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether an IPv4 address falls within a CIDR range.
     * IPv6 exact-match is handled by the caller; CIDR for IPv6 is not supported.
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $prefix] = explode('/', $cidr, 2);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $bits = (int) $prefix;
        $mask = $bits === 0 ? 0 : (~0 << (32 - $bits));

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
