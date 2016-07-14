<?php

namespace Peridot\Plugin\Scenarios\Test\Doubles;

use Peridot\Plugin\Scenarios\ScenarioComposite;
use Peridot\Core\AbstractTest;

class ScenarioCompositeExplicitTestSetupAndTeardownDouble extends ScenarioComposite
{
    protected $test_setup_fn;
    protected $test_teardown_fn;

    public function __construct(
        AbstractTest $test,
        array $scenarios = [],
        callable $test_setup_fn,
        callable $test_teardown_fn
    ) {
        parent::__construct($test, $scenarios);
        $this->test_setup_fn = $test_setup_fn;
        $this->test_teardown_fn = $test_teardown_fn;
    }

    public function executeTestTeardown()
    {
        $teardown = $this->test_teardown_fn;
        $teardown();
    }

    public function executeTestSetup()
    {
        $setup = $this->test_setup_fn;
        $setup();
    }
}
