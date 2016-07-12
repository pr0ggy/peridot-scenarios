<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Mockleton\MockableSingletonBehavior;

/**
 * Class defining the plugin context itself.  Plugin context is a singleton.
 *
 * @package  Peridot\Plugin\Scenarios
 */
class Plugin
{
    use MockableSingletonBehavior;

    /**
     * @var \Peridot\EventEmitterInterface
     */
    protected $event_emitter;

    /**
     * @var \Peridot\Plugin\Scenarios\ContextListener
     */
    protected $peridot_context_listener;

    public function __construct(EventEmitterInterface $event_emitter, ContextListener $peridot_context_listener)
    {
        self::verifyInstanceNotYetRegistered();
        $this->event_emitter = $event_emitter;
        $this->peridot_context_listener = $peridot_context_listener;
    }

    /**
     * Fires a 'scenario.created' event from the event emitter indicating that a new test scenario
     * has been created
     *
     * @param  Scenario $test_scenario
     */
    public function whenScenarioCreated(Scenario $test_scenario)
    {
        $this->event_emitter->emit('scenario.created', $test_scenario);
    }
}
