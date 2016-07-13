<?php

use Mockery as m;
use Peridot\Plugin\Scenarios\ContextListener;

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
});
