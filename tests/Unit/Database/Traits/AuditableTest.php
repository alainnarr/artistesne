<?php

namespace Tests\Unit\Database\Traits;

use App\Database\Schemas\Audit;
use App\Database\Schemas\Table;
use App\Database\Traits\Auditable;
use Carbon\CarbonInterface;
use App\Database\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Http\Request;

class AuditableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrate();
    }

    private function migrate()
    {
        Schema::dropIfExists('auditable_test');
        Schema::dropIfExists('_auditable_test');
        Schema::dropIfExists('notauditable_test');
        Schema::dropIfExists('transactable_test');
        Table::make('auditable', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
        });
        Audit::make('auditable', function (&$table) {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
        });
        Table::make('notauditable', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });
        Table::make('transactable_test', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
        });
    }

    protected function getModel()
    {
        return new class extends Model {
            use Auditable;
            use SoftDeletes;

            protected $table = 'transactable_test';
            protected $guarded = [];
            public $timestamps = true;
            public $dates = ['deleted_at'];
        };
    }

    protected function getModelAuditable()
    {
        return new class extends Model {
            use SoftDeletes;
            use Auditable;

            protected $table = 'auditable';
            protected $auditable = '_auditable';
        };
    }

    protected function getModelNotAuditable()
    {
        return new class extends Model {
            use Auditable;

            protected $table = 'notauditable';
        };
    }

    public function testReturnsCustomAuditableTable()
    {
        $model = $this->getModelAuditable();

        $auditable = $model->getAuditable();
        $this->assertEquals('_auditable', $auditable);
    }

    public function testReturnsPrefixedTableWhenExists()
    {
        $model = $this->getModelAuditable();
        $table = $model->getAuditable();

        $this->assertEquals('_auditable', $table);
    }

    public function testReturnsDefaultAuditsWhenNoTableFound()
    {
        $model = $this->getModelNotAuditable();

        $table = $this->invokeMethod(Auditable::class, 'getAuditableTableName', [$model]);
        $this->assertEquals('audits', $table);
    }

    public function testReturnsCustomAuditableTableNotExplicitlySet()
    {
        $model = new class extends Model {
            use SoftDeletes;
            use Auditable;

            protected $table = 'auditable';
        };

        $table = $this->invokeMethod(Auditable::class, 'getAuditableTableName', [$model]);
        $this->assertEquals('_auditable', $table);
    }

    public function testStoreAuditableInsertsIntoCustomTable()
    {
        $model = $this->getModelAuditable();
        $model->name = 'Name';
        $model->save();

        $model->name = 'New Name';
        $model->save();

        $audit = DB::table('_auditable')->first();

        $this->assertNotNull($audit);
        $this->assertEquals($model->id, $audit->id);
        $this->assertEquals('C', $audit->audit_action);
    }

    public function testStoreAuditableInsertsIntoAuditsTable()
    {
        $model = $this->getModelNotAuditable();
        $model->name = 'Name';
        $model->save();

        $model->name = 'New Name';
        $model->save();

        $audit = DB::table('audits')->first();

        $this->assertNotNull($audit);
        $this->assertEquals('notauditable', $audit->fk_table);
        $this->assertEquals($model->id, $audit->fk_id);
        $this->assertEquals('C', $audit->audit_action);
    }

    public function testStoreAuditableDeleteIntoCustomTable()
    {
        $model = $this->getModelAuditable();
        $model->name = 'Name';
        $model->save();
        $model->delete();

        $audit = DB::table('_auditable')->first();
        $audits = DB::table('_auditable')->get();
        //dd('Model', $model, 'Audit', $audit, 'Audits', $audits);

        $this->assertNotNull($audit);
        $this->assertEquals($model->id, $audit->id);
        $this->assertEquals('C', $audit->audit_action);
    }

    public function testStoreAuditableHarddeleteIntoCustomTable()
    {
        $model = $this->getModelAuditable();
        $model->name = 'Name';
        $model->save();
        $model->forceDelete();

        $audit = DB::table('_auditable')->latest('_id')->first();

        $this->assertNotNull($audit);
        $this->assertEquals($model->id, $audit->id);
        $this->assertEquals('D', $audit->audit_action);
    }

    public function testStoreAuditableDeleteIntoAuditsTable()
    {
        $model = $this->getModelNotAuditable();
        $model->name = 'Name';
        $model->save();
        $model->delete();

        $audit = DB::table('audits')->first();

        $this->assertNotNull($audit);
        $this->assertEquals('notauditable', $audit->fk_table);
        $this->assertEquals($model->id, $audit->fk_id);

        $this->assertEquals('C', $audit->audit_action);
    }

    public function testStoreAuditableHarddeleteIntoAuditsTable()
    {
        $model = $this->getModelNotAuditable();
        $model->name = 'Name';
        $model->save();
        $model->delete();

        $audit = DB::table('audits')->latest('id')->first();

        $this->assertNotNull($audit);
        $this->assertEquals('notauditable', $audit->fk_table);
        $this->assertEquals($model->id, $audit->fk_id);
    }

    private function invokeMethod($class, $methodName, array $parameters = [])
    {
        $ref = new \ReflectionClass($class);
        $method = $ref->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $parameters);
    }

    public function testCreatingFillsTransactionColumns(): void
    {
        $model = $this->getModel()->create(['name' => 'Test Create']);

        $this->assertEquals('Test Create', $model->name);
        $this->assertEquals('C', $model->audit_action);
        $this->assertEquals(null, $model->created_by);
        $this->assertNull($model->updated_at);
        $this->assertNull($model->updated_by);
    }

    public function testUpdatingFillsTransactionColumns(): void
    {
        $model = $this->getModel()->create(['name' => 'Initial']);
        $model->name = 'Updated Name';
        $model->save();

        $this->assertEquals('U', $model->audit_action);
        $this->assertEquals(null, $model->updated_by);
        $this->assertInstanceOf(CarbonInterface::class, $model->updated_at);
    }

    public function testDeletingSetsAuditColumns(): void
    {
        $model = $this->getModel()->create(['name' => 'To Delete']);
        $model->delete();

        $fresh = $this->getModel()->withTrashed()->find($model->id);
        $this->assertEquals('D', $fresh->audit_action);
        $this->assertInstanceOf(CarbonInterface::class, $fresh->deleted_at);
        $this->assertEquals(null, $fresh->deleted_by);
    }

    public function testRestoringSetsAuditColumns(): void
    {
        $model = $this->getModel()->create(['name' => 'To Restore']);
        $model->delete();
        $model->restore();

        $fresh = $this->getModel()->find($model->id);
        $this->assertEquals('R', $fresh->audit_action);
        $this->assertNull($fresh->deleted_at);
        $this->assertNull($fresh->deleted_by);
        $this->assertInstanceOf(CarbonInterface::class, $fresh->updated_at);
        $this->assertEquals(null, $fresh->updated_by);
    }

    public function testForceDeleteDoesNotModifyAuditColumns(): void
    {
        $model = $this->getModel()->create(['name' => 'Force Delete']);
        $model->forceDelete();

        $this->assertNull($this->getModel()->withTrashed()->find($model->id));
    }

    public function testUpdatingWithDeleteOrRestoreActionSkipsUpdating(): void
    {
        $model = $this->getModel()->create(['name' => 'Skip Update']);
        $model->audit_action = 'D';
        $model->name = 'Updated Name';
        $model->save();

        $this->assertEquals('D', $model->audit_action);
    }

    public function testTransactionColumnsMethod(): void
    {
        $columns = $this->getModel()::transactionColumns();
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('audit_action', $columns);
    }

    public function testTransactionColumnsAttributesAllCases(): void
    {
        $model = $this->getModel();

        $create = $model::transactionColumnsAttributes('C');
        $this->assertEquals('C', $create['audit_action']);
        $this->assertInstanceOf(CarbonInterface::class, $create['created_at']);
        $this->assertEquals(null, $create['created_by']);

        $update = $model::transactionColumnsAttributes('U');
        $this->assertEquals('U', $update['audit_action']);
        $this->assertEquals(null, $update['updated_by']);
        $this->assertInstanceOf(CarbonInterface::class, $update['updated_at']);

        $delete = $model::transactionColumnsAttributes('D');
        $this->assertEquals('D', $delete['audit_action']);
        $this->assertEquals(null, $delete['deleted_by']);
        $this->assertInstanceOf(CarbonInterface::class, $delete['deleted_at']);

        $restore = $model::transactionColumnsAttributes('R');
        $this->assertEquals('R', $restore['audit_action']);
        $this->assertEquals(null, $restore['updated_by']);
        $this->assertNull($restore['deleted_at']);
        $this->assertNull($restore['deleted_by']);
    }

    public function testGetters(): void
    {
        $model = $this->getModel();
        $this->assertEquals(null, $model::getUser());
        $this->assertIsString($model::getIp());
        $this->assertTrue($model::substringInArray('test', ['this is a test']));
        $this->assertFalse($model::substringInArray('fail', ['this is a test']));
    }

    public function testGetIpReturnsIpFromRequest(): void
    {
        $model = $this->getModel();

        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '123.123.123.123'
        ]);
        $this->app->instance(Request::class, $request);

        $this->assertEquals('123.123.123.123', $model::getIp());
    }

    public function testGetIpReturnsNoIpOnException(): void
    {
        $model = $this->getModel();
        $fakeRequest = new class {
            public function ip()
            {
                throw new \Exception('Forced exception');
            }
        };

        $this->app->instance(Request::class, $fakeRequest);
        $this->assertEquals('No IP', $model::getIp());
    }
}
