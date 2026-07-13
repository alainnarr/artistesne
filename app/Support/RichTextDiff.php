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

        return $diff->build();
    }
}
