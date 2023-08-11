<?php

namespace Squarebit\Workflows\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Squarebit\Workflows\Models\WorkflowStatus;

/**
 * @extends Factory<WorkflowStatus>
 */
class WorkflowStatusFactory extends Factory
{
    protected $model = WorkflowStatus::class;

    public function definition(): array
    {
        return [
            'code' => fake()->firstName,
            'description' => fake()->text,
        ];
    }
}
