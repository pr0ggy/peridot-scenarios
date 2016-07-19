<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\Reporter
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Reporter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

describe('Peridot\Plugin\Scenarios\Reporter', function () {

    beforeEach(function () {
        $this->fake_event_emitter = m::mock('Peridot\EventEmitterInterface')->shouldIgnoreMissing();
        $this->fake_output_interface = m::mock('Symfony\Component\Console\Output\OutputInterface')->shouldIgnoreMissing();

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
                $this->fake_event_emitter->shouldReceive('on')->once()->with($event, m::type('callable'));
            }

            $this->reporter_under_test->registerEventHandlers();
        });
    });

});
