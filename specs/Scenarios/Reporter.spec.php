<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\Reporter
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Test;
use Peridot\Plugin\Scenarios\Reporter;
use Peridot\Plugin\Scenarios\Test\Doubles\ReporterTestFailureDetailsSpy;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

describe('Peridot\Plugin\Scenarios\Reporter', function () {

    beforeEach(function () {
        $this->fake_event_emitter = m::mock('Peridot\EventEmitterInterface')->shouldIgnoreMissing();
        $this->fake_output_interface = m::mock('Symfony\Component\Console\Output\OutputInterface')->shouldIgnoreMissing();
        $this->fake_output_interface->shouldReceive('getFormatter')->andReturn(m::mock()->shouldIgnoreMissing());

        $this->reporter_under_test =
            new Reporter(
                $this->fake_event_emitter,
                $this->fake_output_interface
            );
    });

    afterEach(function () {
        m::close();
    });

    describe('->registerEventHandlers()', function () {
        it('should register the proper event handlers', function () {
            $relevent_events = [
                'test.failed',
                'runner.end'
            ];

            foreach ($relevent_events as $event) {
                $this->fake_event_emitter
                    ->shouldReceive('on')
                    ->once()
                    ->with($event, m::type('callable'));
            }

            $this->reporter_under_test->registerEventHandlers();
        });
    });

    describe('->registerTestFailure($test, $e)', function () {
        context('if given test has fewer than 2 scenarios associated', function () {
            it('shouldn\'t register the failure as a reportable scenario failure', function () {
                $fake_test_instance = m::mock('Peridot\Core\TestInterface');
                $fake_test_instance->explicitly_defined_scenario_count = $this->scenario_count;
                $some_failure_exception = m::mock('Exception');

                $this->reporter_under_test = Test\createReporterTestFailureDetailSpy($this);
                $this->reporter_under_test->registerTestFailure($fake_test_instance, $some_failure_exception);

                assert($this->reporter_under_test->getScenarioFailureCount() === 0);
            });
            inScenario(['scenario_count' => 0]);
            inScenario(['scenario_count' => 1]);
        });

        context('if given test has at least 2 scenarios associated', function () {
            it('should register the failure as a reportable scenario failure with correct failure message', function () {
                $fake_test_instance = m::mock('Peridot\Core\TestInterface');
                $fake_test_instance->explicitly_defined_scenario_count = $this->scenario_count;
                $fake_failure_exception = m::mock('Exception');
                $fake_failure_exception->failed_scenario_index = $this->failed_scenario_index;

                $this->reporter_under_test = Test\createReporterTestFailureDetailSpy($this);
                $this->reporter_under_test->registerTestFailure($fake_test_instance, $fake_failure_exception);

                assert(
                    $this->reporter_under_test->getScenarioFailureCount() === 1,
                    'Failed to registered scenario failure'
                );
                assert(
                    $this->reporter_under_test->getScenarioFailureMessageForTest($fake_test_instance) === $this->expected_scenario_message,
                    'Failed to associate correct failure message with test'
                );
            });
            inScenario([
                'scenario_count' => 2,
                'failed_scenario_index' => 2,
                'expected_scenario_message' => 'SCENARIO 2 FAILED'
            ]);
            inScenario([
                'scenario_count' => 3,
                'failed_scenario_index' => 5,
                'expected_scenario_message' => 'SCENARIO 5 FAILED'
            ]);
            inScenario([
                'scenario_count' => 4,
                'failed_scenario_index' => null,
                'expected_scenario_message' => 'LAST SCENARIO FAILED'
            ]);
        });
    });

    describe('->printInfoOnAnyScenarioFailures()', function () {
        context('when no reportable scenario failures registered', function () {
            it('should not write anything to output', function () {
                $this->fake_output_interface->shouldNotReceive('writeln');
                $this->reporter_under_test = Test\createReporterTestFailureDetailSpy($this);
                $this->reporter_under_test->printInfoOnAnyScenarioFailures();
            });
        });

        context('when at least 1 scenario failure registered', function () {
            xit('should write messages to output indicating scenario failures for each failed test', function () {
                // IN-PROGRESS
                $this->fake_output_interface->shouldReceive('writeln')->once()->with('');
                $this->fake_output_interface->shouldReceive('writeln')->once()->with('  Scenario Failures');
                $this->fake_output_interface->shouldReceive('writeln')->once()->with('  -------------------');
                foreach ($this->expected_scenario_failure_output_lines as $line) {
                    $this->fake_output_interface->shouldReceive('writeln')->once()->with($line);
                }
                $this->fake_output_interface->shouldReceive('writeln')->once()->with('');

                $this->reporter_under_test = Test\createReporterTestFailureDetailSpy($this, $this->failure_map);
                $this->reporter_under_test->printInfoOnAnyScenarioFailures();
            });
            inScenario(setUp(function () {
                $this->failure_map = new SplObjectStorage();
                $fake_test = Test\getLeafOfSimpleDescriptionTestHeirarchyOfDepth(1, this);
                $this->failure_map[$fake_test] = 'Scenario 1 Failed';

                $this->expected_scenario_failure_output_lines = [
                    '  Test 1 Description',
                    '    Scenario 1 Failed'
                ];
            }));
        });
    });

});
