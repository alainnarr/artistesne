<?php

namespace App\Database\Schemas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Audit extends Blueprint
{
    public static function make($tableName, $tableColumns)
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new Audit($table, $callback);
        });

        $schema->create('_'.$tableName, function ($table) use ($tableColumns) {
            $table->bigIncrements('_id');
            $tableColumns($table);
            $hasDeletedAt = false;
            foreach ($table->columns as $column) {
                $column->nullable();
                if ($column->name === 'deleted_at') {
                    $hasDeletedAt = true;
                }
            }

            Table::defaultColumns($table);

            if (! $hasDeletedAt) {
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable(); // User id
            }
        });
    }

    public function bigIncrements($column)
    {
        if ($column === '_id') {
            return parent::bigIncrements($column);
        }

        return parent::unsignedBigInteger($column);
    }

    public function unique($columns, $name = null, $algorithm = null)
    {
        return new IndexDefinition([]);
    }

    public function foreignId($column)
    {
        return parent::foreignId($column);
    }

    public function id($column = 'id')
    {
        return parent::unsignedBigInteger($column);
    }

    public function foreignUlid($column, $length = 26)
    {
        return parent::foreignUlid($column, $length);
    }

    public function foreignIdFor($model, $column = null): ForeignIdColumnDefinition
    {
        if (! ($model instanceof Model) && ! (is_string($model) && is_subclass_of($model, Model::class))) {
            throw new InvalidArgumentException('foreignIdFor expects an Eloquent Model class or instance.');
        }

        return parent::foreignIdFor($model, $column);
    }

    public function foreignUuid($column)
    {
        return parent::foreignUuid($column);
    }

    public function foreign($columns, $name = null)
    {
        return new ForeignKeyDefinition([]);
    }

    public function foreignKey(
        string $column,
        string $fk_table,
        string $fk_column,
        string $type,
        ?int $length = null,
        $parameters = []
    ): ColumnDefinition {
        switch ($type) {
            case 'bigInteger':
            case 'integer':
            case 'mediumInteger':
            case 'smallInteger':
            case 'tinyInteger':
                $parameters['unsigned'] = true;
                $parameters['autoIncrement'] = false;
                break;
            case 'uuid':
                $parameters['length'] = 36;
                break;
            case 'char':
            case 'string':
                $parameters['length'] = $length ?? 100;
                break;
            default:
                throw new InvalidArgumentException('Type: '.$type.' not accepted for foreign key');
        }

        $foreignKey = $this->addColumn($type, $column, $parameters);

        return $foreignKey;
    }

    public function enumeration(string $column, string $type, $parameters = []): ColumnDefinition
    {
        switch ($type) {
            case 'int':
                $parameters['type'] = 'integer';
                $parameters['unsigned'] = true;
                $parameters['autoIncrement'] = false;
                break;
            case 'boolean':
                $parameters['type'] = 'tinyInteger';
                $parameters['unsigned'] = true;
                $parameters['autoIncrement'] = false;
                break;
            case 'string':
                $parameters['type'] = 'string';
                $parameters['length'] = 255;
                break;
            default:
                throw new InvalidArgumentException(
                    'Type: '.$type.' not accepted for enumeration. Only accepts int, boolean, string'
                );
        }

        return $this->addColumn($parameters['type'], $column, $parameters);
    }

    public function repository(string $column, bool $multiple = false, $parameters = []): ColumnDefinition
    {
        if ($multiple) {
            return $this->foreignKey(
                $column,
                'repositories',
                'code',
                'string',
                255,
                $parameters
            );
        } else {
            return $this->foreignKey(
                $column,
                'repositories',
                'id',
                'bigInteger',
                null,
                $parameters
            );
        }
    }
}
