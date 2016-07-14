<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\ScenarioComposite
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\ScenarioComposite;
use Peridot\Plugin\Scenarios\Test;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\ScenarioComposite', function () {

    describe('->asSetupFunction()', function () {
        context('when no scenarios given', function () {
            it('should return a no-op function', function () {
                $composite = new ScenarioComposite(Test\getFakeTest(), []);
                assert(is_callable($composite->asSetupFunction()));
            });
        });

        context('when 1 scenario given', function () {
            it('should return the lone scenario\'s setup function bound to the given test scope', function () {
                $fake_test = Test\getFakeTest();
                $fake_scenario = m::mock('Peridot\Plugin\Scenarios\Scenario');
                $some_function = function () { };
                $fake_scenario
                    ->shouldReceive('setUpFunctionBoundTo')
                    ->once()
                    ->with($fake_test->getScope())
                    ->andReturn($some_function);

                $composite = new ScenarioComposite($fake_test, [$fake_scenario]);
                assert($composite->asSetupFunction() === $some_function);
            });
        });

        context('when more than 1 scenario given', function () {
            it('should generate a function which executes N-1 scenarios and runs setup for Nth scenario');
        });
    });

});
