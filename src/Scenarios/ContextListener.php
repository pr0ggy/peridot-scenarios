<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\AbstractTest;
use Peridot\Core\Suite;

class ContextListener
{
    use HasEventEmitterTrait;

    protected $test_scenario_map;

    protected $last_test_added;

    protected $reporter;

    public function __construct(EventEmitterInterface $event_emitter, Reporter $reporter)
    {
        $this->eventEmitter = $event_emitter;
        $this->registerEventHandlers();
        $this->reporter = $reporter;
        $this->test_scenario_map = new \SplObjectStorage();
    }

    protected function registerEventHandlers()
    {
        $this->eventEmitter->on('test.added', [$this, 'whenTestAdded']);
        $this->eventEmitter->on('scenario.created', [$this, 'whenScenarioCreated']);
        $this->eventEmitter->on('suite.define', [$this, 'exitActiveTestContext']);
        $this->eventEmitter->on('suite.wasDefined', [$this, 'exitActiveTestContext']);
        $this->eventEmitter->on('test.start', [$this, 'whenTestStarted']);
    }

    public function whenTestAdded(AbstractTest $test)
    {
        $this->test_scenario_map[$test] = [];
        $this->enterActiveTestContext($test);
    }

    protected function enterActiveTestContext(AbstractTest $test)
    {
        $this->last_test_added = $test;
        $this->last_test_added->explicitly_defined_scenario_count = 0;
    }

    public function whenScenarioCreated(Scenario $scenario)
    {
        if (isset($this->last_test_added) === false) {
            throw new \RuntimeException('Can only add scenarios to test contexts');
        }

        ++$this->last_test_added->explicitly_defined_scenario_count;
        $current_test_scenarios = $this->test_scenario_map[$this->last_test_added];
        array_push($current_test_scenarios, $scenario);
        $scenario->index = count($current_test_scenarios);
        $this->test_scenario_map[$this->last_test_added] = $current_test_scenarios;
    }

    public function exitActiveTestContext()
    {
        unset($this->last_test_added);
    }

    public function whenTestStarted(AbstractTest $test)
    {
        $scenario_composite = new ScenarioComposite($test, $this->test_scenario_map[$test], $this->eventEmitter);
        $test->addSetupFunction($scenario_composite->asSetupFunction());
        $test->addTearDownFunction($scenario_composite->asTearDownFunction());
    }
}
