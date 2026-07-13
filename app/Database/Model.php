<?php

namespace App\Database;

use App\Database\Traits\Auditable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Model extends EloquentModel
{
    use Auditable;

    protected $table = '';

    protected $auditable = '';

    protected $fillable = [];
    protected $updatable = [];

    protected $hidden = [
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'audit_action',
        'audit_url'
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        return [];
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /* * * * * * * * UTILS * * * * * * * */
    public function getTable(): string
    {
        return $this->table;
    }
    public function getAuditable(): string
    {
        return $this->auditable;
    }
    public function getFillable(): array
    {
        return $this->fillable;
    }
    public function getUpdatable(): array
    {
        return $this->updatable;
    }
    /* * * * * * * * END - UTILS * * * * * * * */
}
