<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Peridot\Core\AbstractTest;

class TestContextSetupAndTeardownSpy extends AbstractTest
{
    protected $setup_func;
    protected $teardown_func;

    public function walkUp(callable $fn)
    {
        $this->teardown_func = $fn;
    }

    public function walkUp(callable $fn)
    {
        $this->teardown_func = $fn;
    }
}
