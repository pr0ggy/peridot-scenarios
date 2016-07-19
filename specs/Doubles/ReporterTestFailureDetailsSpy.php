<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use SplObjectStorage;
use Peridot\Plugin\Scenarios\Reporter;
use Peridot\Core\TestInterface;
use Peridot\EventEmitterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Peridot\Plugin\Scenarios\Reporter spy double which allows more detailed inspection
 * of the test to failure message map
 *
 * @package Peridot\Plugin\Scenarios\Test\Doubles
 */
class ReporterTestFailureDetailsSpy extends Reporter
{
    /**
     * @param SplObjectStorage $initial_test_failure_map
     */
    public function __construct(
        EventEmitterInterface $event_emitter,
        OutputInterface $output,
        SplObjectStorage $initial_test_failure_map
    ) {
        parent::__construct($event_emitter, $output);
        $this->test_to_scenario_failure_message_map = $initial_test_failure_map;
    }

    /**
     * @return int
     */
    public function getScenarioFailureCount()
    {
        return count($this->test_to_scenario_failure_message_map);
    }

    /**
     * @param  TestInterface $test
     * @return string|null
     */
    public function getScenarioFailureMessageForTest(TestInterface $test)
    {
        return $this->test_to_scenario_failure_message_map[$test];
    }
}
