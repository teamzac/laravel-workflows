<?php

namespace TeamZac\Workflow\Tests\Workflows\ThrowsException;

use TeamZac\Workflow\AbstractWorkflow;
use TeamZac\Workflow\AbstractWorkflowStep;

class ThrowsExceptionStep extends AbstractWorkflowStep
{
    protected $statusMessage = 'Exception thrown';

    public function handle()
    {
        throw new \Exception('None shall pass');
    }
}
