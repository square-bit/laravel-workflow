<?php

namespace Squarebit\Workflows\Traits;

use BackedEnum;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Squarebit\Workflows\Contracts\Workflowable;
use Squarebit\Workflows\Exceptions\InvalidTransitionException;
use Squarebit\Workflows\Exceptions\UnauthorizedTransitionException;
use Squarebit\Workflows\Helpers\ModelHelper;
use Squarebit\Workflows\Models\Workflow;
use Squarebit\Workflows\Models\WorkflowModelStatus;
use Squarebit\Workflows\Models\WorkflowStatus;
use Squarebit\Workflows\Models\WorkflowTransition;
use Squarebit\Workflows\Services\TransitionService;
use Throwable;

/**
 * @property WorkflowModelStatus $modelStatus
 * @property Collection $allModelStatus
 * @property Collection $modelStatuses
 */
trait HasWorkflows
{
    protected ?Workflow $usingWorkflow = null;

    public static function bootHasWorkflows(): void
    {
        static::created(function (Workflowable $model) {
            if (($workflow = $model->getDefaultWorkflow()) === null) {
                return;
            }

            $model->initWorkflow($workflow);
        });
    }

    public function initWorkflow(int|Workflow $workflow): static
    {
        $this->with = array_unique(array_merge($this->with, ['modelStatus']));

        $this->usingWorkflow($workflow);
        if ($this->modelStatus === null) {
            $this->createModelStatus($workflow, TransitionService::getWorkflowStartStatus($workflow));
        }

        return $this->usingWorkflow($workflow);
    }

    public function getDefaultWorkflow(): ?Workflow
    {
        $name = $this->getDefaultWorkflowName();
        if ($name === null) {
            return null;
        }

        return Workflow::where('name', $name)->first();
    }

    /**
     * Override this if the model should have a default workflow
     */
    public function getDefaultWorkflowName(): ?string
    {
        return null;
    }

    public function usingWorkflow(null|int|Workflow $workflow): static
    {
        $this->usingWorkflow = $workflow instanceof Workflow ? $workflow : Workflow::find($workflow);
        $this->unsetRelation('modelStatus');
        $this->unsetRelation('modelStatuses');
        $this->unsetRelation('allModelStatus');

        return $this;
    }

    public function getCurrentWorkflow(): ?Workflow
    {
        return $this->usingWorkflow ?? $this->getDefaultWorkflow();
    }

    /**
     * The current WorkflowModelStatus for the active workflow (see method usingWorkflow())
     *
     * @throws Throwable
     */
    public function modelStatus(): MorphOne
    {
        return $this->morphOne(WorkflowModelStatus::class, 'model')
            ->where('workflow_id', $this->getCurrentWorkflow()?->id);
    }

    /**
     * The current WorkflowModelStatus for all the workflows
     *
     * @throws Throwable
     */
    public function modelStatuses(): MorphMany
    {
        return $this->morphMany(WorkflowModelStatus::class, 'model');
    }

    /**
     * @throws Throwable
     */
    public function allModelStatus(): MorphMany
    {
        return $this->morphMany(WorkflowModelStatus::class, 'model')
            ->where('workflow_id', $this->getCurrentWorkflow()?->id)
            ->withTrashed();
    }

    public function getStatus(): ?WorkflowStatus
    {
        return $this->modelStatus?->status;
    }

    public function isInStatus(string|BackedEnum $statusName): bool
    {
        return $this->getStatus()->name === ($statusName instanceof BackedEnum ? $statusName->value : $statusName);
    }

    public function scopeInStatus(
        Builder $query,
        int|array|BackedEnum|WorkflowStatus|Collection $status,
        int|string|Workflow $workflow = null,
    ): Builder {
        $workflow = is_string($workflow)
            ? Workflow::findWithName($workflow)
            : ($workflow ?? $this->getDefaultWorkflow());

        throw_unless($workflow, Exception::class, "Workflow '$workflow' not found and/or no default workflow defined.");

        $statuses = $status instanceof BackedEnum
            ? [WorkflowStatus::findWithName($status->value)?->id]
            : ModelHelper::toIdsArray($status);

        return $query->whereHas('modelStatuses', function (Builder $query) use ($statuses, $workflow) {
            $query->where('workflow_id', is_int($workflow) ? $workflow : $workflow->id)
                ->whereIn('workflow_status_id', $statuses);
        });
    }

    public function possibleTransitions(): \Illuminate\Support\Collection
    {
        throw_unless($this->getCurrentWorkflow(), Exception::class, 'Select a workflow before calling '.__FUNCTION__);

        return TransitionService::possibleTransitions($this->modelStatus, Auth::user());
    }

    protected function getTransitionTo(WorkflowStatus $status): ?WorkflowTransition
    {
        return WorkflowTransition::forWorkflow($this->getCurrentWorkflow())
            ->fromTo($this->modelStatus?->status, $status)
            ->first();
    }

    public function isAllowed(WorkflowStatus|WorkflowTransition $status): bool
    {
        $transition = $status instanceof WorkflowTransition
            ? $this->getTransitionTo($status->toStatus)
            : $this->getTransitionTo($status);

        return $transition && TransitionService::isAllowed($transition, Auth::user());
    }

    public function transition(WorkflowTransition $transition): static
    {
        return $this->transitionTo($transition->toStatus);
    }

    /**
     * @throws \Squarebit\Workflows\Exceptions\InvalidTransitionException
     * @throws \Squarebit\Workflows\Exceptions\UnauthorizedTransitionException
     */
    public function transitionTo(WorkflowStatus $status): static
    {
        throw_unless($transition = $this->getTransitionTo($status), InvalidTransitionException::class);
        throw_unless($this->isAllowed($transition), UnauthorizedTransitionException::class);

        $this->modelStatus?->delete();
        $this->createModelStatus(Workflow::findOrFail($this->getCurrentWorkflow()->id), $status);

        return $this->unsetRelations();
    }

    public function isInFinalStatus(): bool
    {
        return $this->possibleTransitions()->count() === 0;
    }

    protected function createModelStatus(Workflow $workflow, WorkflowStatus $status): WorkflowModelStatus
    {
        $modelStatus = new WorkflowModelStatus();
        $modelStatus->model()->associate($this);
        $modelStatus->user()->associate(Auth::user());
        $modelStatus->workflow()->associate($workflow);
        $modelStatus->status()->associate($status);
        $modelStatus->save();

        return $modelStatus;
    }
}
