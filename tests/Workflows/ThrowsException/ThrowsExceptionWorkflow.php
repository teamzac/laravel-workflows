<?php

namespace TeamZac\Workflow\Tests\Workflows\ThrowsException;

use TeamZac\Workflow\AbstractWorkflow;

class ThrowsExceptionWorkflow extends AbstractWorkflow
{
    protected $steps = [
        EmptyStep::class,
        ThrowsExceptionStep::class,
    ];
}
