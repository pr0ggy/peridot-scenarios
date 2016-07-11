<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
Use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\AbstractTest;

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
        $this->eventEmitter->on('test.start', [$this, 'whenTestStarted']);
    }

    public function whenTestAdded(AbstractTest $test)
    {
        $this->test_scenario_map[$test] = [];
        $this->last_test_added = $test;
        //$this->last_test_added->has_explicitly_defined_scenarios = false;
        $this->last_test_added->explicitly_defined_scenario_count = 0;
    }

    public function whenScenarioCreated(Scenario $scenario)
    {
        if (isset($this->last_test_added) === false) {
            throw new \RuntimeException('Scenarios must be added to tests...no tests found.');
        }

        //$this->last_test_added->has_explicitly_defined_scenarios = true;
        ++$this->last_test_added->explicitly_defined_scenario_count;
        $current_test_scenarios = $this->test_scenario_map[$this->last_test_added];
        array_push($current_test_scenarios, $scenario);
        $scenario->index = count($current_test_scenarios);
        $this->test_scenario_map[$this->last_test_added] = $current_test_scenarios;
    }

    public function whenTestStarted(AbstractTest $test)
    {
        $scenario_composite = new ScenarioComposite($test, $this->test_scenario_map[$test], $this->eventEmitter);
        $test->addSetupFunction($scenario_composite->asSetupFunction());
        $test->addTearDownFunction($scenario_composite->asTearDownFunction());
    }

}
