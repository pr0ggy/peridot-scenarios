<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;

class Plugin
{
    protected static $instance;

    public function getInstance()
    {
        if (isset(self::$instance) === false) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    protected $event_emitter;

    protected $context_listener;

    public function registerEmitter(EventEmitterInterface $event_emitter)
    {
        $this->event_emitter = $event_emitter;
    }

    public function registerContextListener(ContextListener $listener)
    {
        $this->context_listener = $listener;
    }

    public function whenScenarioCreated(Scenario $test_scenario)
    {
        if (isset($this->event_emitter) === false) {
            throw new \RuntimeException('No event emitter registered');
        }

        $this->event_emitter->emit('scenario.created', $test_scenario);
    }
}
