<?php

namespace Squarebit\Workflows\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Squarebit\Workflows\Models\WorkflowTransition;

/**
 * @extends Factory<WorkflowTransition>
 */
class WorkflowTransitionFactory extends Factory
{
    protected $model = WorkflowTransition::class;

    public function definition(): array
    {
        return [
            /** @phpstan-ignore-next-line */
            'workflow_id' => fn() => WorkflowFactory::new()->create()->id,
            /** @phpstan-ignore-next-line */
            'from_id' => fn() => WorkflowStatusFactory::new()->create()->id,
            /** @phpstan-ignore-next-line */
            'to_id' => fn() => WorkflowStatusFactory::new()->create()->id,
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
