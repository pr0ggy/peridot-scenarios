<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\Core\Scope;
use Closure;

class Scenario
{
    /**
     * @var callable
     */
    protected $setup_func;

    protected $teardown_func;

    /**
     * @param array $context_value_map
     */
    public function __construct(callable $setup_func, callable $teardown_func)
    {
        $this->setup_func = $setup_func;
        $this->teardown_func = $teardown_func;
    }

    public function setUpFunctionBoundTo(Scope $scope)
    {
        return Closure::bind($this->setup_func, $scope, $scope);
    }

    public function tearDownFunctionBoundTo(Scope $scope)
    {
        return Closure::bind($this->teardown_func, $scope, $scope);
    }
}
