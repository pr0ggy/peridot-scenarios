<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\Plugin
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Plugin;

describe('Peridot\Plugin\Scenarios\Plugin', function () {
    $this->original_plugin_context = Plugin::getInstance();

    $this->fake_scenario_factory = m::mock('\Peridot\Plugin\Scenarios\ScenarioFactory')->shouldIgnoreMissing();
    $this->fake_context_listener = m::mock('\Peridot\Plugin\Scenarios\ContextListener')->shouldIgnoreMissing();

    beforeEach(function () {
        Plugin::unregisterSingletonInstance();
        $this->plugin_instance =
            new Plugin(
                $this->fake_scenario_factory,
                $this->fake_context_listener,
                m::mock('\Peridot\Plugin\Scenarios\Reporters\AbstractReporter')
            );
    });

    afterEach(function () {
        Plugin::unregisterSingletonInstance();
        Plugin::registerSingletonInstance($this->original_plugin_context);
    });

    it('should allow new instance creation if singleton instance not yet registered', function () {
        Plugin::unregisterSingletonInstance();
        createNewPluginInstance();
    });

    function createNewPluginInstance()
    {
        return new Plugin(
            m::mock('\Peridot\Plugin\Scenarios\ScenarioFactory'),
            m::mock('\Peridot\Plugin\Scenarios\ContextListener'),
            m::mock('\Peridot\Plugin\Scenarios\Reporters\AbstractReporter')
        );
    }

    it('should throw RuntimeException if singleton instance already registered', function () {
        Plugin::registerSingletonInstance(createNewPluginInstance());

        try {
            createNewPluginInstance();
        } catch (RuntimeException $e) {
            return;
        }

        throw new Exception('Failed to throw exception when attempting to create instance with singleton already registered');
    });

    describe('->registerNewScenario($setup, $teardown)', function () {
        it('should defer Scenario instance creation to the ScenarioFactory dependency', function() {
            $some_scenario_setup = function () {};
            $some_scenario_teardown = function () {};
            $this->fake_scenario_factory
                ->shouldReceive('createScenario')
                ->with($some_scenario_setup, $some_scenario_teardown)
                ->andReturn(m::mock('Peridot\Plugin\Scenarios\Scenario'));

            $this->plugin_instance->registerNewScenario($some_scenario_setup, $some_scenario_teardown);
        });

        it('should pass Scenario instance created via the ScenarioFactory to the ContextListener', function() {
            $some_scenario_setup = function () {};
            $some_scenario_teardown = function () {};
            $some_scenario = m::mock('Peridot\Plugin\Scenarios\Scenario');

            $this->fake_scenario_factory
                ->shouldReceive('createScenario')
                ->andReturn($some_scenario);

            $this->fake_context_listener
                ->shouldReceive('addScenarioToTestContext')
                ->with($some_scenario);

            $this->plugin_instance->registerNewScenario($some_scenario_setup, $some_scenario_teardown);
        });
    });
});
