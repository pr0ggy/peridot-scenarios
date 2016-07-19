<?php

namespace Peridot\Plugin\Scenarios\Test;

use Mockery as m;
use SplObjectStorage;
use Peridot\Core\AbstractTest;
use Peridot\Plugin\Scenarios\Test\Doubles;

function createFakeTest()
{
    $fake_test = m::mock('Peridot\Core\AbstractTest')->shouldIgnoreMissing();
    $fake_test_scope = m::mock('Peridot\Core\Scope')->shouldIgnoreMissing();
    $fake_test->shouldReceive('getScope')->andReturn($fake_test_scope);
    return $fake_test;
}

function createFakeTestWithScope($scope)
{
    $fake_test = m::mock('Peridot\Core\AbstractTest')->shouldIgnoreMissing();
    $fake_test->shouldReceive('getScope')->andReturn($scope);
    return $fake_test;
}

function createFakeTestWithSetupAndTeardownActions(array $setups, array $teardowns)
{
    $fake_test = m::mock('Peridot\Core\AbstractTest')->shouldIgnoreMissing();
    $fake_test->shouldReceive('getSetupFunctions')->andReturn($setups);
    $fake_test->shouldReceive('getTearDownFunctions')->andReturn($teardowns);
    return $fake_test;
}

function createFakeScenarioContextAction()
{
    return m::mock('Peridot\Plugin\Scenarios\ScenarioContextAction');
}

function createFakeScenario()
{
    return m::mock('\Peridot\Plugin\Scenarios\Scenario')->shouldIgnoreMissing();
}

function createFakeScenarioWithSetupAndTeardownFuncs(callable $setup, callable $teardown)
{
    $fake_scenario = createFakeScenario();
    $fake_scenario->shouldReceive('executeSetupInContext')->andReturnUsing($setup);
    $fake_scenario->shouldReceive('executeTeardownInContext')->andReturnUsing($teardown);
    return $fake_scenario;
}

function createReporterTestFailureDetailSpy($test_scope, SplObjectStorage $initial_test_failure_map = null)
{
    if (isset($initial_test_failure_map) === false) {
        $initial_test_failure_map = new SplObjectStorage();
    }

    return new Doubles\ReporterTestFailureDetailsSpy(
        $test_scope->fake_event_emitter,
        $test_scope->fake_output_interface,
        $initial_test_failure_map
    );
}

function getLeafOfSimpleDescriptionTestHeirarchyOfDepth($depth, $test_scope)
{
    $node_count = 1;
    $active_node = createFakeTest();
    $active_node->shouldReceive('getDescription')->andReturn("Test {$node_count} Description");
    $active_node->shouldReceive('walkUp')->passthru();

    while ($node_count < $depth) {
        ++$node_count;
        $this_node = createFakeTest();
        $this_node->shouldReceive('getDescription')->andReturn("Test {$node_count} Description");
        $this_node->shouldReceive('getParent')->andReturn($active_node);
        $active_node->shouldReceive('walkUp')->passthru();
        $active_node = $this_node;
    }

    return $active_node;
}
