<?php

namespace Peridot\Plugin\Scenarios;

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios\Reporter;
use Mockleton\MockableSingletonBehavior;

/**
 * Defines the plugin context itself.  Plugin context is a singleton.
 *
 * @package  Peridot\Plugin\Scenarios
 */
class Plugin
{
    use MockableSingletonBehavior;

    /**
     * @var ContextListener
     */
    private $peridot_context_listener;

    /**
     * @var Reporters\AbstractReporter
     */
    private $scenario_reporter;

    /**
     * @var ScenarioFactory
     */
    private $scenario_factory;

    /**
     * @param ScenarioFactory       $scenario_factory
     * @param ContextListener       $peridot_context_listener
     * @param AbstractReporter      $scenario_reporter
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
