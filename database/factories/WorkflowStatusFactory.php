<?php

namespace Squarebit\Workflows\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Squarebit\Workflows\Models\WorkflowStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<WorkflowStatus>
 */
class WorkflowStatusFactory extends Factory
{
    protected $model = WorkflowStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->firstName,
            'description' => fake()->text,
        ];
    }
}
