<?php

namespace Squarebit\Workflows\Contracts;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection as SupportCollection;
use Squarebit\Workflows\Exceptions\InvalidTransitionException;
use Squarebit\Workflows\Exceptions\UnauthorizedTransitionException;
use Squarebit\Workflows\Models\Workflow;
use Squarebit\Workflows\Models\WorkflowStatus;
use Squarebit\Workflows\Models\WorkflowTransition;
use Throwable;

interface Workflowable
{
    public function initWorkflow(int|Workflow $workflow): static;

    public function getDefaultWorkflowName(): ?string;

    public function getDefaultWorkflow(): ?Workflow;

    public function usingWorkflow(null|int|Workflow $workflow): static;

    public function getCurrentWorkflow(): ?Workflow;

    /**
     * The current WorkflowModelStatus for the active workflow (see method usingWorkflow())
     *
     * @throws Throwable
     */
    public function modelStatus(): MorphOne;

    /**
     * The current WorkflowModelStatus for all the workflows
     *
     * @throws Throwable
     */
    public function modelStatuses(): MorphMany;

    /**
     * @throws Throwable
     */
    public function allModelStatus(): MorphMany;

    public function getStatus(): ?WorkflowStatus;

    public function isInStatus(string|BackedEnum $statusName): bool;

    public function scopeInWorkflow(
        Builder $query,
        int|string|Workflow $workflow,
    ): Builder;

    public function scopeInStatus(
        Builder $query,
        int|array|BackedEnum|WorkflowStatus|SupportCollection $status,
        int|string|Workflow|null $workflow = null,
    ): Builder;

    public function possibleTransitions(): SupportCollection;

    public function isAllowed(WorkflowStatus|WorkflowTransition $status): bool;

    public function transition(WorkflowTransition $transition): static;

    /**
     * @throws InvalidTransitionException
     * @throws UnauthorizedTransitionException
     */
    public function transitionTo(WorkflowStatus $status): static;

    public function isInFinalStatus(): bool;
}
