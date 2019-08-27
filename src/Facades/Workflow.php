<?php

namespace TeamZac\Workflow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Teamzac\Workflow\WorkflowManager
 */
class Workflow extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'workflow';
    }
}
