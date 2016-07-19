<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Mockery as m;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Symfony\Component\Console\Output\Output double that simply records the argument strings
 * of calls to the writeln() method, in the order called
 *
 * @package Peridot\Peridot\Plugin\Scenarios\Test\Doubles
 */
class OutputInterfaceWriteLineRecordingSpy extends ConsoleOutput
{
    private $output_lines = [];

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $options = self::OUTPUT_NORMAL)
    {
        foreach ((array) $messages as $line) {
            $this->output_lines[] = $line;
        }
    }

    /**
     * @param  array  $expected_lines
     * @return boolean
     */
    public function outputLinesMatch(array $expected_lines)
    {
        return ($this->output_lines === $expected_lines);
    }
}
