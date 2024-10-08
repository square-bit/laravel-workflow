<?php

namespace Squarebit\Workflows\Contracts;

use Squarebit\Workflows\Models\Workflow;

interface Workflowable
{
    public function initWorkflow(int|Workflow $workflow): static;

    public function getDefaultWorkflowName(): ?string;

    public function getDefaultWorkflow(): ?Workflow;
}
