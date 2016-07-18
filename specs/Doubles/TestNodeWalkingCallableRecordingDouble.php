<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Peridot\Core\Test;

/**
 * Peridot\Core\Test double which simply records the callback arguments provided to the
 * walkUp and walkdDown methods
 *
 * @package Peridot\Plugin\Scenarios\Test\Doubles
 */
class TestNodeWalkingCallableRecordingDouble extends Test
{
    protected $last_walkup_execution_callback;
    protected $last_walkdown_execution_callback;

    public function walkUp(callable $callback)
    {
        $this->last_walkup_execution_callback = $callback;
    }

    public function walkDown(callable $callback)
    {
        $this->last_walkdown_execution_callback = $callback;
    }

    public function getLastWalkUpExecutionCallback()
    {
        return $this->last_walkup_execution_callback;
    }

    public function getLastWalkDownExecutionCallback()
    {
        return $this->last_walkdown_execution_callback;
    }
}
