<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasPermissions;
use Squarebit\Workflows\Traits\BelongsToWorkflow;

/**
 * @property int $id
 * @property int $workflow_id
 * @property int $from_id
 * @property int $to_id
 * @property Workflow $workflow
 * @property WorkflowStatus $fromStatus
 * @property WorkflowStatus $toStatus
 *
 * @method static Builder from(WorkflowStatus $from)
 * @method static Builder to(WorkflowStatus $to)
 * @method static Builder fromTo(WorkflowStatus $from, WorkflowStatus $to)
 * @method static Builder entry()
 * @method static Builder exit()
 */
class WorkflowTransition extends Model
{
    use SoftDeletes;

    /** @use BelongsToWorkflow<WorkflowTransition> */
    use BelongsToWorkflow;

    use HasPermissions;

    public string $guard_name = 'web';

    protected $table = 'workflow_transitions';

    public function getMorphClass(): string
    {
        return 'Workflow.WorkflowTransition';
    }

    /**
     * @return BelongsTo<WorkflowStatus, WorkflowTransition>
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(WorkflowStatus::class, 'from_id');
    }

    /**
     * @return BelongsTo<WorkflowStatus, WorkflowTransition>
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(WorkflowStatus::class, 'to_id');
    }

    /**
     * @param  Builder<WorkflowTransition>  $query
     * @return Builder<WorkflowTransition>
     */
    public function scopeFrom(Builder $query, ?WorkflowStatus $from): Builder
    {
        return $query->where('from_id', $from?->id);
    }

    /**
     * @param  Builder<WorkflowTransition>  $query
     * @return Builder<WorkflowTransition>
     */
    public function scopeTo(Builder $query, ?WorkflowStatus $to): Builder
    {
        return $query->where('to_id', $to?->id);
    }

    /**
     * @param  Builder<WorkflowTransition>  $query
     * @return Builder<WorkflowTransition>
     */
    public function scopeFromTo(Builder $query, ?WorkflowStatus $from, ?WorkflowStatus $to): Builder
    {
        return $query->from($from)
            ->to($to);
    }

    /**
     * @param  Builder<WorkflowTransition>  $query
     * @return Builder<WorkflowTransition>
     */
    public function scopeEntry(Builder $query): Builder
    {
        return $query->whereNull('from_id');
    }

    /**
     * @param  Builder<WorkflowTransition>  $query
     * @return Builder<WorkflowTransition>
     */
    public function scopeExit(Builder $query): Builder
    {
        return $query->whereNull('to_id');
    }

    public function __toString()
    {
        return $this->workflow.': '.($this->fromStatus ?? 'o').' -> '.($this->toStatus ?? 'x');
    }
}
