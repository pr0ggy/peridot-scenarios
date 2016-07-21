<?php

namespace Peridot\Plugin\Scenarios;

use Closure;

/**
 * Represents a callable setup or teardown action associated with a Scenario instance
 *
 * @package Peridot\Plugin\Scenarios
 */
class ScenarioContextAction
{
    /**
     * @var callable
     */
    protected $action_callable;

    /**
     * @param callable $action_callable
     */
    public function __construct(callable $action_callable)
    {
        $this->action_callable = $action_callable;
    }

    /**
     * Executes the action's callable bound to the given context argument
     *
     * @param  mixed $context
     * @return mixed
     */
    public function executeInContext($context)
    {
        $bound_action = Closure::bind($this->action_callable, $context);
        return $bound_action();
    }

    /**
     * Invokes the action's callable
     * @return mixed
     */
    public function __invoke()
    {
        $action = $this->action_callable;
        return $action();
    }
}
