<?php

namespace App\Database\Traits;

use App\Database\Models\Audit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public const CREATE = 'C';
    public const DELETE = 'D';
    public const HARDDELETE = 'HD';
    public const RESTORE = 'R';
    public const UPDATE = 'U';

    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            $model->fillable = array_merge($model->fillable, self::transactionColumns());
            $model->attributes = array_merge($model->attributes, self::transactionColumnsAttributes(self::CREATE));
            $model->updated_at = null;
        });

        static::updating(function ($model) {
            if (isset($model->audit_action) && in_array($model->audit_action, [self::DELETE, self::RESTORE])) {
                return;
            }

            self::storeAuditable(self::UPDATE, $model);

            $model->fillable = array_merge($model->fillable, self::transactionColumns());
            $model->attributes = array_merge($model->attributes, self::transactionColumnsAttributes(self::UPDATE));
        });

        static::deleting(function ($model) {
            $usesSoftDeletes = in_array(
                \Illuminate\Database\Eloquent\SoftDeletes::class,
                class_uses_recursive(static::class)
            );

            if ($usesSoftDeletes && !$model->forceDeleting) {
                $action = self::DELETE;
                self::storeAuditable($action, $model);

                $model->audit_action = $action;
                    $model->fillable = array_merge($model->fillable, self::transactionColumns());
                    $model->attributes = array_merge(
                        $model->attributes,
                        self::transactionColumnsAttributes($action)
                    );
                    $model->save();
            } else {
                self::storeAuditable(self::HARDDELETE, $model);
            }
        });
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::restoring(function ($model) {
                self::storeAuditable(self::RESTORE, $model);

                $model->audit_action = self::RESTORE;
                $model->fillable = array_merge($model->fillable, self::transactionColumns());
                $model->attributes = array_merge($model->attributes, self::transactionColumnsAttributes(self::RESTORE));
            });
        }
    }

    public static function transactionColumns(): array
    {
        return [
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'audit_action',
            'audit_url'
        ];
    }

    protected static function transactionColumnsAttributes($action): array
    {
        $columns = [];
        switch ($action) {
            case self::CREATE:
                $columns['created_at'] = new Carbon();
                $columns['created_by'] = self::getUser();
                $columns['updated_at'] = null;
                $columns['updated_by'] = null;
                break;
            case self::RESTORE:
                $columns['deleted_at'] = null;
                $columns['deleted_by'] = null;
                $columns['updated_at'] = new Carbon();
                $columns['updated_by'] = self::getUser();
                break;
            case self::UPDATE:
                $columns['updated_at'] = new Carbon();
                $columns['updated_by'] = self::getUser();
                break;
            case self::DELETE:
                $columns['deleted_at'] = new Carbon();
                $columns['deleted_by'] = self::getUser();
                break;
        }
        $columns['audit_action'] = $action;
        $columns['audit_ip'] = self::getIp();
        $columns['audit_url'] = self::getUrl();

        return $columns;
    }

    protected static function getUser(): mixed
    {
        return Auth::user()?->id;
    }

    protected static function getUrl(): mixed
    {
        return $_SERVER['REQUEST_URI'] ?? null;
    }

    protected static function substringInArray($substring, array $array): bool
    {
        return array_reduce($array, function ($inArray, $item) use ($substring) {
            return $inArray || str_contains($item, $substring);
        }, false);
    }

    protected static function getIp(): string
    {
        try {
            $request = app(Request::class);

            $ip = $request->ip();

            return $ip ?: 'No IP';
        } catch (\Exception $e) {
            return 'No IP';
        }
    }

    private static function storeAuditable($action, $model): void
    {
        $tableName = self::getAuditableTableName($model);
        $data = $model->original;
        if ($tableName == 'audits') {
            $data = [
                'fk_table' => $model->table,
                'fk_id' => $model->id,
                'data' => json_encode($model->original),
            ];
            $data = array_merge($data, self::transactionColumnsAttributes(self::CREATE));
        }
        DB::table($tableName)->insert($data);

        if ($action === self::HARDDELETE) {
            if ($tableName !== 'audits') {
                $data['deleted_at'] = new Carbon();
            } else {
                $data['updated_at'] = new Carbon();
            }
            $data['audit_action'] = self::DELETE;
            DB::table($tableName)->insert($data);
        }
    }

    private static function getAuditableTableName($model): string
    {
        if ($model->getAuditable()) {
            return $model->getAuditable();
        } else {
            $audit = Schema::hasTable('_' . $model->getTable());
            if (($audit)) {
                return '_' . $model->getTable();
            } else {
                return 'audits';
            }
        }
    }
}
