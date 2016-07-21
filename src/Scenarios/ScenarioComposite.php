<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\TestInterface;
use Peridot\Plugin\Scenarios\ScenarioContextAction;

/**
 * Represents a set of scenarios to be executed against a given test defintion.
 * The main methods of this class are 'asCallableSetupHook()' and 'asCallableTearDownHook'.
 * In the setup hook, the idea is to execute N-1 scenarios against the test definition of
 * the TestInterface instance member, and run the setup of the Nth scenario. We need to
 * take this route because the Peridot core code will always follow the loop:
 * 		Execute test setup callbacks, top-down
 * 		Execute test definition
 * 		Execute test teardown callbacks, bottom-up
 *
 * This means that the test definition will be executed at least once after execution
 * of all setup callbacks (which we're hooking into to execute the scenarios), so we have
 * to ensure the last scenario is set up during this execution of the test definition.
 * This means the new logical flow of the Peridot core test execution loop with the
 * Scenarios plugin enabled is:
 * 		Execute test setup callbacks, top-down
 * 		Execute N-1 scenarios against the test definition via a callable added as a final test setup function
 * 			Scenario setup
 * 			Test definition
 * 			Scenario teardown
 * 			Test teardown, bottom-up
 * 			Test Setup, top-down
 * 			Scenario Setup
 * 			Test definition
 * 			Test teardown, bottom-up
 * 			...
 * 			Scenario N setup
 * 		Execute test definition
 * 		Execute Scenario N teardown
 * 		Execute test teardown callbacks, bottom-up
 *
 * As apparent above, the teardown hook merely executes the teardown action of the last
 * scenario in the set.
 *
 * @package  Peridot\Plugin\Scenarios
 */
class ScenarioComposite
{
    /**
     * @var TestInterface
     */
    protected $test;

    /**
     * @var callable
     */
    protected $test_definition;

    /**
     * @var \Peridot\Core\Scope
     */
    protected $test_scope;

    /**
     * @var array
     */
    protected $scenarios;

    /**
     * @param TestInterface $test
     * @param array         $scenarios
     */
    public function __construct(TestInterface $test, array $scenarios = [])
    {
        $this->test = $test;
        $this->test_definition = $test->getDefinition();
        $this->test_scope = $test->getScope();
        $this->scenarios = $scenarios;
    }

    /**
     * Returns a callback designed to be hooked into a test as a final setup function via
     * TestInterface::addSetupFunction($fn).  The idea is this function executes N-1
     * scenarios against the test definition and then runs the setup for the Nth scenario,
     * ensuring all scenarios are executed once against the test definition.  If no test
     * scenarios have been given, a simple no-op is used.  If only 1 is given, then we just
     * run the setup for that scenario.
     *
     * @return callable
     */
    public function asCallableSetupHook()
    {
        switch (count($this->scenarios)) {
            case 0:
                return getNoOp();
            case 1:
                return $this->getFirstScenarioSetupCallable();

            default:
                return $this->getMultiScenarioCompositeSetupCallable();
        }
    }

    /**
     * Returns a callable ScenarioContextAction instance that will execute the setup code
     * of the first scenario when invoked
     *
     * @return ScenarioContextAction
     */
    protected function getFirstScenarioSetupCallable()
    {
        $first_scenario = $this->scenarios[0];
        $test_scope = $this->test_scope;
        return new ScenarioContextAction(function () use ($first_scenario, $test_scope) {
            $first_scenario->executeSetupInContext($test_scope);
        });
    }

    /**
     * Returns a callable ScenarioContextAction instance that will execute N-1 scenarios
     * against the test definition and then execute the setup of the Nth scenario
     *
     * @return ScenarioContextAction
     */
    protected function getMultiScenarioCompositeSetupCallable()
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
        $this->test->walkUp(
            function (TestInterface $test) {
                $teardown_functions = $test->getTearDownFunctions();

                foreach ($teardown_functions as $teardown_function) {
                    if ($teardown_function instanceof ScenarioContextAction) {
                        continue;
                    }
                    $teardown_function();
                }
            }
        );
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
        $this->test->walkDown(
            function (TestInterface $test) {
                $setup_functions = $test->getSetupFunctions();

                foreach ($setup_functions as $setup_function) {
                    if ($setup_function instanceof ScenarioContextAction) {
                        continue;
                    }
                    $setup_function();
                }
            }
        );
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

    public function asCallableTearDownHook()
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
