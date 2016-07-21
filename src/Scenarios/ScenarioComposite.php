<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\Core\HasEventEmitterTrait;
use Peridot\Core\TestInterface;
use Peridot\Plugin\Scenarios\ScenarioContextAction;
use Exception;

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
        $second_through_second_last_scenario = array_slice($this->scenarios, 1, -1);
        return new ScenarioContextAction(function () use ($scenario_composite, $second_through_second_last_scenario) {
            $scenario_composite->executeFirstScenarioAgainstTestDefinition();
            $scenario_composite->executeSpecificScenariosAgainstTestDefinition($second_through_second_last_scenario);
            $scenario_composite->prepareForLastScenarioToBeExecutedAgainstTestDefinition();
        });
    }

    /**
     * Executes the first scenario against the test definition.  Because we're hooking
     * scenario execution in as a test setup function, the test setup will already be
     * completed when the first scenario is ready to run against the test definition,
     * meaning for the first scenario, we only have to execute the scenario setup, run the
     * test definition, run scenario teardown, and then run test teardown.  The test setup
     * will have already been completed.
     */
    public function executeFirstScenarioAgainstTestDefinition()
    {
        $first_scenario = $this->scenarios[0];
        $this->executeScenarioAgainstTestDefinition($first_scenario);
        $this->executeTestTeardown();
    }

    /**
     * Executes all teardown callbacks associated with a test with the intent to then re-
     * run all setup callbacks, setup the next scenario, and run the scenario against the
     * test definition.  However, we have to remember that the final scenario teardown
     * action will be in the form of an invocable ScenarioContextAction registered as a
     * test teardown function...so we have to take care not to execute that particular
     * teardown action.
     */
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

    /**
     * Executes the given scenario set against the  test definition.  This means:
     * 		Executing all setup callbacks associated with the test
     * 		Executing scenario setup
     * 		Executing test definition
     * 		Executing scenario teardown
     * 		Executing all teardown callbacks associated with the test
     */
    public function executeSpecificScenariosAgainstTestDefinition(array $specific_scenarios)
    {
        foreach ($specific_scenarios as $active_scenario) {
            $this->executeTestSetup();
            $this->executeScenarioAgainstTestDefinition($active_scenario);
            $this->executeTestTeardown();
        }
    }

    /**
     * Executes all setup callbacks associated with a test with the intent to then setup
     * the next scenario, run the scenario against the test definition, then run scenario
     * and test teardowns.  However, we have to remember that the method we're using to
     * hook in this scenario execution system is in the form of an invocable
     * ScenarioContextAction registered as a test setup function...so we have to take care
     * not to execute that particular setup action.
     */
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

    /**
     * Executes the given scenario against the test definition.  This entails:
     * 		Executing the scenario setup
     * 	 	Executing the test definition
     * 	 	Executing the scenario teardown
     *
     * @param  Scenario $scenario
     * @throws Exception if any exceptions occur during the scenario setup, execution against
     *         			 the test definition, or scenario teardown
     */
    protected function executeScenarioAgainstTestDefinition(Scenario $scenario)
    {
        try {
            $scenario->executeSetupInContext($this->test_scope);
            ($this->test_definition)();
            $scenario->executeTeardownInContext($this->test_scope);
        } catch (Exception $e) {
            $e->failed_scenario_index = $scenario->index;
            throw $e;
        }
    }

    /**
     * Prepares for the final scenario to be run by:
     * 		Executing all setup callbacks associated with the test
     * 		Executing the setup for the final scenario
     */
    public function prepareForLastScenarioToBeExecutedAgainstTestDefinition()
    {
        $this->executeTestSetup();
        $last_scenario = $this->scenarios[count($this->scenarios) - 1];
        $last_scenario->executeSetupInContext($this->test_scope);
    }

    /**
     * Returns a callback designed to be hooked into a test as a teardown function via
     * TestInterface::addTearDownFunction($fn).  The only thing this callable need be
     * responsible for is tearing down the final scenario that was executed against the
     * test definition.  If no scenarios are present, then a simple no-op is used.
     *
     * @return callable
     */
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
