<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\AbstractTest;

class Reporter
{
    use HasEventEmitterTrait;

    public function __construct(EventEmitterInterface $event_emitter)
    {
        $this->eventEmitter = $event_emitter;
        // $this->registerEventHandlers();
        $this->eventEmitter->on('peridot.reporters', [$this, 'registerEventHandlers']);
    }

    public function registerEventHandlers()
    {
        $this->eventEmitter->on('test.failed', [$this, 'whenTestFails']);
        $this->eventEmitter->on('test.passed', [$this, 'whenTestPasses']);
    }

    public function whenTestFails(AbstractTest $test, \Exception $e)
    {
        if ($test->explicitly_defined_scenario_count < 2) {
            return;
        }

        echo
            isset($e->failed_scenario_index)
                ? "SCENARIO {$e->failed_scenario_index} FAILED\n"
                : "LAST SCENARIO FAILED\n";
    }

    public function whenTestPasses(AbstractTest $test)
    {
        return; // do we even want to show passing scenario counts?

        if ($test->explicitly_defined_scenario_count === 0) {
            return;
        }

        $scenario_word = 'Scenario';
        if ($test->explicitly_defined_scenario_count > 1) {
            $scenario_word .= 's';
        }
        echo "{$test->explicitly_defined_scenario_count} {$scenario_word} Passed\n";
    }
}
