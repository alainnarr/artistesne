<?php

namespace App\Models;

use Database\Factories\TaxonomyTermFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxonomyTerm extends Model
{
    /** @use HasFactory<TaxonomyTermFactory> */
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = ['domain', 'type', 'name', 'slug', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Artistic domains, keyed by their label (the value actually persisted on
     * `Artist::$discipline` / `$secondary_discipline`), ordered by position.
     *
     * @return array<string, string>
     */
    public static function domainOptions(): array
    {
        return static::query()
            ->where('type', 'domain')
            ->orderBy('position')
            ->pluck('name', 'name')
            ->all();
    }

    /**
     * Artistic domains, keyed by their stable slug (used to scope
     * `main_activities` / `secondary_activities` / `keywords` terms to a
     * parent domain via the `domain` column, and by the registration form
     * before the label is known).
     *
     * @return array<string, string>
     */
    public static function domainSlugOptions(): array
    {
        return static::query()
            ->where('type', 'domain')
            ->orderBy('position')
            ->pluck('name', 'slug')
            ->all();
    }
}
