<?php

namespace App\Database\Schemas;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Table extends Blueprint
{
    public static function make($tableName, $tableColumns)
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new Table($table, $callback);
        });

        $schema->create($tableName, function ($table) use ($tableColumns) {
            $tableColumns($table);
            Table::defaultColumns($table);
        });
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
            $parameters['type'] = 'string';
            $parameters['length'] = 255;
            $this->index($column);

            return $this->addColumn($parameters['type'], $column, $parameters);
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
                $parameters['length'] = $length ?? 255;
                break;
            default:
                throw new InvalidArgumentException('Type: '.$type.' not accepted for foreign key');
        }

        $foreignKey = $this->addColumn($type, $column, $parameters);
        $this->foreign($column)->references($fk_column)->on($fk_table);

        return $foreignKey;
    }

    public static function defaultColumns($table)
    {
        $table->timestamps();
        $table->unsignedBigInteger('created_by')->nullable(); // User id
        $table->unsignedBigInteger('updated_by')->nullable(); // User id
        $table->char('audit_action', 1)->nullable(); // Create, Update, Delete, Select
        $table->text('audit_url')->nullable(); // URL
        $table->ipAddress('audit_ip')->nullable(); // IP

        foreach ($table->columns as $column) {
            if ($column->name === 'deleted_at') {
                $table->unsignedBigInteger('deleted_by')->nullable(); // User id
                $table->index('deleted_by');
            }
        }
    }
}
