<?php

use Illuminate\Support\Facades\Auth;
use Squarebit\Workflows\Database\Factories\WorkflowFactory;
use Squarebit\Workflows\Database\Factories\WorkflowTransitionFactory;
use Squarebit\Workflows\Exceptions\InvalidTransitionException;
use Squarebit\Workflows\Models\WorkflowModelStatus;
use Squarebit\Workflows\Models\WorkflowStatus;
use Squarebit\Workflows\Tests\Support\WorkflowableModel;

beforeEach(function () {
    $this->workflow = $workflow = WorkflowFactory::new()->create();
    // 1 entry
    $this->entry = $entry = WorkflowTransitionFactory::new()->workflow($workflow->id)->entry()->create();
    $this->mid1 = $mid1 = WorkflowTransitionFactory::new()->workflow($workflow->id)->from($entry->to_id)->create();
    $mid2 = WorkflowTransitionFactory::new()->workflow($workflow->id)->from($mid1->to_id)->create();
    // 2 exits
    WorkflowTransitionFactory::new()->workflow($workflow->id)->from($mid1->to_id)->exit()->create();
    WorkflowTransitionFactory::new()->workflow($workflow->id)->from($mid2->to_id)->exit()->create();
});

test('it has correct relationships', function () {
    expect($this->workflow)
        ->transitions->count()->toBe(5)
        ->entryTransitions->count()->toBe(1)
        ->exitTransitions->count()->toBe(2)
        ->and($this->workflow->transitions->first())
        ->fromStatus->toBeNull()
        ->toStatus->toBeInstanceOf(WorkflowStatus::class)
        ->and($this->workflow->transitions->last())
        ->fromStatus->toBeInstanceOf(WorkflowStatus::class)
        ->toStatus->toBeNull();
});

test('it creates the first status for a newly created model', function () {
    (new WorkflowableModel())->save();
    expect(WorkflowModelStatus::count())->toBe(0);

    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    expect(WorkflowModelStatus::first())
        ->count()->toBe(1)
        ->model->id->toBe($model->id);
});

test('it gets the status(es) of a model', function () {
    ($modelA = new WorkflowableModel())->save();
    expect($modelA->modelStatuses)->toHaveCount(0);

    ($modelB = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    $status = $modelB->getDefaultWorkflow()->entryTransitions->first()->toStatus;

    expect($modelB->modelStatus->status)->toEqual($modelB->getStatus())->toEqual($status)
        ->and($modelB->modelStatus->workflow)->toEqual($modelB->getDefaultWorkflow())
        ->and($modelB->modelStatuses)->toHaveCount(1);
});

test('it can transition a model', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    $transitions = $model->possibleTransitions();

    expect($transitions)->toHaveCount(1);

    $model->transition($transitions->first());
    expect($model->modelStatus->status)->toEqual($transitions->first()->toStatus)
        ->and($model->modelStatuses)->toHaveCount(1)
        ->and($model->allModelStatus)->toHaveCount(2);
});

test('it fails transition if not valid', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    $transitions = $model->possibleTransitions();

    expect(fn () => $model->transitionTo($transitions->first()->fromStatus))
        ->toThrow(InvalidTransitionException::class);
});

test('it can filter model with specific status', function () {
    (new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();

    expect(WorkflowableModel::inStatus( $this->entry->toStatus, $this->workflow)->get())->toHaveCount(1)
        ->and(WorkflowableModel::inStatus( $this->mid1->toStatus->id, $this->workflow)->get())->toHaveCount(0);
});

test('it checks if a transition is allowed', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();

    expect($model->isAllowed($this->mid1))->toBeTrue()
        ->and($model->isAllowed($this->entry->toStatus))->toBeFalse();

    Auth::logout();

    expect($model->isAllowed($this->mid1))->toBeFalse();
});

test('it has toString', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();

    expect($model->getCurrentWorkflow()?->__toString())->toBe($model->getCurrentWorkflow()->name);
    expect($model->possibleTransitions()->first()->__toString())->toContain($model->getCurrentWorkflow()?->__toString());
});
