<?php

namespace TeamZac\Workflow;

use Illuminate\Support\Arr;

abstract class AbstractWorkflowStep
{
    /** @var TeamZac\Workflow\WorkflowInstance */
    protected $instance;

    /** @var string */
    protected $statusMessage;

    /**
     * This method is what is actually called in the RunWorkflowStepJob. It calls
     * the `setup()` method first to allow for any shared setup needed across
     * a series of workflow steps. Then it calls `handle()` to run the step.
     */
    public function fire()
    {
        if (!method_exists($this, 'handle')) { 
            throw new \Exception('Concrete workflow steps must implement a handle() method but ' . get_class($this) . ' does not');
        }

        $this->setup();
        app()->call([$this, 'handle']);
        $this->tearDown();
    }

    /**
     * Subclasses can override this step to perform any setup needed. Usually
     * this would be overridden in a trait if you need to set some shared
     * state across multiple workflow steps. Ignore if not needed.
     */
    protected function setup()
    {

    }

    /**
     * Subclasses can override this step to perform any setup needed. Usually
     * this would be overridden in a trait if you need to set some shared
     * state across multiple workflow steps. Ignore if not needed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * Set the instance
     */
    public function setInstance(WorkflowInstance $instance)
    {
        $this->instance = $instance;
        return $this;
    }

    /**
     * Get the workflow instance
     * 
     * @return  WorkflowInstance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Get instance metadata
     * 
     * @param   string|null $key
     * @return  mixed
     */
    public function getMetadata($key = null)
    {
        if (is_null($key)) {
            return $this->getInstance()->metadata;
        }
        return Arr::get($this->getInstance()->metadata, $key);
    }

    /**
     * Get the status message
     * 
     * @return  string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }
}
