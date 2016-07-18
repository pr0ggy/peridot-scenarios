<?php
/**
 * Unit tests for
 * Peridot\Plugin\Scenarios\Test\Doubles\ScenarioCompositeExplicitTestSetupAndTeardownDouble
 */

use Peridot\Plugin\Scenarios\Test;
use Peridot\Plugin\Scenarios\Test\Doubles\ScenarioCompositeExplicitTestSetupAndTeardownDouble;

describe('Peridot\Plugin\Scenarios\Test\Doubles\ScenarioCompositeExplicitTestSetupAndTeardownDouble', function () {

    describe('->executeTestTeardown()', function () {
        it('should execute the teardown callable given during construction', function () {
            $some_test = Test\createFakeTest();
            $some_scenarios = [];
            $some_setup_function = function () { };
            $test_scope = $this;
            $teardown_function = function () use ($test_scope) {
                    $test_scope->foo = 'bar';
            };

            $double_under_test = new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                $some_test,
                $some_scenarios,
                $some_setup_function,
                $teardown_function
            );

            $double_under_test->executeTestTeardown();

            assert($this->foo === 'bar');
        });
    });

    describe('->executeTestSetup()', function () {
        it('should execute the setup callable given during construction', function () {
            $some_test = Test\createFakeTest();
            $some_scenarios = [];
            $some_teardown_function = function () { };
            $test_scope = $this;
            $setup_function = function () use ($test_scope) {
                    $test_scope->foo = 'bar';
            };

            $double_under_test = new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                $some_test,
                $some_scenarios,
                $setup_function,
                $some_teardown_function
            );

            $double_under_test->executeTestSetup();

            assert($this->foo === 'bar');
        });
    });

});
