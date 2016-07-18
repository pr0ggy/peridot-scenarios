<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\Test\Doubles\ScenarioFactorySetupTeardownConversionSpy
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Test\Doubles\ScenarioFactorySetupTeardownConversionSpy;

describe('Peridot\Plugin\Scenarios\Test\Doubles\ScenarioFactorySetupTeardownConversionSpy', function () {

    $this->noOp = function () {};

    describe('->setupGivenToCreatedScenarioWas($expected_setup)', function () {
        it('should return whether or not the setup passed to newly-created scenario instance was the expected setup', function () {
            $spy_under_test = new ScenarioFactorySetupTeardownConversionSpy();
            $some_teardown = function () {};

            $scenario = $spy_under_test->createScenario($this->setup, $some_teardown);
            assert($spy_under_test->setupGivenToCreatedScenarioWas($this->expected_setup) === $this->expected_result);
        });
        inScenario(setUp(function () {
            $this->setup = function () {};
            $this->expected_setup = $this->setup;
            $this->expected_result = true;
        }));
        inScenario(setUp(function () {
            $this->setup = function () {};
            $this->expected_setup = [];
            $this->expected_result = false;
        }));
    });

    describe('->setupGivenToCreatedScenarioWasCallable()', function () {
        it('should return whether or not the setup passed to newly-created scenario instance was callable', function () {
            $spy_under_test = new ScenarioFactorySetupTeardownConversionSpy();
            $some_teardown = function () {};

            $scenario = $spy_under_test->createScenario($this->setup, $some_teardown);
            assert($spy_under_test->setupGivenToCreatedScenarioWasCallable());
        });
        inScenario(['setup' => function () {}]);
        inScenario(['setup' => [$this, 'noOp']]);
    });

    describe('->teardownGivenToCreatedScenarioWas($expected_teardown)', function () {
        it('should return whether or not the teardown passed to newly-created scenario instance was the expected setup', function () {
            $spy_under_test = new ScenarioFactorySetupTeardownConversionSpy();
            $some_setup = function () {};

            $scenario = $spy_under_test->createScenario($some_setup, $this->teardown);
            assert($spy_under_test->teardownGivenToCreatedScenarioWas($this->expected_teardown) === $this->expected_result);
        });
        inScenario(setUp(function () {
            $this->teardown = function () {};
            $this->expected_teardown = $this->teardown;
            $this->expected_result = true;
        }));
        inScenario(setUp(function () {
            $this->teardown = function () {};
            $this->expected_teardown = [];
            $this->expected_result = false;
        }));
    });

    describe('->teardownGivenToCreatedScenarioWasCallable()', function () {
        it('should return whether or not the teardown passed to newly-created scenario instance was callable', function () {
            $spy_under_test = new ScenarioFactorySetupTeardownConversionSpy();
            $some_setup = function () {};

            $scenario = $spy_under_test->createScenario($some_setup, $this->teardown);
            assert($spy_under_test->teardownGivenToCreatedScenarioWasCallable());
        });
        inScenario(['teardown' => function () {}]);
        inScenario(['teardown' => [$this, 'noOp']]);
    });

});
