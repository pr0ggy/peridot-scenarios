<?php

namespace Peridot\Plugin\Scenarios;

use SplObjectStorage;
use SplStack;
use Iterator;
use Exception;
use Peridot\Core\TestInterface;
use Peridot\EventEmitterInterface;
use Peridot\Core\HasEventEmitterTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Reporter
{
    use HasEventEmitterTrait;

    private $test_to_scenario_failure_message_map;

    private $output;

    public function __construct(
        EventEmitterInterface $event_emitter,
        OutputInterface $output
    ) {
        $this->eventEmitter = $event_emitter;
        $this->output = $output;
        $this->test_to_scenario_failure_message_map = new SplObjectStorage();
        $this->registerOutputStyles();
        $this->eventEmitter->on('runner.start', [$this, 'registerEventHandlers']);
    }

    public function registerOutputStyles()
    {
        $this->output->getFormatter()->setStyle('failure', new OutputFormatterStyle('red', null, array('bold')));
    }

    public function registerEventHandlers()
    {
        $this->eventEmitter->on('test.failed', [$this, 'registerTestFailure']);
        $this->eventEmitter->on('runner.end', [$this, 'printInfoOnAnyScenarioFailures']);
    }

    public function registerTestFailure(TestInterface $test, Exception $e)
    {
        if ($test->explicitly_defined_scenario_count < 2) {
            return;
        }

        $failed_scenario_message =
            isset($e->failed_scenario_index)
                ? "SCENARIO {$e->failed_scenario_index} FAILED"
                : "LAST SCENARIO FAILED";

        $this->test_to_scenario_failure_message_map[$test] = $failed_scenario_message;
    }

    public function printInfoOnAnyScenarioFailures()
    {
        if (count($this->test_to_scenario_failure_message_map) === 0) {
            return;
        }

        $this->output->writeln('');
        $this->output->writeln('  Scenario Failures');
        $this->output->writeln('  -------------------');
        foreach ($this->test_to_scenario_failure_message_map as $test) {
            $this->printScenarioFailureMessageForTest($test, $this->test_to_scenario_failure_message_map[$test]);
            $this->output->writeln('');
        }
    }

    protected function printScenarioFailureMessageForTest(TestInterface $test, $scenario_failure_message)
    {
        $test_heirarchy_descriptions = $this->getDescriptionStackForTest($test);
        $this->outputDescriptionHeirarchy($test_heirarchy_descriptions);
        $this->outputScenarioFailureMessage($scenario_failure_message, count($test_heirarchy_descriptions));
    }

    protected function getDescriptionStackForTest(TestInterface $test)
    {
        $descriptions = new SplStack();
        $test->walkUp(function (TestInterface $test) use ($descriptions) {
            $description = $test->getDescription();
            if (empty($description)) {
                return;
            }

            $descriptions->push($description);
        });

        return $descriptions;
    }

    protected function outputDescriptionHeirarchy(Iterator $descriptions)
    {
        $heirarchy_level = 1;
        foreach ($descriptions as $description) {
            $this->output->writeln($this->indent($heirarchy_level, $description));
            ++$heirarchy_level;
        }
    }

    protected function outputScenarioFailureMessage($scenario_failure_message, $heirarchy_level)
    {
        $this->output->writeln($this->indent($heirarchy_level, "<failure>{$scenario_failure_message}</>"));
    }

    protected function indent($indent_level, $text)
    {
        return implode('  ', array_fill(0, $indent_level + 1, '')) . $text;
    }
}
