<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Database\Models\Repository;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Marks a model as having a polymorphic repositories relation.
 * Implement this on any model that owns Repository records.
 */
interface RepositoryableContract
{
    public function repositories(): MorphMany;
}
