<?php

namespace App\Database\Traits;

use Exception;

trait PreventUpdate
{
    use Auditable;

    public static function bootPreventUpdate(): void
    {
        static::updating(function ($model) {
            if (!self::canUpdateColumns($model)) {
                throw new Exception(
                    'It is not allowed to update information in this table: ' . $model->getTable() .
                    ' - columns: ' . implode(', ', self::getUpdatingColumns($model))
                );
            }
        });
    }

    private static function canUpdateColumns($model): bool
    {
        $keyChanges = self::getUpdatingColumns($model);
        if (empty($keyChanges)) {
            return true;
        }
        return false;
    }
    private static function getUpdatingColumns($model): array
    {
        $arrayKeyChanges = array_keys(array_diff_assoc($model->attributes, $model->original));
        $changeableKeys = array_merge(self::transactionColumns(), $model->updatable);
        return array_diff($arrayKeyChanges, $changeableKeys);
    }
}
