<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\TestInterface;
use Peridot\Core\AbstractTest;

class ScenarioComposite
{
    protected $test;

    protected $test_definition;

    protected $test_scope;

    protected $scenarios;

    public function __construct(AbstractTest $test, array $scenarios = [])
    {
        $this->test = $test;
        $this->test_definition = $test->getDefinition();
        $this->test_scope = $test->getScope();
        $this->scenarios = $scenarios;
    }

    public function asSetupFunction()
    {
        switch (count($this->scenarios)) {
            case 0:
                return getNoOp();
            case 1:
                return $this->scenarios[0]->setUpFunctionBoundTo($this->test_scope);

            default:
                return $this->getMultiScenarioCompositeSetupFunction();
        }
    }

    protected function getMultiScenarioCompositeSetupFunction()
    {
        $test_scope = $this->test_scope;
        $scenarios = $this->scenarios;
        $scenario_composite = $this;
        return function () use ($scenario_composite, $test_scope, $scenarios) {
            $scenario_composite->executeFirstScenarioAgainstTestDefinition();
            $scenario_composite->executeRemainingScenariosExceptLastAgainstTestDefinition();
            $scenario_composite->prepareForLastScenarioToBeExecutedAgainstTestDefinition();
        };
    }

    public function executeFirstScenarioAgainstTestDefinition()
    {
        $first_scenario = $this->scenarios[0];
        $this->executeScenarioAgainstTestDefinition($first_scenario);
        $this->executeTestTeardown();
    }

    public function executeTestTeardown()
    {
        $this->test->walkUp(function (TestInterface $test) {
            $teardown_functions = array_slice($test->getTearDownFunctions(), 0, -1);
            foreach ($teardown_functions as $teardown_function) {
                $teardown_function();
            }
        });
    }

    public function executeRemainingScenariosExceptLastAgainstTestDefinition()
    {
        $all_scenarios_except_last = array_slice($this->scenarios, 1, -1);
        foreach ($all_scenarios_except_last as $active_scenario) {
            $this->executeTestSetup();
            $this->executeScenarioAgainstTestDefinition($active_scenario, $this->test);
            $this->executeTestTeardown();
        }
    }

    public function executeTestSetup()
    {
        $this->test->walkDown(function (TestInterface $test) {
            $setup_functions = array_slice($test->getSetupFunctions(), 0, -1);
            foreach ($setup_functions as $setup_function) {
                $setup_function();
            }
        });
    }

    protected function executeScenarioAgainstTestDefinition(Scenario $scenario)
    {
        $scenario_setup = $scenario->setUpFunctionBoundTo($this->test_scope);
        $scenario_teardown = $scenario->tearDownFunctionBoundTo($this->test_scope);

        try {
            $scenario_setup();
            ($this->test_definition)();
            $scenario_teardown();
        } catch (\Exception $e) {
            $e->failed_scenario_index = $scenario->index;
            throw $e;
        }
    }

    public function prepareForLastScenarioToBeExecutedAgainstTestDefinition()
    {
        $last_scenario = $this->scenarios[count($this->scenarios) - 1];
        $last_scenario_setup = $last_scenario->setUpFunctionBoundTo($this->test_scope);
        $this->executeTestSetup();
        $last_scenario_setup();
    }

    public function asTearDownFunction()
    {
        if (empty($this->scenarios)) {
            return getNoOp();
        }

        $last_scenario = $this->scenarios[count($this->scenarios) - 1];
        $last_scenario_teardown = $last_scenario->tearDownFunctionBoundTo($this->test->getScope());
        return function () use ($last_scenario_teardown) {
            $last_scenario_teardown();
        };
    }
}
