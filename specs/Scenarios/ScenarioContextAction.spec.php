<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\ScenarioContextAction
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\ScenarioContextAction;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\ScenarioContextAction', function () {
    describe('->inContext($context)', function () {
        it('should return a new instance with the current instance\'s action bound to the given context', function () {
            $context = new stdClass();
            $action_callable = function () {
                    $this->foo = 'bar';
            };
            $context_action = new ScenarioContextAction($action_callable);

            $bound_action = $context_action->inContext($context);
            assert($bound_action instanceof ScenarioContextAction);

            $bound_action();
            assert($context->foo === 'bar');
        });
    });
});
