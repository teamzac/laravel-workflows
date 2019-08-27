<?php

namespace TeamZac\Workflow;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('workflows.php'),
            ], 'config');

            if (! class_exists('CreateWorkflowTables')) {
                $this->publishes([
                    __DIR__.'/../database/migrations' => database_path('migrations'),
                ], 'migrations');
            }

            // Registering package commands.
            $this->commands([
                Commands\MakeWorkflowCommand::class,
                Commands\MakeWorkflowStepCommand::class,
                Commands\WorkflowGenerateCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'workflows');

        // Register the main class to use with the facade
        $this->app->singleton('workflow', function ($app) {
            return new WorkflowManager($app);
        });
    }
}
