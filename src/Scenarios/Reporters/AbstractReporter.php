<?php

namespace Peridot\Plugin\Scenarios\Reporters;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\AbstractTest;
use Peridot\Reporter\AbstractBaseReporter ;

abstract class AbstractReporter
{
    use HasEventEmitterTrait;

    public function __construct(EventEmitterInterface $event_emitter)
    {
        $this->eventEmitter = $event_emitter;
        $this->eventEmitter->on('runner.start', [$this, 'registerEventHandlers']);
    }

    public function registerEventHandlers()
    {
        $this->eventEmitter->on('test.failed', [$this, 'whenTestFails']);
    }

    public function whenTestFails(AbstractTest $test, \Exception $e)
    {
        if ($test->explicitly_defined_scenario_count < 2) {
            return;
        }

        $failed_scenario_message =
            isset($e->failed_scenario_index)
                ? "SCENARIO {$e->failed_scenario_index} FAILED\n"
                : "LAST SCENARIO FAILED\n";

        $this->eventEmitter->emit(
            'reporter.customOutputRequest',
            $this->getScenarioFailureReportingCallbackForMessage($failed_scenario_message)
        );
    }

    abstract public function getScenarioFailureReportingCallbackForMessage($scenario_failure_message);
}
