<?php

namespace TeamZac\Workflow\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeWorkflowStepCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:workflow-step';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new workflow step class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'WorkflowStep';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../stubs/workflow-step.stub';
    }
}
