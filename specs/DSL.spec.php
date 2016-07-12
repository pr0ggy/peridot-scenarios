<?php
/**
 * Defines test for functions defined in src/DSL.php
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Plugin;
use Peridot\Plugin\Scenarios\ScenarioFactory;

describe('DSL Extension', function () {

    describe('inScenario', function () {
        /*
         * remember, scenario plugin singleton is referenced from the inScenario function and the
         * scenarios plugin is being used as these tests are running...have to do a wholesale swap
         * on the plugin context
         */
        $this->mock_plugin_context =
            m::mock('Peridot\Plugin\Scenarios\Plugin')
                ->shouldReceive('whenScenarioCreated')
                ->getMock();

        $this->mock_scenario = m::mock('Peridot\Plugin\Scenarios\Scenario');

        $this->mock_scenario_factory =
            m::mock('Peridot\Plugin\Scenarios\ScenarioFactory')
                ->shouldReceive('createScenario')
                ->andReturn($this->mock_scenario)
                ->getMock();

        $this->original_plugin_context = Plugin::getInstance();
        $this->original_scenario_factory = ScenarioFactory::getInstance();

        $this->noOp = function () { };

        beforeEach(function () {
            Plugin::unregisterSingletonInstance();
            Plugin::registerSingletonInstance($this->mock_plugin_context);

            ScenarioFactory::unregisterSingletonInstance();
            ScenarioFactory::registerSingletonInstance($this->mock_scenario_factory);
        });

        afterEach(function () {
            m::close();

            Plugin::unregisterSingletonInstance();
            Plugin::registerSingletonInstance($this->original_plugin_context);

            ScenarioFactory::unregisterSingletonInstance();
            ScenarioFactory::registerSingletonInstance($this->original_scenario_factory);
        });

        it('should allow closure as setup arg', function () {
            inScenario(function () {});
        });

        it('should allow callable as setup arg', function () {
            inScenario([$this, 'noOp']);
        });

        it('should allow simple value map as setup arg', function () {
            inScenario(['foo' => 'bar']);
        });

        it('should allow closure as teardown arg', function () {
            $some_setup_arg = [$this, 'noOp'];
            inScenario($some_setup_arg, function () {});
        });

        it('should allow callable as teardown arg', function () {
            $some_setup_arg = [$this, 'noOp'];
            inScenario($some_setup_arg, [$this, 'noOp']);
        });

        it('should allow pass-thru of any exception generated during call', function () {
            $exception = new Exception('Test exception');
            ScenarioFactory::unregisterSingletonInstance();
            ScenarioFactory::registerSingletonInstance(
                m::mock('Peridot\Plugin\Scenarios\ScenarioFactory')
                    ->shouldReceive('createScenario')
                    ->andThrow($exception)
                    ->getMock()
            );

            try {
                inScenario([]);
            } catch (Exception $e) {
                assert($e === $exception);
                return;
            }

            throw new Exception('Failed to allow pass-thru of generated exception');
        });

        it('should pass given args to scenario factory for Scenario instance creation', function () {
            $scenario_setup = function () {};
            $scenario_teardown = function () {};
            $this->mock_scenario_factory
                ->shouldReceive('createScenario')
                ->once()
                ->with($scenario_setup, $scenario_teardown);

            inScenario($scenario_setup, $scenario_teardown);
        });

        it('should notify plugin context of scenario created by the factory', function () {
            $this->mock_plugin_context
                ->shouldReceive('whenScenarioCreated')
                ->once()
                ->with($this->mock_scenario);

            inScenario([]);
        });
    });

    describe('setUp', function () {
        it('should return given argument', function () {
            assert(setUp($this->argument) === $this->argument);
        });
        inScenario(['argument' => 1]);
        inScenario(['argument' => 'test']);
        inScenario(['argument' => function () {}]);
    });

    describe('tearDown', function () {
        it('should return given argument', function () {
            assert(setUp($this->argument) === $this->argument);
        });
        inScenario(['argument' => 1]);
        inScenario(['argument' => 'test']);
        inScenario(['argument' => function () {}]);
    });

});
