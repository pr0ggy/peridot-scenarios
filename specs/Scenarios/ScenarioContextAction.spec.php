<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\ScenarioContextAction
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\ScenarioContextAction;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\ScenarioContextAction', function () {
    describe('->executeInContext($context)', function () {
        it('should execute the current instance\'s action bound to the given context', function () {
            $context = new stdClass();
            $action_callable = function () {
                    $this->foo = 'bar';
            };
            $context_action = new ScenarioContextAction($action_callable);

            $context_action->executeInContext($context);

            assert($context->foo === 'bar');
        });
    });
});
