<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Peridot\Plugin\Scenarios\ScenarioFactory;

/**
 * Peridot\Plugin\Scenarios\ScenarioFactory spy double to be used in testing which will allow
 * the tests to verify that given setup/teardown arguments were correctly processed
 */
class ScenarioFactorySetupTeardownConversionSpy extends ScenarioFactory
{
    protected $actual_setup_passed_to_new_scenario_instance;
    protected $actual_teardown_passed_to_new_scenario_instance;

    protected function getScenarioSetupAsCallable($setup)
    {
        $result = parent::getScenarioSetupAsCallable($setup);
        $this->actual_setup_passed_to_new_scenario_instance = $result;
        return $result;
    }

    protected function getScenarioTeardownAsCallable($teardown = null)
    {
        $result = parent::getScenarioTeardownAsCallable($teardown);
        $this->actual_teardown_passed_to_new_scenario_instance = $result;
        return $result;
    }

    public function setupGivenToCreatedScenarioWas($expected_setup)
    {
        return ($this->actual_setup_passed_to_new_scenario_instance === $expected_setup);
    }

    public function setupGivenToCreatedScenarioWasCallable()
    {
        return is_callable($this->actual_setup_passed_to_new_scenario_instance);
    }

    public function teardownGivenToCreatedScenarioWas($expected_teardown)
    {
        return ($this->actual_teardown_passed_to_new_scenario_instance === $expected_teardown);
    }

    public function teardownGivenToCreatedScenarioWasCallable()
    {
        return is_callable($this->actual_teardown_passed_to_new_scenario_instance);
    }
}
