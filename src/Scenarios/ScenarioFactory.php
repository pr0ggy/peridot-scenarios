<?php

namespace Peridot\Plugin\Scenarios;

/**
 * Handles creation and configuration of new scenarios based on given setup and teardown entities.
 * TODO: Revisit this, testing methodologies feel off
 *
 * @package Peridot\Plugin\Scenarios
 */
class ScenarioFactory
{
    /**
     * Creates and returns a new Scenario instance
     *
     * @param  callable|array $setup
     * @param  callable|null  $teardown
     * @return Scenario
     * @throws \RuntimeException if the given $setup argument is not an array or callable
     */
    public function createScenario($setup, callable $teardown = null)
    {
        $setup_func = $this->getScenarioSetupAsCallable($setup);
        $teardown_func = $this->getScenarioTeardownAsCallable($teardown);
        return new Scenario(
            new ScenarioContextAction($setup_func),
            new ScenarioContextAction($teardown_func)
        );
    }

    /**
     * Returns the given $setup argument as a callable function.
     * If the $setup argument is already callable, it is returned as-is.  If the $setup arg is given
     * as a k/v map, it is assumed that the map simply defines field names and values that should be
     * set in the scenario test context, so a closure which handles this is returned.
     *
     * @param  callable|array $setup
     * @return callable
     * @throws \RuntimeException if the given $setup arg is not an array or callable
     */
    protected function getScenarioSetupAsCallable($setup)
    {
        if (is_callable($setup)) {
            return $setup;
        } elseif (is_array($setup)) {
            return $this->getSimpleContextualAttributeSettingFunction($setup);
        }

        throw new \TypeError('Scenario setup must be given as a callable or a key/value map');
    }

    /**
     * Accepts a k/v map an returns a closure which binds each k/v pair to a context field and value.
     * The context ($this) will be bound to test's scope when the test is run, meaning the new fields
     * and values will be accessible via '$this' within a test's definition.
     *
     * @param  array  $field_name_to_value_map
     * @return callable
     */
    protected function getSimpleContextualAttributeSettingFunction(array $field_name_to_value_map = [])
    {
        return function () use ($field_name_to_value_map) {
            foreach ($field_name_to_value_map as $field_name => $value) {
                $this->$field_name = $value;
            }
        };
    }

    /**
     * Returns the given $teardown argument as a callable function.
     * If the $teardown argument is already callable, it is returned as-is.  If it is null, then a
     * no-op closure is returned.  Any other value results in a \RuntimeException.
     *
     * @param  callable|null $teardown
     * @return callable
     * @throws \RuntimeException if any $teardown argument is given that is neither null nor callable
     */
    protected function getScenarioTeardownAsCallable($teardown = null)
    {
        if (is_callable($teardown)) {
            return $teardown;
        } elseif ($teardown === null) {
            return getNoOp();
        }

        throw new \TypeError('Scenario teardown must be given as a callable');
    }
}
