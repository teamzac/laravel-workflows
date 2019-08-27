<?php

namespace TeamZac\Workflow\Tests\Workflows\Multiplication;

use TeamZac\Workflow\AbstractWorkflow;

class MultiplicationWorkflow extends AbstractWorkflow
{
    protected $steps = [
        MultiplyByTen::class,
        MultiplyByTwo::class,
        MultiplyByTen::class,
    ];
}
