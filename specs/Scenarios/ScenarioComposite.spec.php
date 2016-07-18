<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\ScenarioComposite
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\ScenarioComposite;
use Peridot\Plugin\Scenarios\Test\Doubles\ScenarioCompositeExplicitTestSetupAndTeardownDouble;
use Peridot\Plugin\Scenarios\Test\Doubles\TestNodeWalkingCallableRecordingDouble;
use Peridot\Plugin\Scenarios\ScenarioContextAction;
use Peridot\Plugin\Scenarios\Test;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\ScenarioComposite', function () {

    beforeEach(function () {
        $this->execution_events = [];
    });

    afterEach(function () {
        m::close();
    });

    describe('->asCallableSetupHook()', function () {
        context('when no scenarios given', function () {
            it('should return a no-op function', function () {
                $scenario_composite_under_test = new ScenarioComposite(Test\createFakeTest(), []);
                assert(is_callable($scenario_composite_under_test->asCallableSetupHook()));
            });
        });

        context('when 1 scenario given', function () {
            it('should return a ScenarioContextAction that executes the lone scenario\'s setup action bound to the given test scope', function () {
                $fake_scope = m::mock('Peridot\Core\Scope');
                $fake_test = Test\createFakeTestWithScope($fake_scope);
                $mock_scenario = Test\createFakeScenario();
                $mock_scenario
                    ->shouldReceive('executeSetupInContext')
                    ->once()
                    ->with($fake_scope);

                $scenario_composite_under_test = new ScenarioComposite($fake_test, [$mock_scenario]);
                $composite_as_setup = $scenario_composite_under_test->asCallableSetupHook();
                assert($composite_as_setup instanceof ScenarioContextAction);
                $composite_as_setup();
            });
        });

        context('when more than 1 scenario given', function () {
            it('should generate a ScenarioContextAction which executes N-1 scenarios and runs setup for Nth scenario', function () {
                $test_scope = $this;
                $scenario_composite_under_test =
                    new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                        createFakeTestWithEventReportingDefinition($test_scope),
                        createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                        createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                        createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                    );

                $composite_as_setup = $scenario_composite_under_test->asCallableSetupHook();
                assert($composite_as_setup instanceof ScenarioContextAction);

                $composite_as_setup();

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
        it('should pass a test tree walking callback that executes all test teardowns that aren\'t ScenarioContextActions', function () {
            $test_scope = $this;
            $some_setup_functions = [];
            $fake_test_with_scenario_teardown_actions = Test\createFakeTestWithSetupAndTeardownActions(
                $some_setup_functions,
                createCallableSetIncludingAScenarioSetupActionThatShouldntBeCalled($test_scope)
            );
            $some_scenarios = [];
            $test_walking_double = new TestNodeWalkingCallableRecordingDouble();
            $scenario_composite_under_test = new ScenarioComposite(
                $test_walking_double,
                $some_scenarios
            );

            $scenario_composite_under_test->executeTestTeardown();

            $callback = $test_walking_double->getLastWalkUpExecutionCallback();
            $callback($fake_test_with_scenario_teardown_actions);
        });
    });

    describe('->executeRemainingScenariosExceptLastAgainstTestDefinition()', function () {
        it('should...do that...', function () {
            $test_scope = $this;
            $scenario_composite_under_test =
                new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                    createFakeTestWithEventReportingDefinition($test_scope),
                    createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                    createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                    createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                );

            $scenario_composite_under_test->executeRemainingScenariosExceptLastAgainstTestDefinition();

            assert($this->execution_events === $this->expected_execution_events);
        });
        inScenario([
            'scenario_count' => 2,
            'expected_execution_events' => []
        ]);
        inScenario([
            'scenario_count' => 3,
            'expected_execution_events' => [
                'test context setup',
                'scenario 2 setup',
                'test definition',
                'scenario 2 teardown',
                'test context teardown',
            ]
        ]);
        inScenario([
            'scenario_count' => 4,
            'expected_execution_events' => [
                'test context setup',
                'scenario 2 setup',
                'test definition',
                'scenario 2 teardown',
                'test context teardown',
                'test context setup',
                'scenario 3 setup',
                'test definition',
                'scenario 3 teardown',
                'test context teardown'
            ]
        ]);
    });

    describe('->executeTestSetup()', function () {
        it('should pass a test tree walking callback that executes all test setups that aren\'t ScenarioContextActions', function () {
            $test_scope = $this;
            $some_teardown_functions = [];
            $fake_test_with_scenario_setup_actions = Test\createFakeTestWithSetupAndTeardownActions(
                createCallableSetIncludingAScenarioSetupActionThatShouldntBeCalled($test_scope),
                $some_teardown_functions
            );
            $some_scenarios = [];
            $test_walking_double = new TestNodeWalkingCallableRecordingDouble();
            $scenario_composite_under_test = new ScenarioComposite(
                $test_walking_double,
                $some_scenarios
            );

            $scenario_composite_under_test->executeTestSetup();

            $callback = $test_walking_double->getLastWalkDownExecutionCallback();
            $callback($fake_test_with_scenario_setup_actions);
        });
    });

    describe('->prepareForLastScenarioToBeExecutedAgainstTestDefinition()', function () {
        it('should run the test setup and the setup of the final scenario', function () {
            $test_scope = $this;
            $scenario_composite_under_test =
                new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                    createFakeTestWithEventReportingDefinition($test_scope),
                    createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                    createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                    createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                );

            $scenario_composite_under_test->prepareForLastScenarioToBeExecutedAgainstTestDefinition();

            assert($this->execution_events === $this->expected_execution_events);
        });
        inScenario([
            'scenario_count' => 2,
            'expected_execution_events' => [
                'test context setup',
                'scenario 2 setup'
            ]
        ]);
        inScenario([
            'scenario_count' => 3,
            'expected_execution_events' => [
                'test context setup',
                'scenario 3 setup'
            ]
        ]);
        inScenario([
            'scenario_count' => 4,
            'expected_execution_events' => [
                'test context setup',
                'scenario 4 setup'
            ]
        ]);
    });

    describe('->asCallableTearDownHook()', function () {
        context('when no scenarios given', function () {
            it('should return a no-op callable', function () {
                $fake_scope = m::mock('Peridot\Core\Scope');
                $fake_test = Test\createFakeTestWithScope($fake_scope);

                $scenario_composite_under_test = new ScenarioComposite($fake_test, []);
                $teardown_hook = $scenario_composite_under_test->asCallableTearDownHook();
                $teardown_hook();
                assert(empty($this->execution_events));
            });
        });

        context('when at least 1 scenario given', function () {
            it('should return a ScenarioContextAction that executes the final scenario teardown', function () {
                $test_scope = $this;
                $scenario_composite_under_test =
                    new ScenarioCompositeExplicitTestSetupAndTeardownDouble(
                        createFakeTestWithEventReportingDefinition($test_scope),
                        createNFakeEventReportingScenarios($this->scenario_count, $test_scope),
                        createExecutionEventReportingFunctionInScope('test context setup', $test_scope),
                        createExecutionEventReportingFunctionInScope('test context teardown', $test_scope)
                    );

                $composite_as_setup = $scenario_composite_under_test->asCallableTearDownHook();
                assert($composite_as_setup instanceof ScenarioContextAction);

                $composite_as_setup();

                assert($this->execution_events === $this->expected_execution_events);
            });
            inScenario([
                'scenario_count'=>1,
                'expected_execution_events' => [
                    'scenario 1 teardown'
                ]
            ]);
            inScenario([
                'scenario_count'=>2,
                'expected_execution_events' => [
                    'scenario 2 teardown'
                ]
            ]);
            inScenario([
                'scenario_count'=>3,
                'expected_execution_events' => [
                    'scenario 3 teardown'
                ]
            ]);
        });
    });

});

function createCallableSetIncludingAScenarioSetupActionThatShouldntBeCalled($test_scope)
{
    $scenario_action_that_shouldnt_be_called = m::mock('Peridot\Plugin\Scenarios\ScenarioContextAction');
    $scenario_action_that_shouldnt_be_called->shouldNotReceive('__invoke');

    return [
        createExecutionEventReportingFunctionInScope('callable 1', $test_scope),
        createExecutionEventReportingFunctionInScope('callable 2', $test_scope),
        createExecutionEventReportingFunctionInScope('callable 3', $test_scope),
        $scenario_action_that_shouldnt_be_called
    ];
}

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

function createNFakeEventReportingScenarios($n, $test_scope)
{
    $fake_scenarios = [];
    for ($i = 1; $i <= $n; ++$i) {
        $fake_scenarios[] = Test\createFakeScenarioWithSetupAndTeardownFuncs(
            createExecutionEventReportingFunctionInScope("scenario {$i} setup", $test_scope),
            createExecutionEventReportingFunctionInScope("scenario {$i} teardown", $test_scope)
        );
    }

    return $fake_scenarios;
}
