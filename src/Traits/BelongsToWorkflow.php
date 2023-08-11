<?php

namespace Squarebit\Workflows\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Squarebit\Workflows\Models\Workflow;

/**
 * @template T of Model
 *
 * @property int $workflow_id
 * @property \Squarebit\Workflows\Models\Workflow $workflow
 *
 * @method static Builder forWorkflow(int|Workflow $workflow)
 */
trait BelongsToWorkflow
{
    /**
     * @return BelongsTo<Workflow, T>
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * @param  Builder<T>  $query
     * @return Builder<T>
     */
    public function scopeForWorkflow(Builder $query, int|Workflow $workflow): Builder
    {
        return $query->where('workflow_id', is_int($workflow) ? $workflow : $workflow->id);
    }
}
