<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\Core\Scope;
use Closure;

/**
 * Represents a specific context setup/teardown to be applied to a given test definition to
 * represent an execution of the test in a given scenario.
 *
 * @package  Peridot\Plugin\Scenarios
 */
class Scenario
{
    /**
     * @var callable
     */
    protected $setup_func;

    /**
     * @var callable
     */
    protected $teardown_func;

    /**
     * @param callable $setup_func
     * @param callable $teardown_func
     */
    public function __construct(callable $setup_func, callable $teardown_func)
    {
        $this->setup_func = $setup_func;
        $this->teardown_func = $teardown_func;
    }

    /**
     * Returns the Scenario instance's setup function with the function's $this context bound to
     * the given Scope argument
     *
     * @param Scope $scope
     * @return callable
     */
    public function setUpFunctionBoundTo(Scope $scope)
    {
        return Closure::bind($this->setup_func, $scope, $scope);
    }

    /**
     * Returns the Scenario instance's teardown function with the function's $this context bound to
     * the given Scope argument
     *
     * @param Scope $scope
     * @return callable
     */
    public function tearDownFunctionBoundTo(Scope $scope)
    {
        return Closure::bind($this->teardown_func, $scope, $scope);
    }
}
