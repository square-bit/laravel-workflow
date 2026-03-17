<?php

use App\Models\User;
use Squarebit\Workflows\Models\WorkflowModelStatus;

// config for Squarebit/Workflows

return [
    /** @phpstan-ignore-next-line  */
    'user_model' => User::class,
    'workflow_model_status_class' => WorkflowModelStatus::class,
    'allow_guests_to_transition' => false,
];
