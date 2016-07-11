<?php

use Peridot\Plugin\Scenarios\Plugin;
use Peridot\Plugin\Scenarios\Scenario;

function inScenario(callable $scenario_setup, callable $scenario_teardown = null)
{
    Plugin::getInstance()
        ->whenScenarioCreated(
            new Scenario($scenario_setup, ($scenario_teardown ?: getNoOp()))
        );
}

function getNoOp()
{
    return function() {
        /* no-op */
    };
}

function setUp($setup_fn)
{
    return $setup_fn;
}

function tearDown($tearDown_fn)
{
    return $tearDown_fn;
}
