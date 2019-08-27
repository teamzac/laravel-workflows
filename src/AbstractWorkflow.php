<?php

namespace TeamZac\Workflow;

use TeamZac\Workflow\Enums\WorkflowStatus;
use TeamZac\Workflow\Events\WorkflowCompleted;

abstract class AbstractWorkflow
{
    /** @var TeamZac\Workflow\WorkflowInstance */
    protected $instance;

    /** @var array */
    protected $steps;

    /**
     * Set the workflow instance
     * 
     * @param   WorkflowInstance $instance
     * @return  $this
     */
    public function setInstance(WorkflowInstance $instance)
    {
        $this->instance = $instance;
        return $this;
    }

    /** @return WorkflowInstance */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Is the workflow instance currently in a Queued state?
     * 
     * @return  bool
     */
    public function isQueued()
    {
        return $this->instance->isQueued();
    }

    /** @return array */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Get the next step in this workflow
     * 
     * @return  AbstractWorkflowStep
     */
    public function getNextStep()
    {
        if (
            !$this->instance->isActionable() ||
            $this->instance->current_step >= count($this->steps)
        ) {
            return;
        }

        $class = $this->steps[$this->instance->current_step];
        return app($class)->setInstance($this->instance);
    }

    /**
     * Does the workflow have another step?
     * 
     * @return  boolean
     */
    public function hasAnotherStep()
    {
        return $this->instance->current_step < (count($this->steps) - 1);
    }

    /**
     * Does the workflow have any more steps?
     * 
     * @return  boolean
     */
    public function hasMoreSteps()
    {
        return $this->instance->isActionable() ? 
            $this->instance->current_step <= count($this->steps) :
            false;
    }

    /**
     * Run the workflow
     * 
     * @return  $this
     */
    public function run()
    {
        RunWorkflowStepJob::dispatch($this, $this->getNextStep())
            ->onQueue(config('workflows.queue'));
    }

    /**
     * Mark the instance in progress
     * 
     * @return  $this
     */
    public function markInProgress()
    {
        $this->instance->update([
            'status' => WorkflowStatus::InProgress,
        ]);

        return $this;
    }

    /**
     * Mark the instance as completed
     * 
     * @return  $this
     */
    public function markCompleted()
    {
        $this->instance->markCompleted();

        return $this;
    }

    /**
     * Update the status message of the instance
     * 
     * @param   string $message
     * @return  $this
     */
    public function updateStatus($message)
    {
        $this->instance->update([
            'status_message' => $message,
        ]);

        return $this;
    }

    /**
     * Advance to the next step
     * 
     * @return  $this
     */
    public function advance()
    {
        $this->instance->increment('current_step');
        $this->run();
        return $this;
    }

    /**
     * Pause the workflow
     * 
     * @param   string $message
     * @return  $this
     */
    public function pause($message)
    {
        $this->instance->pause($message);
    }
}