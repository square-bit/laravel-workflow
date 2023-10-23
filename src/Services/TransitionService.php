<?php

namespace Squarebit\Workflows\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Squarebit\Workflows\Models\Workflow;
use Squarebit\Workflows\Models\WorkflowModelStatus;
use Squarebit\Workflows\Models\WorkflowStatus;
use Squarebit\Workflows\Models\WorkflowTransition;

class TransitionService
{
    /**
     * @return Collection<int, WorkflowTransition>
     */
    public static function possibleTransitions(WorkflowModelStatus $modelStatus, Authenticatable $user = null): Collection
    {
        return WorkflowTransition::query()
            ->where('workflow_id', $modelStatus->workflow_id)
            ->where('from_id', $modelStatus->workflow_status_id)
            ->get()
            ->filter(fn (WorkflowTransition $transition) => self::isAllowed($transition, $user));
    }

    public static function isAllowed(WorkflowTransition $transition, Authenticatable $user = null): bool
    {
        $user = $user ?? Auth::user();

        if ($user === null) {
            return (bool) config('workflow.allow_guests_to_transition');
        }

        $requiredPermissions = $transition->getAllPermissions();
        if (count($requiredPermissions) === 0) {
            return true;
        }

        return method_exists($user, 'hasAllPermissions')
            ? $user->hasAllPermissions($requiredPermissions)
            : true;
    }

    public static function getWorkflowStartStatus(Workflow $workflow): WorkflowStatus
    {
        /** @var \Squarebit\Workflows\Models\WorkflowTransition $transition */
        $transition = WorkflowTransition::forWorkflow($workflow)
            ->whereNull('from_id')
            ->first();

        return $transition->toStatus;
    }
}
