<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\AbstractTest;
use Peridot\Core\Suite;

/**
 * Context object which tracks the state of the Peridot context to ensure scenarios are
 * bound to the correct test instances.
 *
 * @package Peridot\Plugin\Scenarios
 */
class ContextListener
{
    use HasEventEmitterTrait;

    /**
     * @var \SplObjectStorage
     */
    protected $test_scenario_map;

    /**
     * @var AbstractTest
     */
    protected $last_test_added;

    /**
     * @param EventEmitterInterface $event_emitter
     */
    public function __construct(EventEmitterInterface $event_emitter)
    {
        $this->eventEmitter = $event_emitter;
        $this->test_scenario_map = new \SplObjectStorage();
        $this->registerEventHandlers();
    }

    /**
     * Registers event handlers with the EventEmitterInterface dependency
     */
    protected function registerEventHandlers()
    {
        $this->eventEmitter->on('test.added',       [$this, 'enterNewActiveTestContext']);
        $this->eventEmitter->on('suite.define',     [$this, 'exitActiveTestContext']);
        $this->eventEmitter->on('suite.wasDefined', [$this, 'exitActiveTestContext']);
        $this->eventEmitter->on('test.start',       [$this, 'hookScenariosIntoTest']);
    }

    /**
     * Adds a new test to the test/scenario mapping and makes the new test the active
     * test context to which to bind new scenarios
     *
     * @param  AbstractTest $test
     */
    public function enterNewActiveTestContext(AbstractTest $test)
    {
        $this->test_scenario_map[$test] = [];
        $this->setActiveTestContext($test);
        $test->explicitly_defined_scenario_count = 0;
    }

    /**
     * Marks the given test as the last-added test, indicating it will be the test new
     * scenarios will be bound to
     *
     * @param  AbstractTest $test
     */
    protected function setActiveTestContext(AbstractTest $test)
    {
        $this->last_test_added = $test;
    }

    /**
     * Associates the given scenario with the current active test context
     *
     * @param  Scenario $scenario
     * @throws \RuntimeException if there is no currently-active test context
     */
    public function addScenarioToContext(Scenario $scenario)
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

    /**
     * Unsets the active test context, indicating that there are no active tests to which
     * to bind new scenarios
     */
    public function exitActiveTestContext()
    {
        unset($this->last_test_added);
    }

    /**
     * Hooks all the scenarios associated with the given test into the test's execution
     *
     * @param  AbstractTest $test
     */
    public function hookScenariosIntoTest(AbstractTest $test)
    {
        $test_scenarios = $this->test_scenario_map[$test];
        if (empty($test_scenarios)) {
            return;
        }

        $scenario_composite = new ScenarioComposite(
            $test,
            $test_scenarios
        );

        $test->addSetupFunction($scenario_composite->asSetupFunction());
        $test->addTearDownFunction($scenario_composite->asTearDownFunction());
    }
}
