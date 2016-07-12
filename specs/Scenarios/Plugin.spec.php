<?php
/**
 * Unti tests for Peridot\Plugin\Scenarios\Plugin
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Plugin;

describe('Peridot\Plugin\Scenarios\Plugin', function () {
    $this->original_plugin_context = Plugin::getInstance();

    beforeEach(function () {
        Plugin::unregisterSingletonInstance();
    });

    afterEach(function () {
        Plugin::unregisterSingletonInstance();
        Plugin::registerSingletonInstance($this->original_plugin_context);
    });

    it('should allow new instance creation if singleton instance not yet registered', function () {
        createNewPluginInstance();
    });

    function createNewPluginInstance()
    {
        $fake_event_emitter = m::mock('\Peridot\EventEmitterInterface');
        $fake_context_listener = m::mock('\Peridot\Plugin\Scenarios\ContextListener');
        return new Plugin($fake_event_emitter, $fake_context_listener);
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

    describe('->whenScenarioCreated($test_scenario)', function () {
        it('should emit the given scenario argument packaged in a "scenario.created" event', function () {
            $fake_scenario = m::mock('\Peridot\Plugin\Scenarios\Scenario');

            $mock_event_emitter =
                m::mock('\Peridot\EventEmitterInterface')
                ->shouldReceive('emit')
                ->once()
                ->with('scenario.created', $fake_scenario)
                ->getMock();

            $fake_context_listener = m::mock('\Peridot\Plugin\Scenarios\ContextListener');

            Plugin::registerSingletonInstance(
                new Plugin(
                    $mock_event_emitter,
                    $fake_context_listener
                )
            );

            Plugin::getInstance()->whenScenarioCreated($fake_scenario);
        });
    });
});
