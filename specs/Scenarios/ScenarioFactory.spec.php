<?php
/**
 * Unti tests for Peridot\Plugin\Scenarios\ScenarioFactory
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\ScenarioFactory;
use Peridot\Plugin\Scenarios\Test\Doubles\ScenarioFactorySetupTeardownConversionSpy;

describe('Peridot\Plugin\Scenarios\ScenarioFactory', function () {

    beforeEach(function () {
        $this->scenario_factory_spy = new ScenarioFactorySetupTeardownConversionSpy();
    });

    describe('->createScenario($setup, $teardown)', function () {
        context('with callable $setup', function () {
            it('leaves setup unchanged', function () {
                $scenario =
                    $this->scenario_factory_spy
                        ->createScenario($this->callable_setup);

                assert($this->scenario_factory_spy->setupGivenToCreatedScenarioWas($this->callable_setup));
            });
            inScenario(['callable_setup'=>'\Peridot\Plugin\Scenarios\getNoOp']);
            inScenario(
                setUp(function () {
                    $d = new DateTime();
                    $this->callable_setup = [$d, 'getTimestamp'];
                })
            );
        });

        context('with a k/v map given as $setup', function () {
            it('converts setup to callable', function () {
                $scenario =
                    $this->scenario_factory_spy
                        ->createScenario(['foo' => 'bar']);

                assert($this->scenario_factory_spy->setupGivenToCreatedScenarioWasCallable());
            });
        });

        context('with any other type given as $setup', function () {
            it('throws a TypeError', function () {
                try {
                    $scenario =
                        $this->scenario_factory_spy
                            ->createScenario($this->bad_setup_value);
                } catch (TypeError $e) {
                    return;
                }

                throw new Exception('Failed to throw InvalidArgumentException when bad scenario setup argument given');
            });
            inScenario(['bad_setup_value' => 1]);
            inScenario(['bad_setup_value' => 'foo']);
            inScenario(['bad_setup_value' => true]);
            inScenario(['bad_setup_value' => new stdClass()]);
        });

        context('with callable $teardown', function () {
            it('leaves teardown unchanged', function () {
                $scenario =
                    $this->scenario_factory_spy
                        ->createScenario([], $this->callable_teardown);

                assert($this->scenario_factory_spy->teardownGivenToCreatedScenarioWas($this->callable_teardown));
            });
            inScenario(['callable_teardown'=>'\Peridot\Plugin\Scenarios\getNoOp']);
            inScenario(
                setUp(function () {
                    $d = new DateTime();
                    $this->callable_teardown = [$d, 'getTimestamp'];
                })
            );
        });

        context('with null $teardown', function () {
            it('generates a no-op teardown', function () {
                $scenario =
                    $this->scenario_factory_spy
                        ->createScenario([]);

                assert($this->scenario_factory_spy->teardownGivenToCreatedScenarioWasCallable());
            });
        });

        context('with any other type given as $teardown', function () {
            it('throws a TypeError', function () {
                try {
                    $scenario =
                        $this->scenario_factory_spy
                            ->createScenario([], $this->bad_teardown_value);
                } catch (TypeError $e) {
                    return;
                }

                throw new Exception('Failed to throw TypeError when bad scenario setup argument given');
            });
            inScenario(['bad_teardown_value' => 1]);
            inScenario(['bad_teardown_value' => 'foo']);
            inScenario(['bad_teardown_value' => true]);
            inScenario(['bad_teardown_value' => ['foo' => 'bar']]);
            inScenario(['bad_teardown_value' => new stdClass()]);
        });
    });

});
