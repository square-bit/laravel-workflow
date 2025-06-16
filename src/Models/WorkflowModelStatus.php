<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Squarebit\Workflows\Contracts\Workflowable;
use Squarebit\Workflows\Traits\BelongsToWorkflow;

/**
 * @property Model $model
 * @property WorkflowStatus $status
 * @property int $workflow_status_id
 * @property Authenticatable $user
 * @property int $user_id
 */
class WorkflowModelStatus extends Model
{
    /** @use BelongsToWorkflow<WorkflowModelStatus> */
    use BelongsToWorkflow;

    use SoftDeletes;

    protected $with = ['status'];

    protected $guarded = ['id'];

    protected $table = 'workflows_model_statuses';

    /**
     * @return MorphTo<Workflowable, WorkflowModelStatus>
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * @return BelongsTo<User, WorkflowModelStatus>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('workflow.user_model'));
    }

    /**
     * @return BelongsTo<WorkflowStatus, WorkflowModelStatus>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(WorkflowStatus::class, 'workflow_status_id');
    }

    public function scopeInStatus(Builder $query, int|WorkflowStatus $status): Builder
    {
        return $query->where('workflow_status_id', $status instanceof WorkflowStatus ? $status->getKey() : $status);
    }

    public function scopeForWorkflow(Builder $query, int|Workflow $workflow): Builder
    {
        return $query->where('workflow_id', $workflow instanceof Workflow ? $workflow->getKey() : $workflow);
    }
}
