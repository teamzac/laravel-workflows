<?php

namespace TeamZac\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use TeamZac\Workflow\WorkflowInstance;
use TeamZac\Workflow\AbstractWorkflowStep;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WorkflowPaused
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var WorkflowInstance */
    public $instance;

    /** @var Exception */
    public $exception;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WorkflowInstance $instance, \Exception $exception)
    {
        $this->instance = $instance;
        $this->exception = $exception;
    }
}
