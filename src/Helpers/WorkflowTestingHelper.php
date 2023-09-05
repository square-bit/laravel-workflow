<?php

namespace Squarebit\Workflows\Helpers;

use Squarebit\Workflows\Contracts\Workflowable;
use Squarebit\Workflows\Models\WorkflowStatus;

trait WorkflowTestingHelper
{
    protected function inState(string $stateName)
    {
        return $this->afterCreating(function (Workflowable $workflowable) use ($stateName) {
            $workflowable->modelStatus->status()->associate(WorkflowStatus::findWithName($stateName));
            $workflowable->modelStatus->save();
        });
    }
}
