<?php
/**
 * Unit tests for Peridot\Plugin\Scenarios\Scenario
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Test;
use Peridot\Plugin\Scenarios\Scenario;
use Peridot\Plugin\Scenarios\ScenarioContextAction;
use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\Scenario', function () {

    beforeEach(function () {
        $this->fake_scope = m::mock('Peridot\Core\Scope');
        $this->fake_setup_action = Test\createFakeScenarioContextAction();
        $this->fake_teardown_action = Test\createFakeScenarioContextAction();
        $this->scenario = new Scenario(
            $this->fake_setup_action,
            $this->fake_teardown_action
        );
    });

    afterEach(function () {
        m::close();
    });

    describe('->executeSetupInContext($scope)', function () {
        it('should execute the setup action within the given scope context', function () {
            $mock_context_applied_action = Test\createFakeScenarioContextAction();
            $mock_context_applied_action->shouldReceive('__invoke')->once();
            $this->fake_setup_action
                ->shouldReceive('inContext')
                ->once()
                ->with($this->fake_scope)
                ->andReturn($mock_context_applied_action);

            $this->scenario->executeSetupInContext($this->fake_scope);
        });
    });

    describe('->executeTeardownInContext($scope)', function () {
        it('should execute the teardown action within the given scope context', function () {
            $mock_context_applied_action = Test\createFakeScenarioContextAction();
            $mock_context_applied_action->shouldReceive('__invoke')->once();
            $this->fake_teardown_action
                ->shouldReceive('inContext')
                ->once()
                ->with($this->fake_scope)
                ->andReturn($mock_context_applied_action);

            $this->scenario->executeTeardownInContext($this->fake_scope);
        });
    });
});
