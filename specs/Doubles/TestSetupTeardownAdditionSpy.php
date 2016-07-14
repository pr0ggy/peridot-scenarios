<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Peridot\Core\Test;

class TestSetupTeardownAdditionSpy extends Test
{
    protected $watching_setup_teardowns = false;
    protected $setup_function_added_while_watching = false;
    protected $teardown_function_added_while_watching = false;

    public function startWatchingSetupsAndTeardowns()
    {
        $this->watching_setup_teardowns = true;
    }

    public function addSetupFunction(callable $setupFn)
    {
        parent::addSetupFunction($setupFn);
        if ($this->watching_setup_teardowns) {
            $this->setup_function_added_while_watching = true;
        }
    }

    public function addTearDownFunction(callable $tearDownFn)
    {
        parent::addTearDownFunction($tearDownFn);
        if ($this->watching_setup_teardowns) {
            $this->teardown_function_added_while_watching = true;
        }
    }

    public function setupAndTeardownFunctionsWereAdded()
    {
        return $this->setup_function_added_while_watching
               && $this->teardown_function_added_while_watching;
    }
}
