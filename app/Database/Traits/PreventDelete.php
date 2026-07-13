<?php

namespace App\Database\Traits;

use Exception;

trait PreventDelete
{
    public static function bootPreventDelete(): void
    {
        static::deleting(function ($model) {
            throw new Exception(
                'It is not allowed to delete information from this table: ' . $model->getTable()
            );
        });
    }
}
