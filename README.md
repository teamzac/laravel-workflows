# A Laravel 8+ package for running multi-step, queued workflows

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teamzac/laravel-workflows.svg?style=flat-square)](https://packagist.org/packages/teamzac/laravel-workflows)
[![Total Downloads](https://img.shields.io/packagist/dt/teamzac/laravel-workflows.svg?style=flat-square)](https://packagist.org/packages/teamzac/laravel-workflows)

Pipelines are a powerful tool in Laravel apps, but sometimes you need to perform longer running tasks that may be split into multiple steps with better support for handling situations where errors occur end you need to restart from the current point. This package helps with those types of workflows.

This package is available for public use, but keep in mind that it's built for our specific use case at this time. It has been extracted from an older app and updated a bit for more general use. There are probably some edge cases that we haven't accounted for, and there might be some *duh* features that we haven't included yet. It may not fit all of your needs. If you'd like to contribute to make it better, that'd be awesome! But please reach out beforehand in case what you need doesn't fit with our use for the package.

## Installation

You can install the package via composer:

```bash
composer require teamzac/laravel-workflows
```

This package uses auto-discovery, so you do not need to include it in your ```config/app.php```.

You can publish the config and tweak settings including the name of the workflow instance table.

```bash
php artisan vendor:publish --provider="TeamZac\Workflow\WorkflowServiceProvider"
```

Once you're ready, migrate the database to create the ```workflow_instances``` table. You can also publish the migration file if you wish to modify it. The table name can be changed in the config.

```bash
php artisan migrate
```

## Concepts

### Workflow

A ```Workflow``` is comprised of one or more ```WorkflowStep```s. When you run a Workflow, this package will iterate through each WorkflowStep. If an unhandled error is encountered, the Workflow will be paused; otherwise, it will proceed to the next step until there are no more.

### WorkflowStep

A WorkflowStep is one of potentially many tasks that need to be handled during a Workflow. 

### WorkflowInstance

A WorkflowInstance is a concrete instance of a Workflow, which often includes specific data on which the Workflow will perform actions. In this package, a WorkflowInstance is represented via an Eloquent model, and is stored in your database.

### WorkflowManager

This is a central repository for Workflows in your app. You should register your Workflows in a service provider. It provides convenient access to run and manage Workflows.

## Usage

### Creating a Workflow

Before you can run a Workflow, you'll need to create one. You can create a new subclass of ```TeamZac\Workflows\AbstractWorkflow``` where ever you'd like. You can also use the built-in generators to quickly scaffold a new Workflow:

```bash
// example
php artisan make:workflow App\\Workflows\\TestWorkflow
```

The AbstractWorkflow subclass does a good deal of work for you, so all you need to do is define the ```WorkflowStep```s that should be performed:

```php
<?php

namespace App\Workflows;

use TeamZac\Workflows\AbstractWorkflow;

class TestWorkflow extends AbstractWorkflow
{
  protected $steps = [
      'App\Workflows\StepOne',
      'App\Workflows\StepTwo',
      'App\Workflows\StepThree',
    ];
}

```

You are free to organize your code however you prefer. This is just an example.

Each WorkflowStep should be a subclass of ```TeamZac\Workflows\AbstractWorkflowStep```. It would be a pain to manually create all of these classes, so this package provides another generator for your convenience. 

### Register your new Workflow

Before you can use it, make sure to register your workflow with the WorkflowManager. You may do this in a service provider's ```boot()``` method if you prefer:

```php

namespace App\Providers;

use TeamZac\Workflow\Facades\Workflow;

class AppServiceProvider 
{
  public function boot() 
    {
    Workflow::extend('test-workflow', function() {
          return new \App\Workflows\TestWorkflow;
        });
  }
}
```

The WorkflowManager uses Laravel's build-in Manager pattern. Simply call the ```extend()``` method with a key and callback that returns your workflow. You may do any set up that might be needed here.

### Generating WorkflowStep classes

Now that you've registered your workflow, you may use the generator:

```bash
php artisan workflow:generate test-workflow
```

This will inspect the ```$steps``` variable and create stubbed WorkflowStep classes, much in the same way that ```php artisan event:generate``` works.

### Creating a WorkflowInstance

A Workflow relies on a WorkflowInstance, which is passed through to each WorkflowStep. It is a typical Eloquent model, so you can create it as you normally would.

```php
$instance = TeamZac\Workflows\WorkflowInstance::create([
  'workflow' => 'test-workflow',
]);
```

The ```workflow``` key is the only required field, and it should reference the key that you used when you registered the Workflow.

#### Workflowable Relationship

WorkflowInstances can reference any Eloquent object in your domain via the polymorphic relationship ```workflowable```. If you're running a Workflow on a user, for instance, this can let you directly access that object.

#### Metadata

You can also store arbitrary key/value data using the WorkflowInstance's ```metadata``` property. This field is cast to an array.

### Running a Workflow

Once you have a WorkflowInstance, you can run it through the workflow in two ways:

```php
// by calling run() directly on the instance
$instance->run();

// by passing it through the Workflow facade
Workflow::run($instance);

// by manually creating an instance of the Workflow driver, setting the instance, and calling run
Workflow::driver('test-workflow')->setInstance($instance)->run();
```

The last way is what's done under the hood, and there's really not a reason do to that yourself, but feel free if you wish.

Once you call ```$instance->run();```, the ```TeamZac\Workflows\RunWorkflowStepJob``` will be dispatched. You can configure the queue to use.

The RunWorkflowStepJob receives the Workflow (along with the instance, which was set previously), and the next step to run.

Here is the process:

1. If this Instance is starting at the beginning of the Workflow, commencement events will be fired
2. The Instance's status will be updated to "in_progress"
3. The next WorkflowStep will be run by calling the ```handle()``` method out of the container
4.  If an unhandled Exception is thrown, the Instance will be paused and any paused events will be dispatched.
5.  If no unhandled exceptions are thrown, we'll check for any additional steps.
6.  If there is a next step, it will be queued and run (return to step 3).
7.  Otherwise, the Instance will be marked completed and completion events will fire.

### Working with WorkflowSteps

If you define a ```protected $statusMessage``` property on your WorkflowStep, the WorkflowInstance will be updated with that message when the step is successfully completed:

```
  protected $statusMessage = 'Multiplied by 2';
    
    ...
    
    // after completing this step:
    echo $instance->status_message;
    // echoes "Multiplied by 2"
```

If you need to perform some logic in order to determine the status message, you can override ```getStatusMessage()``` instead of setting the property directly.

```
  public function getStatusMessage()
    {
      // some logic here
        return 'message';
    }
```

If you need to complete some actions prior to and/or after the step has completed, you can use the ```setup()``` and ```tearDown()``` methods on the WorkflowStep class.

The meat of your WorkflowStep should reside within the ```handle()``` method, which is called out of the container and can therefore use dependency injection.

You can access the WorkflowInstance using the ```getInstance()``` method on the WorkflowStep. 

You can access the Instances metadata directly via the ```getMetadata()``` method. Pass a key to retrieve a specific value, or null to get the entire metadata array.

## Events

The following events are dispatched by default:

```TeamZac\Workflows\Events\WorkflowStarted``` when the WorkflowInstance begins running its first step.

```TeamZac\Workflows\Events\WorkflowStepCompleted``` when a WorkflowStep has been successfully completed.

```TeamZac\Workflows\Events\WorkflowPaused``` when an unhandled Exception caused the Workflow to pause. You'll receive the WorkflowInstance as well as the original Exception so you can report to an error tracking system or do what you'd like with it.

```TeamZac\Workflows\Events\WorkflowCompleted``` when the final WorkflowStep has been successfully completed.

You can customize the events that get dispatched, or simply reference these in your EventServiceProvider as necessary.

## Testing your workflow steps

You can run a workflow in your test suite, but if you'd like to test a single step without having to run through all of the previous steps, you can use the ```test()``` method on the Workflow Manager.

```php
Workflow::test($workflowInstance, StepClass::class);
```

This will build the workflow step, set the instance, and fire it. It does not returning anything (although perhaps we'll add some test helpers in the future). However, you can test against any data which should have changed during the running of the workflow step.

## Configuration

You can publish the config file:

```bash
php artisan vendor:publish --provider=TeamZac\\Workflows\\WorkflowServiceProvider --tag=config
```

You can also publish the migration if you wish:

```bash
php artisan vendor:publish --provider=TeamZac\\Workflows\\WorkflowServiceProvider --tag=migrations
```

However, you can customize the WorkflowInstance's table name in the config, so if you don't need to add or change any table columns, there's no real need to publish the migrations unless you just want to own it.

### Configuration Options

Once published, you can access the config at ```config/workflows.php```.

```instance_table``` allows you to define the name of the table used to store ```WorkflowInstance```s.

```instance_model``` allows you to use your own subclass of ```TeamZac\Workflow\WorkflowInstance``` if you wish.

```timeout``` sets a custom timeout for queued workflow jobs

```queue``` lets you define which queue to use. It's set to 'default' by default, but you may wish to move workflow steps to a separate queue.

```events``` is where you can choose which events will be broadcast at various points in the Workflow lifecycle. You can use your own in conjunction with ours, or remove ours altogether. Just make sure that your events' constructors properly receive the same parameters as the built-in events.


### Todo

* Add a command to purge old workflow instances
* Some of the code is a bit messy and I'd like to clean it up as I get a chance


### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email chad@zactax.com instead of using the issue tracker.

## Credits

- [Chad Janicek](https://github.com/teamzac)
- [All Contributors](../../contributors)
- [Laravel Package Boilerplate](https://laravelpackageboilerplate.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.