<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\AbstractTest;

class ScenarioComposite
{
    protected $test;

    protected $scenarios;

    public function __construct(AbstractTest $test, array $scenarios = [])
    {
        $this->test = $test;
        $this->scenarios = $scenarios;
    }

    public function asSetupFunction()
    {
        switch (count($this->scenarios)) {
            case 0:
                return getNoOp();
            case 1:
                return $this->scenarios[0]->setUpFunctionBoundTo($this->test->getScope());

            default:
                return $this->getMultiScenarioCompositeSetupFunction();
        }
    }

    protected function getMultiScenarioCompositeSetupFunction()
    {
        $test_scope = $this->test->getScope();
        $scenarios = $this->scenarios;
        $scenario_composite = $this;
        return function () use ($scenario_composite, $test_scope, $scenarios) {
            $scenario_composite->executeAllScenariosExceptLastAgainstTestDefinition();

            $last_scenario = $scenarios[count($scenarios) - 1];
            $last_scenario_setup = $last_scenario->setUpFunctionBoundTo($test_scope);
            $last_scenario_setup();
        };
    }

    public function executeAllScenariosExceptLastAgainstTestDefinition()
    {
        $test_definition = $this->test->getDefinition();
        $test_scope = $this->test->getScope();
        $all_scenarios_except_last = array_slice($this->scenarios, 0, count($this->scenarios)-1);
        $scenario_index = 0;
        try {
            foreach ($all_scenarios_except_last as $active_scenario) {
                ++$scenario_index;
                $scenario_setup = $active_scenario->setUpFunctionBoundTo($test_scope);
                $scenario_teardown = $active_scenario->tearDownFunctionBoundTo($test_scope);
                $scenario_setup();
                $test_definition();
                $scenario_teardown();
            }
        } catch (\Exception $e) {
            $e->failed_scenario_index = $scenario_index;
            throw $e;
        }
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
