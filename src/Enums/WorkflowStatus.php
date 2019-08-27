<?php

namespace TeamZac\Workflow\Enums;

use BenSampo\Enum\Enum;

final class WorkflowStatus extends Enum
{
    const Queued = 'queued';
    const InProgress = 'in_progress';
    const Paused = 'paused';
    const Completed = 'completed';
    const Aborted = 'aborted';
}
