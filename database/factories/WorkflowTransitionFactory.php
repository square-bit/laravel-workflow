<?php

namespace Squarebit\Workflows\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Squarebit\Workflows\Models\WorkflowTransition;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Squarebit\Workflows\Models\WorkflowTransition>
 */
class WorkflowTransitionFactory extends Factory
{
    protected $model = WorkflowTransition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => WorkflowFactory::new()->create()->id,
            'from_id' => WorkflowStatusFactory::new()->create()->id,
            'to_id' => WorkflowStatusFactory::new()->create()->id,
            'order' => random_int(1, 999),
        ];
    }

    public function workflow(int $id): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_id' => $id,
        ]);
    }

    public function from(?int $workflowStatusId): static
    {
        return $this->state(fn (array $attributes) => [
            'from_id' => $workflowStatusId,
        ]);
    }

    public function to(?int $workflowStatusId): static
    {
        return $this->state(fn (array $attributes) => [
            'to_id' => $workflowStatusId,
        ]);
    }

    public function entry(): static
    {
        return $this->from(null);
    }

    public function exit(): static
    {
        return $this->to(null);
    }
}
