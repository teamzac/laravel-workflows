<?php

namespace TeamZac\Workflow\Tests;

use TeamZac\Workflow\Facades\Workflow;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Workflow::extend('multiplication', function() {
            return new Workflows\Multiplication\MultiplicationWorkflow;
        });

        Workflow::extend('exception', function() {
            return new Workflows\ThrowsException\ThrowsExceptionWorkflow;
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            'TeamZac\Workflow\WorkflowServiceProvider'
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Workflow' => 'TeamZac\Workflow\Facades\Workflow',
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        // $app['config']->set('database.default', 'testbench');
        // $app['config']->set('database.connections.testbench', [
        //     'driver'   => 'sqlite',
        //     'database' => ':memory:',
        //     'prefix'   => '',
        // ]);
    }
}
