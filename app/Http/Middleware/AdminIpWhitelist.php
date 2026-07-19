<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict access to the admin panel by IP address / CIDR range.
 *
 * When APP_ADMIN_IP_WHITELIST is empty, all traffic passes through (opt-in).
 * When non-empty, only IPs matching the comma-separated list of exact IPs or
 * CIDR ranges (e.g. 148.196.0.0/16,10.0.0.0/8) can reach admin routes.
 * Everyone else receives a 404 (not a 403) to avoid revealing the panel's existence.
 *
 * The /up health endpoint is always exempt.
 */
class AdminIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $ranges = (array) config('app.admin_ip_whitelist', []);

        // Feature is opt-in — allow all admin traffic when no whitelist is configured.
        if (empty($ranges)) {
            return $next($request);
        }

        // Health endpoint always passes through.
        if ($request->is('up')) {
            return $next($request);
        }

        $ip = $this->resolveClientIp($request);

        if ($this->isWhitelisted($ip, $ranges)) {
            return $next($request);
        }

        abort(404);
    }

    /**
     * Resolve the real originating client IP.
     *
     * Behind trusted reverse proxies the leftmost IP in X-Forwarded-For is the
     * original client. Falls back to $request->ip() when no proxy is configured.
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
     * @param  array<int, string>  $ranges  Exact IPs or CIDR strings (e.g. '148.196.0.0/16').
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

        $mask = (int) $prefix;
        if ($mask < 0 || $mask > 32) {
            return false;
        }

        $netmask = $mask === 0 ? 0 : (~0 << (32 - $mask));

        return ($ipLong & $netmask) === ($subnetLong & $netmask);
    }
}
