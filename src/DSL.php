<?php
/**
 * Defines extension functions to the default Peridot DSL to aid in writing scenario-based tests
 */

use Peridot\Plugin\Scenarios;

/**
 * Peridot DSL extension which binds a specific scenario to a test.  A test definition will run once
 * for each scenario.  See ScenarioFactory and Scenario for valid scenario setups.  See
 * ContextListener::whenTestStarted() method and ScenarioComposite class for details on how scenarios
 * are hooked into test execution.
 *
 * @param  callable|array $scenario_setup
 * @param  callable|null  $scenario_teardown
 * @throws RuntimeException if setup argument is not array or callable
 * @throws TypeError if teardown argument is not callable
 *
 * @see ScenarioFactory
 * @see Scenario
 */
function inScenario($scenario_setup, callable $scenario_teardown = null)
{
    Scenarios\Plugin::getInstance()
        ->registerNewScenario($scenario_setup, $scenario_teardown);
}

/**
 * Simple readability function, just returns given argument
 */
function setUp($setup_fn)
{
    return $setup_fn;
}

/**
 * Simple readability function, just returns given argument
 */
function tearDown($tearDown_fn)
{
    return $tearDown_fn;
}
