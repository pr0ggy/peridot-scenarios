<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\ScenarioComposite
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\ScenarioComposite;
use Peridot\Plugin\Scenarios\Test\Doubles\ScenarioCompositeExplicitTestSetupAndTeardownDouble;
use Peridot\Plugin\Scenarios\Test;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\ScenarioComposite', function () {

    beforeEach(function () {
        $this->execution_events = [];
    });

    afterEach(function () {
        m::close();
    });

    describe('->asSetupFunction()', function () {
        context('when no scenarios given', function () {
            it('should return a no-op function', function () {
                $composite = new ScenarioComposite(Test\createFakeTest(), []);
                assert(is_callable($composite->asSetupFunction()));
            });
        });

        context('when 1 scenario given', function () {
            it('should execute the lone scenario\'s setup function bound to the given test scope', function () {
                $fake_scope = m::mock('Peridot\Core\Scope');
                $fake_test = Test\createFakeTestWithScope($fake_scope);
                $mock_scenario = Test\createFakeScenario();
                $mock_scenario
                    ->shouldReceive('executeSetupInContext')
                    ->once()
                    ->with($fake_scope);

                $composite = new ScenarioComposite($fake_test, [$mock_scenario]);
                $composite_as_setup = $composite->asSetupFunction();
                $composite_as_setup();
            });
        });

        context('when more than 1 scenario given', function () {
            it('should generate a function which executes N-1 scenarios and runs setup for Nth scenario', function () {
                $test_scope = $this;
                $scenario_composite_under_test =
                    new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                        createFakeTestWithEventReportingDefinition($test_scope),
                        createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                        createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                        createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                    );

                $generated_function = $scenario_composite_under_test->asSetupFunction();

                $generated_function();

                assert($this->execution_events === $this->expected_execution_events);
            });
            inScenario([
                'scenario_count' => 2,
                'expected_execution_events' => [
                        'scenario 1 setup',
                        'test definition',
                        'scenario 1 teardown',
                        'test context teardown',
                        'test context setup',
                        'scenario 2 setup'
                ]
            ]);
            inScenario([
                'scenario_count' => 3,
                'expected_execution_events' => [
                        'scenario 1 setup',
                        'test definition',
                        'scenario 1 teardown',
                        'test context teardown',
                        'test context setup',
                        'scenario 2 setup',
                        'test definition',
                        'scenario 2 teardown',
                        'test context teardown',
                        'test context setup',
                        'scenario 3 setup'
                ]
            ]);
            inScenario([
                'scenario_count' => 4,
                'expected_execution_events' => [
                        'scenario 1 setup',
                        'test definition',
                        'scenario 1 teardown',
                        'test context teardown',
                        'test context setup',
                        'scenario 2 setup',
                        'test definition',
                        'scenario 2 teardown',
                        'test context teardown',
                        'test context setup',
                        'scenario 3 setup',
                        'test definition',
                        'scenario 3 teardown',
                        'test context teardown',
                        'test context setup',
                        'scenario 4 setup'
                ]
            ]);
        });
    });

    describe('->executeFirstScenarioAgainstTestDefinition()', function () {
        it('should execute the first scenario against the test definition, then run test context teardown', function () {
            $test_scope = $this;
            $scenario_composite_under_test =
                new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                    createFakeTestWithEventReportingDefinition($test_scope),
                    createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                    createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                    createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                );

            $scenario_composite_under_test->executeFirstScenarioAgainstTestDefinition();
            $expected_execution_events = [
                'scenario 1 setup',
                'test definition',
                'scenario 1 teardown',
                'test context teardown'
            ];

            assert($this->execution_events === $expected_execution_events);
        });
        inScenario(['scenario_count' => 1]);
        inScenario(['scenario_count' => 2]);
        inScenario(['scenario_count' => 3]);
    });

    describe('->executeTestTeardown()', function () {
        xit('should....', function () {
            $test_scope = $this;
            $scenario_composite_under_test =
                new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                    createFakeTestWithEventReportingDefinition($test_scope),
                    createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                    createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                    createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                );
        });
    });

});

function createExecutionEventReportingFunctionInScope($event_message, $test_scope)
{
    return
        function () use ($test_scope, $event_message) {
            $test_scope->execution_events[] = $event_message;
        };
}

function createFakeTestWithEventReportingDefinition($test_scope)
{
    $fake_test = Test\createFakeTest();
    $fake_test
        ->shouldReceive('getDefinition')
        ->andReturn(
            createExecutionEventReportingFunctionInScope('test definition', $test_scope)
        );

    return $fake_test;
}

function createNFakeEventReportingScenarios($n, $test_scope) {
    $fake_scenarios = [];
    for ($i = 1; $i <= $n; ++$i) {
        $fake_scenarios[] = Test\createFakeScenarioWithSetupAndTeardownFuncs(
            createExecutionEventReportingFunctionInScope("scenario {$i} setup", $test_scope),
            createExecutionEventReportingFunctionInScope("scenario {$i} teardown", $test_scope)
        );
    }

    return $fake_scenarios;
}
