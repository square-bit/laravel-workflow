<?php

namespace Squarebit\Workflows\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Squarebit\Workflows\Models\Workflow;

/**
 * @property int $workflow_id
 * @property \Squarebit\Workflows\Models\Workflow $workflow
 *
 * @method static Builder forWorkflow(int|Workflow $workflow)
 */
trait BelongsToWorkflow
{
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function scopeForWorkflow(Builder $query, int|Workflow $workflow): Builder
    {
        return $query->where('workflow_id', is_int($workflow) ? $workflow : $workflow->id);
    }
}
