<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\Core\Scope;

/**
 * Represents a specific context setup/teardown to be applied to a given test definition
 * to represent an execution of the test in a given scenario.
 *
 * @package  Peridot\Plugin\Scenarios
 */
class Scenario
{
    /**
     * @var ScenarioContextAction
     */
    protected $setup;

    /**
     * @var ScenarioContextAction
     */
    protected $teardown;

    /**
     * @param ScenarioContextAction $setup
     * @param ScenarioContextAction $teardown
     */
    public function __construct(ScenarioContextAction $setup, ScenarioContextAction $teardown)
    {
        $this->setup = $setup;
        $this->teardown = $teardown;
    }

    /**
     * Executes the Scenario instance's setup action with the action context bound to
     * the given Scope argument
     *
     * @param Scope $scope
     */
    public function executeSetupInContext(Scope $scope)
    {
        $context_bound_setup = $this->setup->inContext($scope);
        $context_bound_setup();
    }

    /**
     * Executes the Scenario instance's teardown function with the action context bound to
     * the given Scope argument
     *
     * @param Scope $scope
     */
    public function executeTeardownInContext(Scope $scope)
    {
        $context_bound_teardown = $this->teardown->inContext($scope);
        $context_bound_teardown();
    }
}
