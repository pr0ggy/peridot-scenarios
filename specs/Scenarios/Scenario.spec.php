<?php
/**
 * Unti tests for Peridot\Plugin\Scenarios\Scenario
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Scenario;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\Scenario', function () {
    describe('->setUpFunctionBoundTo($scope)', function () {
        it('should return the scenario setup function context-bound to a given Scope', function () {
            $fake_scope = m::mock('\Peridot\Core\Scope');
            $setup_function = function () {
                $this->foo = 'bar';
            };
            $some_teardown_function = getNoOp();
            $scenario = new Scenario($setup_function, $some_teardown_function);

            $bound_setup = $scenario->setUpFunctionBoundTo($fake_scope);
            $bound_setup();

            assert($fake_scope->foo === 'bar');
        });
    });

    describe('->tearDownFunctionBoundTo($scope)', function () {
        it('should return the scenario teardown function context-bound to a given Scope', function () {
            $fake_scope = m::mock('\Peridot\Core\Scope');
            $some_setup_function = getNoOp();
            $teardown_function = function () {
                $this->foo = 'bar';
            };
            $scenario = new Scenario($some_setup_function, $teardown_function);

            $bound_teardown = $scenario->tearDownFunctionBoundTo($fake_scope);
            $bound_teardown();

            assert($fake_scope->foo === 'bar');
        });
    });
});
