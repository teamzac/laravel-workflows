<?php

namespace TeamZac\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use TeamZac\Workflow\AbstractWorkflowStep;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WorkflowStepCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var AbstractWorkflowStep */
    public $step;

    /** @var WorkflowInstance */
    public $instance;

    /** @var string */
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(AbstractWorkflowStep $step)
    {
        $this->step = $step;
        $this->message = $step->getStatusMessage();
        $this->instance = $step->getInstance();
    }
}
