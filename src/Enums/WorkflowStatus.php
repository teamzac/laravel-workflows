<?php

namespace TeamZac\Workflow\Enums;

enum WorkflowStatus: string
{
    case Queued = 'queued';
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';
    case Aborted = 'aborted';
}
