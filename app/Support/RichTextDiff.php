<?php

namespace App\Support;

use Caxy\HtmlDiff\HtmlDiff;

class RichTextDiff
{
    /**
     * Compute an HTML diff between two HTML strings.
     */
    public static function html(?string $old, ?string $new): string
    {
        $diff = new HtmlDiff((string) $old, (string) $new);
        $diff->getConfig()->setPurifierCacheLocation(self::purifierCacheLocation());

        return $diff->build();
    }

    /**
     * HTMLPurifier needs a writable directory to serialize its definition
     * cache. The vendor directory is not writable in most deployments
     * (read-only releases), so we point it at a storage path instead.
     */
    private static function purifierCacheLocation(): string
    {
        $path = function_exists('storage_path') && app()->bound('path.storage')
            ? storage_path('app/htmlpurifier')
            : sys_get_temp_dir().'/htmlpurifier';

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }
}
