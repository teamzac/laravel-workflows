<?php

namespace TeamZac\Workflow;

use Illuminate\Support\Manager;

class WorkflowManager extends Manager
{
    /**
     * Run the given workflow instance
     * 
     * @param   WorkflowInstance $instance
     * @return  $this
     */
    public function run($instance)
    {
        $this->driver($instance->workflow)->setInstance($instance)->run();
        return $this;
    }

    /**
     * Create a new workflow instance
     * 
     * @param   string $workflow
     * @param   mixed|null $workflowable
     * @param   array $metadata
     * @return  WorkflowInstance
     */
    public function createInstance($workflow, $workflowable = null, $metadata = [])
    {
        $class = config('workflows.instance_model');
        return $class::create([
            'workflow' => $workflow,
            'workflowable' => $workflowable,
            'metadata' => $metadata,
        ])->refresh();
    }

    /**
     * Set up the workflow and run a specific step for a given workflow instance
     * Use this primarily for testing your workflow steps individually.
     * 
     * @param   WorkflowInstance $instance
     * @param   string $stepClass
     * @return  void
     */
    public function test(WorkflowInstance $instance, $stepClass)
    {
        resolve($stepClass)->setInstance($instance)->fire();
    }

    /**
     * Get the default driver name.
     *
     * @return  string
     */
    public function getDefaultDriver()
    {
        return 'null';
    }
}
