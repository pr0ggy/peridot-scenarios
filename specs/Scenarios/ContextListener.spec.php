<?php

use Mockery as m;
use Peridot\Core\AbstractTest;
use Peridot\Plugin\Scenarios\ContextListener;
use Peridot\Plugin\Scenarios\Test\Doubles\TestSetupTeardownAdditionSpy;

describe('Peridot\Plugin\Scenarios\ContextListener', function () {

    beforeEach(function () {
        $this->fake_event_emitter = m::mock('Peridot\EventEmitterInterface')->shouldIgnoreMissing();
        $this->context_instance = new ContextListener($this->fake_event_emitter);
    });

    afterEach(function () {
        m::close();
    });

    describe('on construction', function() {
        it('should register events with the EventEmitterInterface instance', function () {
            $this->fake_event_emitter->shouldReceive('on')->once()->with('test.added', m::type('callable'));
            $this->fake_event_emitter->shouldReceive('on')->once()->with('suite.define', m::type('callable'));
            $this->fake_event_emitter->shouldReceive('on')->once()->with('suite.wasDefined', m::type('callable'));
            $this->fake_event_emitter->shouldReceive('on')->once()->with('test.start', m::type('callable'));

            $context = new ContextListener($this->fake_event_emitter);
        });
    });

    describe('->enterNewActiveTestContext($test)', function () {
        it('should set the given test\'s scenario count to 0', function () {
            $fake_test = m::mock('Peridot\Core\Test');
            assert(isset($fake_test->explicitly_defined_scenario_count) === false);

            $this->context_instance->enterNewActiveTestContext($fake_test);
            assert($fake_test->explicitly_defined_scenario_count === 0);
        });
    });

    describe('->addScenarioToContext($scenario)', function () {
        context('when active test context exists', function () {
            it('should increment explicitly-defined scenario count on the active test context', function () {
                $fake_test = m::mock('Peridot\Core\Test');
                $this->context_instance->enterNewActiveTestContext($fake_test);
                assert($fake_test->explicitly_defined_scenario_count === 0);

                for ($i = 0; $i < $this->scenario_count; ++$i) {
                    $this->context_instance->addScenarioToContext(m::mock('Peridot\Plugin\Scenarios\Scenario'));
                }

                assert($fake_test->explicitly_defined_scenario_count === $this->scenario_count);
            });
            inScenario(['scenario_count' => 1]);
            inScenario(['scenario_count' => 3]);
            inScenario(['scenario_count' => 5]);
        });

        context('when no currently-active test context exists', function () {
            it('should throw a RuntimeException', function () {
                try {
                    $this->context_instance->addScenarioToContext(m::mock('Peridot\Plugin\Scenarios\Scenario'));
                } catch (RuntimeException $e) {
                    assert($e->getMessage() === 'Can only add scenarios to test contexts');
                    return;
                }

                throw new Exception('Failed to throw exception when attempting to add scenario when no active test context exists');
            });
        });
    });

    describe('->exitActiveTestContext()', function () {
        it('should unset the active test context', function () {
            $fake_test = m::mock('Peridot\Core\Test');
            $this->context_instance->enterNewActiveTestContext($fake_test);
            $this->context_instance->addScenarioToContext(m::mock('Peridot\Plugin\Scenarios\Scenario'));
            assert($fake_test->explicitly_defined_scenario_count === 1);

            $this->context_instance->exitActiveTestContext();

            try {
                $this->context_instance->addScenarioToContext(m::mock('Peridot\Plugin\Scenarios\Scenario'));
            } catch (RuntimeException $e) {
                assert($e->getMessage() === 'Can only add scenarios to test contexts');
                return;
            }

            throw new Exception('Failed to unset active test context');
        });
    });

    describe('->hookScenariosIntoTest($test)', function () {
        context('when no scenarios are bound to the given test', function () {
            it('should not alter the test\'s setup or teardown function set', function () {
                $fake_test = m::mock('Peridot\Core\AbstractTest');
                $fake_test->shouldNotReceive('addSetupFunction');
                $fake_test->shouldNotReceive('addTearDownFunction');

                // when test not even registered
                $this->context_instance->hookScenariosIntoTest($fake_test);
                // when test registered but no scenarios bound
                $this->context_instance->enterNewActiveTestContext($fake_test);
                $this->context_instance->hookScenariosIntoTest($fake_test);
            });
        });

        context('when at least 1 scenario is bound to the given test', function () {
            it('should add a setup and teardown function to the test that will allow execution of the associated scenarios against the test definition', function () {
                $test_double = new TestSetupTeardownAdditionSpy('some test description', function () {});
                $fake_scenario = m::mock('Peridot\Plugin\Scenarios\Scenario');
                $fake_scenario->shouldReceive('setUpFunctionBoundTo')->andReturn(function () {});
                $fake_scenario->shouldReceive('tearDownFunctionBoundTo')->andReturn(function () {});

                $this->context_instance->enterNewActiveTestContext($test_double);
                $this->context_instance->addScenarioToContext($fake_scenario);

                $test_double->startWatchingSetupsAndTeardowns();
                $this->context_instance->hookScenariosIntoTest($test_double);
                $this->context_instance->exitActiveTestContext();

                assert($test_double->setupAndTeardownFunctionsWereAdded());
            });
        });
    });
});
