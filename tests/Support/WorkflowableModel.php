<?php

namespace Squarebit\Workflows\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Squarebit\Workflows\Contracts\Workflowable;
use Squarebit\Workflows\Traits\HasWorkflows;

class WorkflowableModel extends Model implements Workflowable
{
    use HasWorkflows;

    protected ?string $defaultWorkflowName = null;

    public function setDefaultWorkflowName(?string $defaultWorkflowName): self
    {
        $this->defaultWorkflowName = $defaultWorkflowName;

        return $this;
    }

    public function getDefaultWorkflowName(): ?string
    {
        return $this->defaultWorkflowName;
    }
}
