<?php

namespace TeamZac\Workflow\Tests\Workflows\ThrowsException;

use TeamZac\Workflow\AbstractWorkflow;
use TeamZac\Workflow\AbstractWorkflowStep;

class EmptyStep extends AbstractWorkflowStep
{
    protected $statusMessage = 'noop';

    public function handle()
    {
        //
    }
}
