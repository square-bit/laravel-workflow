<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
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

    protected $table = 'workflow_model_statuses';

    /**
     * @return MorphTo<Model, WorkflowModelStatus>
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
}
