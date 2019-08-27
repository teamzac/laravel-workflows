<?php

namespace TeamZac\Workflow\Tests;

use Illuminate\Support\Facades\Event;
use TeamZac\Workflow\WorkflowInstance;
use TeamZac\Workflow\Enums\WorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TeamZac\Workflow\Events\WorkflowInstanceCreated;

class WorkflowInstanceTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    function an_instance_is_queued_by_default()
    {
        $instance = WorkflowInstance::create([
            'workflow' => 'multiplication',
        ]);

        tap(WorkflowInstance::first(), function($instance) {
            $this->assertSame(WorkflowStatus::Queued, $instance->status);
            $this->assertTrue($instance->isQueued());
        });
    }

    /** @test */
    function it_fires_a_created_event()
    {
        Event::fake([WorkflowInstanceCreated::class]);

        $instance = WorkflowInstance::create([
            'workflow' => 'multiplication',
        ])->refresh();

        Event::assertDispatched(WorkflowInstanceCreated::class, function($event) use ($instance) {
            $this->assertEquals($event->instance->getKey(), $instance->getKey());
            return true;
        });
    }

    /** @test */
    function it_can_attach_a_polymorphic_workflowable_model()
    {
        $instanceOne = WorkflowInstance::create([
            'workflow' => 'multiplication',
        ]);

        $instanceTwo = WorkflowInstance::create([
            'workflowable' => $instanceOne,
            'workflow' => 'multiplication',
        ]);
        
        $this->assertEquals($instanceOne->getKey(), $instanceTwo->workflowable->getKey());
    }
}
