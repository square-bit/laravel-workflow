<?php

namespace Squarebit\Workflows\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Squarebit\Workflows\Contracts\Workflowable;
use Squarebit\Workflows\Exceptions\InvalidTransitionException;
use Squarebit\Workflows\Exceptions\UnauthorizedTransitionException;
use Squarebit\Workflows\Models\Workflow;
use Squarebit\Workflows\Models\WorkflowModelStatus;
use Squarebit\Workflows\Models\WorkflowStatus;
use Squarebit\Workflows\Models\WorkflowTransition;
use Squarebit\Workflows\Services\TransitionService;
use Throwable;

/**
 * @property WorkflowModelStatus $currentStatus
 * @property Collection $currentStatuses
 * @property Collection $allWorkflowStatuses
 */
trait HasWorkflows
{
    protected ?Workflow $usingWorkflow;

    public static function bootHasWorkflows(): void
    {
        static::created(function (Workflowable $model) {
            if (($workflow = $model->getDefaultWorkflow()) === null) {
                return;
            }

            $model->createModelStatus($workflow, TransitionService::getWorkflowStartStatus($workflow));

            $model->refresh();
        });
    }

    public function getDefaultWorkflow(): ?Workflow
    {
        $name = $this->getDefaultWorkflowName();
        $workflow = Workflow::where('name', $name)->first();
        $this->usingWorkflow = $workflow;

        return $workflow;
    }

    public function usingWorkflow(null|int|Workflow $workflow): static
    {
        $this->usingWorkflow = $workflow instanceof Workflow ? $workflow : Workflow::find($workflow);
        $this->unsetRelations();

        return $this;
    }

    public function getCurrentWorkflow(): ?Workflow
    {
        return $this->usingWorkflow;
    }

    /**
     * The current WorkflowModelStatus for the active workflow (see method usingWorkflow())
     *
     * @throws Throwable
     */
    public function currentStatus(): MorphOne
    {
        throw_unless($this->usingWorkflow, Exception::class, 'Select a workflow before calling '.__FUNCTION__);

        return $this->morphOne(WorkflowModelStatus::class, 'model')
            ->where('workflow_id', $this->usingWorkflow->id);
    }

    /**
     * The current WorkflowModelStatus for all the workflows
     *
     * @throws Throwable
     */
    public function currentStatuses(): MorphMany
    {
        return $this->morphMany(WorkflowModelStatus::class, 'model');
    }

    /**
     * @throws Throwable
     */
    public function allWorkflowStatuses(): MorphMany
    {
        throw_unless($this->usingWorkflow, Exception::class, 'Select a workflow before calling '.__FUNCTION__);

        return $this->currentStatuses()
            ->withTrashed();
    }

    public function scopeInStatus(Builder $query, Workflow $workflow, int|array|WorkflowStatus|Collection $statusIds): Builder
    {
        $statuses = collect(Arr::wrap($statusIds));
        if ($statuses->first() instanceof WorkflowStatus) {
            $statuses = $statuses->map->id;
        }

        return $query->whereHas('currentStatuses', function (Builder $query) use ($statuses, $workflow) {
            $query->where('workflow_id', $workflow->id)
                ->whereIn('workflow_status_id', $statuses);
        });
    }

    public function availableTransitions(): \Illuminate\Support\Collection
    {
        throw_unless($this->usingWorkflow, Exception::class, 'Select a workflow before calling '.__FUNCTION__);

        return TransitionService::availableTransitions($this->currentStatus, Auth::user());
    }

    protected function getTransitionTo(WorkflowStatus $status): ?WorkflowTransition
    {
        return WorkflowTransition::forWorkflow($this->usingWorkflow)
            ->fromTo($this->currentStatus?->status, $status)
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

        $this->currentStatus?->delete();
        $this->createModelStatus(Workflow::findOrFail($this->usingWorkflow->id), $status);

        return $this->unsetRelations();
    }

    public function isInFinalStatus(): bool
    {
        return $this->availableTransitions()->count() === 0;
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
