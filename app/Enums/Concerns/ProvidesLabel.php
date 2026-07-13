<?php

namespace App\Enums\Concerns;

/**
 * Provides the Filament `HasLabel::getLabel()` implementation by delegating to
 * a `label()` method defined on the enum, avoiding a duplicated wrapper in
 * every enum.
 */
trait ProvidesLabel
{
    public function getLabel(): string
    {
        return $this->label();
    }
}
