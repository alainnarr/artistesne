<?php

declare(strict_types=1);

namespace App\Database\Models;

use Database\Factories\SearchSynonymFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchSynonym extends Model
{
    /** @use HasFactory<SearchSynonymFactory> */
    use HasFactory;

    protected static function newFactory(): SearchSynonymFactory
    {
        return SearchSynonymFactory::new();
    }

    /** @var list<string> */
    protected $fillable = ['term', 'synonyms', 'one_way'];

    protected $casts = [
        'synonyms' => 'array',
        'one_way' => 'boolean',
    ];
}
