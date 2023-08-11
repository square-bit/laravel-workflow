<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property Collection $transitions
 * @property Collection $entryTransitions
 * @property Collection $exitTransitions
 */
class Workflow extends Model
{
    use SoftDeletes;

    protected $table = 'workflows';

    /**
     * @return HasMany<WorkflowTransition>
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    /**
     * @return HasMany<WorkflowTransition>
     */
    public function entryTransitions(): HasMany
    {
        return $this->transitions()->entry();
    }

    /**
     * @return HasMany<WorkflowTransition>
     */
    public function exitTransitions(): HasMany
    {
        return $this->transitions()->exit();
    }

    public function __toString()
    {
        return $this->name;
    }
}
