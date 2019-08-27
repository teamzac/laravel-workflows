<?php

return [
    /**
     * The name of the table that stores workflow instances
     */
    'instance_table' => env('WORKFLOW_INSTANCE_TABLE', 'workflow_instances'),

    /**
     * The name of the table that stores workflow instances
     */
    'instance_model' => env('WORKFLOW_INSTANCE_MODEL', TeamZac\Workflow\WorkflowInstance::class),

    /**
     * The maximum allowed timeout for the queued jobs
     */
    'timeout' => 600.0,

    /** 
     * The name of the queue on which to run the workflow jobs
     */
    'queue' => env('WORKFLOW_QUEUE', 'workflows'),

    /**
     * This package will fire a variety of events when certain things happen, such
     * as when a WorkflowInstance has been created/started/paused/completed, and
     * when WorkflowSteps have been completed as well. If you would like to 
     * fire your own custom events alongside the package events, specify
     * them in the item below.
     */
    'events' => [
        /** 
         * Constructor receives the WorkflowInstance
         */
        'instance_created' => [
            \TeamZac\Workflow\Events\WorkflowInstanceCreated::class,
            //
        ],

        /** 
         * Constructor receives the WorkflowInstance
         */
        'workflow_started' => [
            \TeamZac\Workflow\Events\WorkflowStarted::class,
            //
        ],

        /** 
         * Constructor receives the WorkflowInstance and the exception that was thrown
         */
        'workflow_paused' => [
            \TeamZac\Workflow\Events\WorkflowPaused::class,
            //
        ],

        /** 
         * Constructor receives the WorkflowInstance
         */
        'workflow_completed' => [
            \TeamZac\Workflow\Events\WorkflowCompleted::class,
            //
        ],

        /** 
         * Constructor receives the WorkflowStep
         */
        'workflow_step_completed' => [
            \TeamZac\Workflow\Events\WorkflowStepCompleted::class,
            //
        ],
    ],


];
