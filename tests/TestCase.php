<?php

namespace TeamZac\Workflow\Tests;

use TeamZac\Workflow\Facades\Workflow;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupDatabase();

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

    protected function setupDatabase()
    {
        include_once __DIR__.'/../database/migrations/create_workflow_tables.stub';
        (new \CreateWorkflowTables)->up();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
