<?php

namespace Tests\Unit\Database\Traits;

use App\Database\Traits\PreventUpdate;
use Exception;
use App\Database\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Database\Schemas\Table;
use Tests\TestCase;

class PreventUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('prevent_update_test');
        Table::make('prevent_update_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('dont_update');
        });
    }
    protected function getModel()
    {
        return new class extends Model {
            use PreventUpdate;

            protected $table = 'prevent_update_test';
            protected $fillable = [
                'name',
                'dont_update'
            ];
            protected $updatable = [
                'name',
            ];
        };
    }

    public function testCreateIsAllowed(): void
    {
        $model = $this->getModel()->create(['name' => 'Created', 'dont_update' => 'initial_value']);
        $this->assertEquals('Created', $model->name);
    }

    public function testUpdateCanUpdateColumn(): void
    {
        $model = $this->getModel()->create(['name' => 'Original', 'dont_update' => 'initial_value']);
        $model->update(['name' => 'New']);
        $this->assertEquals('New', $model->name);
    }

    public function testUpdateThrowsException(): void
    {
        $model = $this->getModel()->create(['name' => 'Original', 'dont_update' => 'initial_value']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('It is not allowed to update information in this table: prevent_update_test');
        $model->update(['dont_update' => 'Updated']);
    }

    public function testSaveWithDirtyAttributesThrowsException(): void
    {
        $model = $this->getModel()->create(['name' => 'Initial', 'dont_update' => 'initial_value']);
        $model->dont_update = 'Changed';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('It is not allowed to update information in this table: prevent_update_test');
        $model->save();
    }
}
