<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Peridot\Core\Test;

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
