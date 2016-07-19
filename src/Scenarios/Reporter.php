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

/**
 * Handles the reporting of failed scenarios to the user after the runner has completed
 *
 * @package  Peridot\Plugin\Scenarios
 */
class Reporter
{
    use HasEventEmitterTrait;

    /**
     * @var SplObjectStorage
     */
    private $test_to_scenario_failure_message_map;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param EventEmitterInterface $event_emitter
     * @param OutputInterface       $output
     */
    public function __construct(
        EventEmitterInterface $event_emitter,
        OutputInterface $output
    ) {
        $this->eventEmitter = $event_emitter;
        $this->output = $output;
        $this->test_to_scenario_failure_message_map = new SplObjectStorage();
        $this->eventEmitter->on('runner.start', [$this, 'registerEventHandlers']);
    }

    /**
     * Registers handlers for all relevant events fired from the EventEmitterInterface
     * dependency
     */
    public function registerEventHandlers()
    {
        $this->eventEmitter->on('test.failed', [$this, 'registerTestFailure']);
        $this->eventEmitter->on('runner.end', [$this, 'printInfoOnAnyScenarioFailures']);
    }

    /**
     * Registers a failed test along with the proper scenario failure message to display
     * based on information from the test and the exception given that caused the test
     * failure
     *
     * @param  TestInterface $test
     * @param  Exception     $e
     */
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

    /**
     * Prints each failed test description heirarchy to the console, along with the scenario
     * failure message associated with the failed test
     */
    public function printInfoOnAnyScenarioFailures()
    {
        if (count($this->test_to_scenario_failure_message_map) === 0) {
            return;
        }

        $this->registerOutputStyles();

        $this->output->writeln('');
        $this->output->writeln('  Scenario Failures');
        $this->output->writeln('  -------------------');
        foreach ($this->test_to_scenario_failure_message_map as $test) {
            $this->printScenarioFailureMessageForTest($test, $this->test_to_scenario_failure_message_map[$test]);
            $this->output->writeln('');
        }
    }

    /**
     * Registers any needed formatting style sets with the OutputInterface dependency
     */
    protected function registerOutputStyles()
    {
        $this->output->getFormatter()->setStyle('failure', new OutputFormatterStyle('red', null, array('bold')));
    }

    /**
     * Prints a failed test description heirarchy to the console, along with a given failure
     * message
     *
     * @param  TestInterface $test
     * @param  string        $scenario_failure_message
     */
    protected function printScenarioFailureMessageForTest(TestInterface $test, $scenario_failure_message)
    {
        $test_heirarchy_descriptions = $this->getDescriptionStackForTest($test);
        $this->outputDescriptionHeirarchy($test_heirarchy_descriptions);
        $this->outputScenarioFailureMessage($scenario_failure_message, count($test_heirarchy_descriptions));
    }

    /**
     * Returns a stack of descriptions for a given test.  The descriptions are fetched and
     * pushed onto the stack in a 'walk-up' manner, starting with the test, then moving up
     * to a potentially-nested set of contexts or suites.  Note that we could have used a
     * 'walk-down' approach with a queue to achieve the same result.
     *
     * @param  TestInterface $test
     * @return SplStack
     */
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

    /**
     * Prints a test description heirarchy out to the console, handling indentation to
     * show the heirarchy in a meaningful way
     *
     * @param  Iterator $descriptions
     */
    protected function outputDescriptionHeirarchy(Iterator $descriptions)
    {
        $heirarchy_level = 1;
        foreach ($descriptions as $description) {
            $this->output->writeln($this->indent($heirarchy_level, $description));
            ++$heirarchy_level;
        }
    }

    /**
     * Prints out a given failure message with proper formatting, using the heirarchy level
     * argument to determine the proper indentation level
     *
     * @param  string $scenario_failure_message
     * @param  int    $heirarchy_level
     */
    protected function outputScenarioFailureMessage($scenario_failure_message, $heirarchy_level)
    {
        $this->output->writeln($this->indent($heirarchy_level, "<failure>{$scenario_failure_message}</>"));
    }

    /**
     * Pads the given test with a number of spaces determined by the given indent level to
     * return a string representing the given text indented
     *
     * @param  int    $indent_level
     * @param  string $text
     * @return string
     */
    protected function indent($indent_level, $text)
    {
        return implode('  ', array_fill(0, $indent_level + 1, '')) . $text;
    }
}
