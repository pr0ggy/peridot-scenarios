<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\TestInterface;
use Peridot\Core\Suite;
use SplObjectStorage;
use RuntimeException;

/**
 * Tracks the state of the Peridot context to ensure scenarios are bound to the correct
 * test instances.
 *
 * @package Peridot\Plugin\Scenarios
 */
class ContextListener
{
    use HasEventEmitterTrait;

    /**
     * @var SplObjectStorage
     */
    private $test_scenario_map;

    /**
     * @var TestInterface
     */
    private $last_test_added;

    /**
     * @param EventEmitterInterface $event_emitter
     */
    public function __construct(EventEmitterInterface $event_emitter)
    {
        $this->eventEmitter = $event_emitter;
        $this->test_scenario_map = new SplObjectStorage();
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
     * @param  TestInterface $test
     */
    public function enterNewActiveTestContext(TestInterface $test)
    {
        $test->explicitly_defined_scenario_count = 0;
        $this->test_scenario_map[$test] = [];
        $this->last_test_added = $test;
    }

    /**
     * Associates the given scenario with the current active test context
     *
     * @param  Scenario $scenario
     * @throws RuntimeException if there is no currently-active test context
     */
    public function addScenarioToTestContext(Scenario $scenario)
    {
        if (isset($this->last_test_added) === false) {
            throw new RuntimeException('Can only add scenarios to test contexts');
        }

        ++$this->last_test_added->explicitly_defined_scenario_count;
        $active_test_context_scenarios = $this->test_scenario_map[$this->last_test_added];
        array_push($active_test_context_scenarios, $scenario);
        $scenario->index = count($active_test_context_scenarios);
        $this->test_scenario_map[$this->last_test_added] = $active_test_context_scenarios;
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
     * @param  TestInterface $test
     */
    public function hookScenariosIntoTest(TestInterface $test)
    {
        if (isset($this->test_scenario_map[$test]) === false
            || empty($this->test_scenario_map[$test])) {
            return;
        }

        $scenario_composite = new ScenarioComposite(
            $test,
            $this->test_scenario_map[$test]
        );

        $test->addSetupFunction($scenario_composite->asCallableSetupHook());
        $test->addTearDownFunction($scenario_composite->asCallableTearDownHook());
    }
}
