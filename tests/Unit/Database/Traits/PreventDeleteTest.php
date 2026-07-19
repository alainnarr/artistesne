<?php

namespace Tests\Unit\Database\Traits;

use App\Database\Schemas\Table;
use App\Database\Traits\PreventDelete;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PreventDeleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('prevent_delete_test');

        Table::make('prevent_delete_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    protected function getModel()
    {
        return new class extends Model
        {
            use PreventDelete;

            protected $table = 'prevent_delete_test';

            protected $fillable = [
                'name',
            ];
        };
    }

    public function test_create_is_allowed(): void
    {
        $model = $this->getModel()->create(['name' => 'Created']);
        $this->assertEquals('Created', $model->name);
    }

    public function test_delete_throws_exception(): void
    {
        $model = $this->getModel()->create(['name' => 'ToDelete']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('It is not allowed to delete information from this table: prevent_delete_test');

        $model->delete();
    }

    public function test_force_delete_throws_exception(): void
    {
        $model = $this->getModel()->create(['name' => 'ToDelete']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('It is not allowed to delete information from this table: prevent_delete_test');
        $model->forceDelete();
    }
}
