<?php

namespace App\Models;

use Database\Factories\SearchSynonymFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchSynonym extends Model
{
    /** @use HasFactory<SearchSynonymFactory> */
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = ['term', 'synonyms', 'one_way'];

    protected $casts = [
        'synonyms' => 'array',
        'one_way' => 'boolean',
    ];
}
