<?php

namespace TeamZac\Workflow;

use Illuminate\Bus\Queueable;
use TeamZac\Workflow\AbstractWorkflow;
use TeamZac\Workflow\AbstractWorkflowStep;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RunWorkflowStepJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /** @var TeamZac\Workflow\AbstractWorkflow */
    public $workflow;

    /** @var TeamZac\Workflow\AbstractWorkflowStep */
    public $step;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AbstractWorkflow $workflow, $step)
    {
        $this->workflow = $workflow;
        $this->step = $step;
        $this->onQueue(config('workflow.queue'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->fireCommencementEventsIfNecessary();

        $this->markInProgress();

        try {
            $this->runStep();
        } catch (\Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->advance();
    }

    /**
     * Check to see if we are on the first step, and if so, fire appropriate events
     */
    public function fireCommencementEventsIfNecessary()
    {
        if (!$this->workflow->isQueued()) {
            return;
        }

        collect(config('workflows.events.workflow_started'))->each(function($class) {
            event(new $class($this->workflow->getInstance()));
        });
    }

    /**
     * Mark the workflow in progress
     */
    public function markInProgress()
    {
        $this->workflow->markInProgress();
        return $this;
    }

    /**
     * Run the step
     */
    public function runStep()
    {
        app()->call([$this->step, 'fire']);

        $this->workflow->updateStatus($this->step->getStatusMessage());

        $this->fireStepCompletedEvents();

        return $this;
    }

    /**
     * Advance to the next step
     */
    public function advance()
    {
        if ($this->workflow->hasAnotherStep()) {
            $this->workflow->advance();
        } else {
            $this->workflow->markCompleted();
            $this->fireWorkflowCompletedEvents();
        }

        return $this;
    }

    /**
     * Handle the error by pausing the workflow and setting the status message
     * 
     * @param   Exception $e
     * @return  $this
     */
    public function handleException($e)
    {
        $this->workflow->pause($e->getMessage());

        collect(config('workflows.events.workflow_paused'))->each(function($class) use ($e) {
            event(new $class($this->workflow->getInstance(), $e));
        });

        if (method_exists($this->step, 'handleException')) {
            $this->step->handleException($e);
        }

        return $this;
    }

    /**
     * Fire WorkflowStepCompleted events
     * 
     * @return  $this
     */
    public function fireStepCompletedEvents()
    {
        collect(config('workflows.events.workflow_step_completed'))->each(function($class) {
            event(new $class($this->step));
        });
    }

    /**
     * Fire WorkflowCompleted events
     * 
     * @return  $this
     */
    public function fireWorkflowCompletedEvents()
    {
        collect(config('workflows.events.workflow_completed'))->each(function($class) {
            event(new $class($this->workflow->getInstance()));
        });
    }

    public function tags()
    {
        $tags = [
            'workflow',
            'workflows:' . $this->step->getInstance()->getKey(),
            'step:' . get_class($this->step),
        ];

        if ($this->step->getInstance()->workflowable) {
            $tags[] = sprintf('%s:%s', $this->step->getInstance()->workflowable_type, $this->step->getInstance()->workflowable_id);
        }

        return $tags;
    }
}
