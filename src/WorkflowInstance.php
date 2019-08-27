<?php

namespace TeamZac\Workflow;

use TeamZac\Workflow\Facades\Workflow;
use Illuminate\Database\Eloquent\Model;
use TeamZac\Workflow\Enums\WorkflowStatus;
use TeamZac\Workflow\Events\WorkflowInstanceCreated;

class WorkflowInstance extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function($instance) {
            event(new WorkflowInstanceCreated($instance));
        });
    }

    /**
     * Run the workflow instance
     *
     * @return  $this
     */
    public function run()
    {
        Workflow::run($this);
        return $this;
    }

    /**
     * Get a progress attribute
     */
    public function getProgressAttribute()
    {
       return round($this->current_step / count(Workflow::driver($this->workflow)->getSteps()), 2); 
    }

    /**
     * Get the workflowable object
     */
    public function workflowable()
    {
        return $this->morphTo();
    }

    /**
     * Scope the query to actionable workflow instances
     * 
     * @param   $query 
     * @return  $query
     */
    public function scopeActionable($query)
    {
        return $query->whereIn('status', [
            WorkflowStatus::Queued,
            WorkflowStatus::InProgress,
            WorkflowStatus::Paused,
        ]);
    }

    /**
     * Is the instance queued?
     * 
     * @return  boolean
     */
    public function isQueued()
    {
        return $this->status == WorkflowStatus::Queued;
    }

    /**
     * Is the instance paused?
     * 
     * @return  boolean
     */
    public function isPaused()
    {
        return $this->status == WorkflowStatus::Paused;
    }

    /**
     * Is the instance actionable? 
     * 
     * @return  boolean
     */
    public function isActionable()
    {
        return $this->status != WorkflowStatus::Completed && 
            $this->status != WorkflowStatus::Aborted;
    }

    /**
     * Pause the workflow
     * 
     * @param   string $message
     * @return  $this
     */
    public function pause($message)
    {
        $this->update([
            'status' => WorkflowStatus::Paused,
            'status_message' => $message,
        ]);

        return $this;
    }

    /**
     * Mark the workflow completed
     * 
     * @return  $this
     */
    public function markCompleted()
    {
        $this->update([
            'status' => WorkflowStatus::Completed,
            'status_message' => 'Completed',
            'current_step' => null,
        ]);

        return $this;
    }

    /**
     * Scope the query to active workflows only
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [WorkflowStatus::Completed, WorkflowStatus::Aborted]);
    }

    /**
     * Scope the query to queued workflows only
     */
    public function scopeQueued($query)
    {
        return $query->where('status', WorkflowStatus::Queued);
    }

    /**
     * Set a workflowable polymorphic property
     * 
     * @param   mixed $workflowable
     */
    public function setWorkflowableAttribute($workflowable)
    {
        if (is_null($workflowable)) {
            return;
        }

        $this->workflowable_type = get_class($workflowable);
        $this->workflowable_id = $workflowable->getKey();
    }
}
