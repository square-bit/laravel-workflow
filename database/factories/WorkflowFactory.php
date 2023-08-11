<?php

namespace Squarebit\Workflows\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Squarebit\Workflows\Models\Workflow;

/**
 * @extends Factory<Workflow>
 */
class WorkflowFactory extends Factory
{
    protected $model = Workflow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->text(32),
        ];
    }
}
