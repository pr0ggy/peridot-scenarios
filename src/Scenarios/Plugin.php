<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios\Reporter;
use Mockleton\MockableSingletonBehavior;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Defines the plugin context itself.  Plugin context is a singleton.
 *
 * @package  Peridot\Plugin\Scenarios
 */
class Plugin
{
    use MockableSingletonBehavior;

    /**
     * Default plugin factory method which registers a new plugin singleton
     *
     * @param  EventEmitterInterface $event_emitter
     */
    public static function register(EventEmitterInterface $event_emitter)
    {
        self::createAndRegisterSingletonWithConstructionArgs(
            new ScenarioFactory(),
            new ContextListener($event_emitter),
            new Reporter($event_emitter, new ConsoleOutput())
        );
    }

    /**
     * @var ContextListener
     */
    private $peridot_context_listener;

    /**
     * @var Reporter
     */
    private $scenario_reporter;

    /**
     * @var ScenarioFactory
     */
    private $scenario_factory;

    /**
     * @param ScenarioFactory $scenario_factory
     * @param ContextListener $peridot_context_listener
     * @param Reporter        $scenario_reporter
     */
    protected function __construct(
        ScenarioFactory $scenario_factory,
        ContextListener $peridot_context_listener,
        Reporter $scenario_reporter
    ) {
        $this->scenario_factory = $scenario_factory;
        $this->peridot_context_listener = $peridot_context_listener;
        $this->scenario_reporter = $scenario_reporter;
    }

    /**
     * @param  callable|array $setup
     * @param  callable|null  $teardown
     */
    public function registerNewScenario($setup, callable $teardown = null)
    {
        $this->peridot_context_listener->addScenarioToTestContext(
            $this->scenario_factory->createScenario($setup, $teardown)
        );
    }
}
