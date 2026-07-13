<?php

namespace App\Enums\Concerns;

/**
 * Provides the Filament `HasColor::getColor()` implementation by delegating to
 * a `color()` method defined on the enum, avoiding a duplicated wrapper in
 * every enum.
 */
trait ProvidesColor
{
    /**
     * @return string|array<int, string>|null
     */
    public function getColor(): string|array|null
    {
        return $this->color();
    }
}
