<?php

namespace TeamZac\Workflow\Tests;

use Illuminate\Support\Facades\Event;
use TeamZac\Workflow\Facades\Workflow;
use TeamZac\Workflow\WorkflowInstance;
use TeamZac\Workflow\Enums\WorkflowStatus;
use TeamZac\Workflow\Events\WorkflowPaused;
use TeamZac\Workflow\Events\WorkflowStarted;
use TeamZac\Workflow\Events\WorkflowCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TeamZac\Workflow\Events\WorkflowStepCompleted;
use TeamZac\Workflow\Tests\Workflows\Multiplication\MultiplyByTen;
use TeamZac\Workflow\Tests\Workflows\Multiplication\MultiplyByTwo;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    function it_knows_what_the_next_step_is()
    {
        $instance = WorkflowInstance::create([
            'workflow' => 'multiplication',
        ])->refresh();

        $multiplicationWorkflow = Workflow::driver($instance->workflow)->setInstance($instance);

        $this->assertInstanceOf(MultiplyByTen::class, $multiplicationWorkflow->getNextStep());

        $instance->increment('current_step');
        $multiplicationWorkflow->setInstance($instance);
        $this->assertInstanceOf(MultiplyByTwo::class, $multiplicationWorkflow->getNextStep());

        $instance->increment('current_step');
        $multiplicationWorkflow->setInstance($instance);
        $this->assertInstanceOf(MultiplyByTen::class, $multiplicationWorkflow->getNextStep());

        $instance->increment('current_step');
        $multiplicationWorkflow->setInstance($instance);
        $this->assertNull($multiplicationWorkflow->getNextStep());
    }

    /** @test */
    function it_runs_the_workflow()
    {
        Event::fake();
        $instance = WorkflowInstance::create([
            'workflow' => 'multiplication',
            'metadata' => [
                'value' => 1,
            ]
        ])->refresh();

        Workflow::run($instance);

        $this->assertEquals(200, $instance->refresh()->metadata['value']);
        Event::assertDispatchedTimes(WorkflowStarted::class, 1);
        Event::assertDispatchedTimes(WorkflowStepCompleted::class, 3);
        Event::assertDispatchedTimes(WorkflowCompleted::class, 1);
    }

    /** @test */
    function it_pauses_when_an_exception_is_thrown()
    {
        Event::fake();
        $instance = WorkflowInstance::create([
            'workflow' => 'exception',
        ])->refresh();

        Workflow::run($instance);

        $this->assertEquals(WorkflowStatus::Paused, $instance->refresh()->status);
        $this->assertEquals('None shall pass', $instance->status_message);
        Event::assertDispatched(WorkflowPaused::class);
    }

    /** @test */
    function a_paused_workflow_can_be_resumed()
    {
        Event::fake();
        $instance = WorkflowInstance::create([
            'workflow' => 'multiplication',
            'status' => WorkflowStatus::Paused,
            'current_step' => 1, // start on the second step
            'metadata' => [
                'value' => 1,
            ]
        ])->refresh();

        Workflow::run($instance);

        $this->assertEquals(20, $instance->refresh()->metadata['value']);
        Event::assertDispatchedTimes(WorkflowStepCompleted::class, 2);
    }
}
