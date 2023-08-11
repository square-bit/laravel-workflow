<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use BelongsToWorkflow;
    use SoftDeletes;

    protected $with = ['status'];

    protected $table = 'workflow_model_statuses';

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('workflow.user_model'));
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(WorkflowStatus::class, 'workflow_status_id');
    }
}
