<?php

namespace Peridot\Plugin\Scenarios\Test;

use Mockery as m;
use Peridot\Core\AbstractTest;

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
