<?php

use Squarebit\Workflows\Database\Factories\WorkflowFactory;
use Squarebit\Workflows\Database\Factories\WorkflowTransitionFactory;
use Squarebit\Workflows\Tests\Support\WorkflowableModel;

beforeEach(function () {
    /**
     * FLOW X (where X=A or X=B)
     * entryX -> X1 -> X2 -> exitX
     *              -> exitX
     */
    $this->workflowA = $workflowA = WorkflowFactory::new()->create();
    // 1 entry
    $this->entryA = $entry = WorkflowTransitionFactory::new()->workflow($workflowA->id)->entry()->create();
    $this->entryA_to_A1 = $mid1 = WorkflowTransitionFactory::new()->workflow($workflowA->id)->from($entry->to_id)->create();
    $this->A1_to_A2 = $mid2 = WorkflowTransitionFactory::new()->workflow($workflowA->id)->from($mid1->to_id)->create();
    // 2 exits
    $this->A1_to_exitA = WorkflowTransitionFactory::new()->workflow($workflowA->id)->from($mid1->to_id)->create();
    $this->A2_to_exitA = WorkflowTransitionFactory::new()->workflow($workflowA->id)->from($mid2->to_id)->create();

    $this->workflowB = $workflowB = WorkflowFactory::new()->create();
    // 1 entry
    $this->entryB = $entry = WorkflowTransitionFactory::new()->workflow($workflowB->id)->entry()->create();
    $this->entryB_to_B1 = $mid1 = WorkflowTransitionFactory::new()->workflow($workflowB->id)->from($entry->to_id)->create();
    $this->B1_to_B2 = $mid2 = WorkflowTransitionFactory::new()->workflow($workflowB->id)->from($mid1->to_id)->create();
    // 2 exits
    $this->B1_to_exitB = WorkflowTransitionFactory::new()->workflow($workflowB->id)->from($mid1->to_id)->create();
    $this->B2_to_exitB = WorkflowTransitionFactory::new()->workflow($workflowB->id)->from($mid2->to_id)->create();
});

test('it supports multiple models', function () {
    ($modelA = new WorkflowableModel())->setDefaultWorkflowName($this->workflowA->name)->save();
    ($modelB = new WorkflowableModel())->setDefaultWorkflowName($this->workflowB->name)->save();

    expect($modelA->currentStatus->workflow)->id->toBe($this->workflowA->id);
    expect($modelB->currentStatus->workflow)->id->toEqual($this->workflowB->id);

    expect($modelA->transition($this->entryA_to_A1))
        ->currentStatus->status->toEqual($this->entryA_to_A1->toStatus);
    expect($modelB->transition($this->entryB_to_B1))
        ->currentStatus->status->toEqual($this->entryB_to_B1->toStatus);

    expect($modelA->availableTransitions())->toHaveCount(2);
    expect($modelB->availableTransitions())->toHaveCount(2);

    expect($modelA->transition($this->A1_to_exitA))
        ->currentStatus->status->toEqual($this->A1_to_exitA->toStatus);
    expect($modelB->transition($this->B1_to_B2))
        ->currentStatus->status->toEqual($this->B1_to_B2->toStatus);

    expect($modelA->availableTransitions())->toHaveCount(0);
    expect($modelB->availableTransitions())->toHaveCount(1);

    expect($modelA->isInFinalStatus())->toBeTrue();
    expect($modelB->isInFinalStatus())->toBeFalse();
});

test('a model can have multiple workflows', function () {
    ($modelA = new WorkflowableModel())->setDefaultWorkflowName($this->workflowA->name)->save();
    $modelA->transition($this->entryA_to_A1);

    expect($modelA->usingWorkflow($this->workflowB)->getCurrentWorkflow())
        ->toEqual($this->workflowB);

    expect(fn () => $modelA->transition($this->entryB))
        ->not()->toThrow(Exception::class);
    expect($modelA->currentStatus->status->id)->toBe($this->entryB->toStatus->id);

    expect(WorkflowableModel::inStatus($this->workflowA, $this->entryA_to_A1->toStatus)->get())->toHaveCount(1)
        ->and(WorkflowableModel::inStatus($this->workflowB, $this->entryA_to_A1->toStatus)->get())->toHaveCount(0);
    expect(WorkflowableModel::inStatus($this->workflowB, $this->entryB->toStatus)->get())->toHaveCount(1)
        ->and(WorkflowableModel::inStatus($this->workflowA, $this->entryB->toStatus)->get())->toHaveCount(0);

    $modelA->usingWorkflow($this->workflowA);
    expect($modelA->currentStatus->status->id)->toBe($this->entryA_to_A1->toStatus->id);
});