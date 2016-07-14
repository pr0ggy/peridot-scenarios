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
                return $this->getFirstScenarioSetupFunction();

            default:
                return $this->getMultiScenarioCompositeSetupFunction();
        }
    }

    protected function getFirstScenarioSetupFunction()
    {
        $first_scenario = $this->scenarios[0];
        $test_scope = $this->test_scope;
        return new ScenarioContextAction(function () use ($first_scenario, $test_scope) {
            $first_scenario->executeSetupInContext($test_scope);
        });
    }

    protected function getMultiScenarioCompositeSetupFunction()
    {
        $scenario_composite = $this;
        return new ScenarioContextAction(function () use ($scenario_composite) {
            $scenario_composite->executeFirstScenarioAgainstTestDefinition();
            $scenario_composite->executeRemainingScenariosExceptLastAgainstTestDefinition();
            $scenario_composite->prepareForLastScenarioToBeExecutedAgainstTestDefinition();
        });
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
            $teardown_functions = $test->getTearDownFunctions();

            foreach ($teardown_functions as $teardown_function) {
                if ($teardown_function instanceof ScenarioContextAction) {
                    continue;
                }
                $teardown_function();
            }
        });
    }

    public function executeRemainingScenariosExceptLastAgainstTestDefinition()
    {
        $all_scenarios_except_last = array_slice($this->scenarios, 1, -1);
        foreach ($all_scenarios_except_last as $active_scenario) {
            $this->executeTestSetup();
            $this->executeScenarioAgainstTestDefinition($active_scenario);
            $this->executeTestTeardown();
        }
    }

    public function executeTestSetup()
    {
        $this->test->walkDown(function (TestInterface $test) {
            $setup_functions = $test->getSetupFunctions();

            foreach ($setup_functions as $setup_function) {
                if ($setup_function instanceof ScenarioContextAction) {
                    continue;
                }
                $setup_function();
            }
        });
    }

    protected function executeScenarioAgainstTestDefinition(Scenario $scenario)
    {
        try {
            $scenario->executeSetupInContext($this->test_scope);
            ($this->test_definition)();
            $scenario->executeTeardownInContext($this->test_scope);
        } catch (\Exception $e) {
            $e->failed_scenario_index = $scenario->index;
            throw $e;
        }
    }

    public function prepareForLastScenarioToBeExecutedAgainstTestDefinition()
    {
        $this->executeTestSetup();
        $last_scenario = $this->scenarios[count($this->scenarios) - 1];
        $last_scenario->executeSetupInContext($this->test_scope);
    }

    public function asTearDownFunction()
    {
        if (empty($this->scenarios)) {
            return getNoOp();
        }

        $last_scenario = $this->scenarios[count($this->scenarios) - 1];
        $test_scope = $this->test_scope;
        return new ScenarioContextAction(function () use ($last_scenario, $test_scope) {
            $last_scenario->executeTeardownInContext($test_scope);
        });
    }
}
