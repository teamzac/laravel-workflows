<?php

namespace TeamZac\Workflow\Tests\Workflows\Multiplication;

use TeamZac\Workflow\AbstractWorkflow;
use TeamZac\Workflow\AbstractWorkflowStep;

class MultiplyByTwo extends AbstractWorkflowStep
{
    protected $statusMessage = 'Multiplied by 2';

    public function handle()
    {
        $this->instance->update([
            'metadata' => [
                'value' => $v = $this->instance->metadata['value'] * 2,
            ]
        ]);
    }
}
