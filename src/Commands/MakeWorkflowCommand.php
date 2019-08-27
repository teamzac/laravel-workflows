<?php

namespace TeamZac\Workflow\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeWorkflowCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:workflow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new workflow class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Workflow';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../stubs/workflow.stub';
    }
}
