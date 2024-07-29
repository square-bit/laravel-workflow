<?php

// config for Squarebit/Workflows

return [
    /** @phpstan-ignore-next-line  */
    'user_model' => App\Models\User::class,
    'workflow_model_status_class' => Squarebit\Workflows\Models\WorkflowModelStatus::class,
    'allow_guests_to_transition' => false,
];
