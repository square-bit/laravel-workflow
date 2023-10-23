<?php

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Squarebit\Workflows\Database\Factories\WorkflowFactory;
use Squarebit\Workflows\Database\Factories\WorkflowTransitionFactory;
use Squarebit\Workflows\Exceptions\UnauthorizedTransitionException;
use Squarebit\Workflows\Tests\Support\WorkflowableModel;

beforeEach(function () {

    Permission::findOrCreate('PERM_A', 'web');
    Permission::findOrCreate('PERM_B', 'web');

    $this->workflow = $workflow = WorkflowFactory::new()->create();
    // 1 entry
    $entry = WorkflowTransitionFactory::new()->workflow($workflow->id)->entry()->create();
    $this->mid1 = $mid1 = WorkflowTransitionFactory::new()->workflow($workflow->id)->from($entry->to_id)->create();
    $this->mid2 = $mid2 = WorkflowTransitionFactory::new()->workflow($workflow->id)->from($mid1->to_id)->create();
    // 2 exits
    WorkflowTransitionFactory::new()->workflow($workflow->id)->from($mid1->to_id)->exit()->create();
    WorkflowTransitionFactory::new()->workflow($workflow->id)->from($mid2->to_id)->exit()->create();
});

test('it can transition if no permissions are defined', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    /** @var \Squarebit\Workflows\Models\WorkflowTransition $transition */
    $transition = $model->possibleTransitions()->first();

    $model->transition($transition);
    expect($model->modelStatus->status)->toEqual($transition->toStatus);
});

test('it cannot transition if user is missing necessary permissions', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    /** @var \Squarebit\Workflows\Models\WorkflowTransition $transition */
    $transition = $model->possibleTransitions()->first();

    $transition->givePermissionTo('PERM_A');
    expect(fn () => $model->transition($transition))
        ->toThrow(UnauthorizedTransitionException::class);
});

test('it can transition when user has necessary permission', function () {
    ($model = new WorkflowableModel())->setDefaultWorkflowName($this->workflow->name)->save();
    /** @var \Squarebit\Workflows\Models\WorkflowTransition $transition */
    $transition = $model->possibleTransitions()->first();

    $transition->givePermissionTo('PERM_B', 'PERM_A');
    Auth::user()?->givePermissionTo('PERM_A')->givePermissionTo('PERM_B');
    expect(fn () => $model->transition($transition))
        ->not()->toThrow(UnauthorizedTransitionException::class);
});
