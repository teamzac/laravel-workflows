<?php

namespace TeamZac\Workflow\Commands;

use Illuminate\Console\Command;
use TeamZac\Workflow\Facades\Workflow;

class WorkflowGenerateCommand extends Command
{
    /**
     * The console command name and signature.
     *
     * @var string
     */
    protected $signature = 'workflow:generate {workflow}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the steps given a workflow';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $workflow = Workflow::driver($this->argument('workflow'));

        foreach ($workflow->getSteps() as $stepClass) {
            $this->callSilent('make:workflow-step', ['name' => $stepClass]);
        }
    }
}