<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\Test\Doubles\TestSetupTeardownAdditionSpy
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Test\Doubles\TestSetupTeardownAdditionSpy;

describe('Peridot\Plugin\Scenarios\Test\Doubles\TestSetupTeardownAdditionSpy', function () {

    $this->noOp = function () {};

    it('should determine if setup/teardown functions were added while the spy was watching', function () {
        $some_test_description = 'description';
        $some_test_definition = function () {};
        $spy_under_test = new TestSetupTeardownAdditionSpy($some_test_description, $some_test_definition);

        $spy_under_test->addSetupFunction($this->noOp);
        assert($spy_under_test->setupAndTeardownFunctionsWereAdded() === false);
        $spy_under_test->addTearDownFunction($this->noOp);
        assert($spy_under_test->setupAndTeardownFunctionsWereAdded() === false);

        $spy_under_test->startWatchingSetupsAndTeardowns();

        $spy_under_test->addTearDownFunction($this->noOp);
        assert($spy_under_test->setupAndTeardownFunctionsWereAdded() === false);
        $spy_under_test->addSetupFunction($this->noOp);
        assert($spy_under_test->setupAndTeardownFunctionsWereAdded());
    });

});
