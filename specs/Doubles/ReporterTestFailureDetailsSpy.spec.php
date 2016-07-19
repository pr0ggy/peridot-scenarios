<?php

use Mockery as m;
use SplObjectStorage;
use Peridot\Plugin\Scenarios\Test\Doubles\ReporterTestFailureDetailsSpy;

describe('Peridot\Plugin\Scenarios\Test\Doubles\ReporterTestFailureDetailsSpy', function () {

    beforeEach(function () {
        $this->fake_event_emitter = m::mock('Peridot\EventEmitterInterface')->shouldIgnoreMissing();
        $this->fake_output_interface = m::mock('Symfony\Component\Console\Output\OutputInterface')->shouldIgnoreMissing();
    });

    afterEach(function () {
        m::close();
    });

    describe('->getScenarioFailureCount()', function () {
        it('should return the count of failed tests', function () {
            $explicit_failure_map = new SplObjectStorage();
            for ($i=0; $i < $this->failure_count; ++$i) {
                $explicit_failure_map[m::mock()] = 'some failure message';
            }

            $this->reporter_under_test =
                new ReporterTestFailureDetailsSpy(
                    $this->fake_event_emitter,
                    $this->fake_output_interface,
                    $explicit_failure_map
                );

            assert(
                $this->reporter_under_test->getScenarioFailureCount() === $this->failure_count,
                'Failed to return the correct count of scenario failures'
            );
        });
        inScenario(['failure_count' => 0]);
        inScenario(['failure_count' => 1]);
        inScenario(['failure_count' => 2]);
    });

    describe('->getFailureMessageForTest($test)', function () {
        it('should return the failure message associated with the given test', function () {
            $fake_test = m::mock('Peridot\Core\TestInterface');
            $expected_failure_message = 'This is the failure message';
            $explicit_failure_map = new SplObjectStorage();
            $explicit_failure_map[$fake_test] = $expected_failure_message;

            $this->reporter_under_test =
                new ReporterTestFailureDetailsSpy(
                    $this->fake_event_emitter,
                    $this->fake_output_interface,
                    $explicit_failure_map
                );

            assert(
                $this->reporter_under_test->getScenarioFailureMessageForTest($fake_test) === $expected_failure_message,
                'Failed to return the expected associated failure message for the given test'
            );
        });
    });

});
