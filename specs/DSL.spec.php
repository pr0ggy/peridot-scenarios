<?php
/**
 * Defines test for functions defined in src/DSL.php
 */

use Mockery as m;
use Peridot\Plugin\Scenarios\Plugin;

describe('DSL Extension', function () {

    $this->noOp = function () { };

    describe('inScenario', function () {
        /*
         * remember, scenario plugin singleton is referenced from the inScenario function and the
         * scenarios plugin is being used as these tests are running...have to do a wholesale swap
         * on the plugin context
         */
        $this->mock_plugin_context =
            m::mock('Peridot\Plugin\Scenarios\Plugin')
                ->shouldReceive('registerNewScenario')
                ->once()
                ->getMock();

        $this->original_plugin_context = Plugin::getInstance();

        beforeEach(function () {
            Plugin::unregisterSingletonInstance();
            Plugin::registerSingletonInstance($this->mock_plugin_context);
        });

        afterEach(function () {
            m::close();

            Plugin::unregisterSingletonInstance();
            Plugin::registerSingletonInstance($this->original_plugin_context);
        });

        it('should notify plugin instance of new scenario', function() {
            $this->mock_plugin_context
                ->shouldReceive('registerNewScenario')
                ->with(m::type('callable'), m::type('callable'));

            inScenario([$this, 'noOp'], [$this, 'noOp']);
        });

        it('should allow pass-thru of any exception generated during call', function () {
            $exception = new Exception('Test exception');
            Plugin::unregisterSingletonInstance();
            Plugin::registerSingletonInstance(
                m::mock('Peridot\Plugin\Scenarios\Plugin')
                    ->shouldReceive('registerNewScenario')
                    ->andThrow($exception)
                    ->getMock()
            );

            try {
                inScenario([]);
            } catch (Exception $e) {
                assert($e === $exception);
                return;
            }

            throw new Exception('Failed to allow pass-thru of generated exception');
        });

        it('should allow closure as setup arg', function () {
            inScenario(function () {});
        });

        it('should allow callable as setup arg', function () {
            inScenario([$this, 'noOp']);
        });

        it('should allow simple value map as setup arg', function () {
            inScenario(['foo' => 'bar']);
        });

        it('should allow closure as teardown arg', function () {
            $some_setup_arg = [$this, 'noOp'];
            inScenario($some_setup_arg, function () {});
        });

        it('should allow callable as teardown arg', function () {
            $some_setup_arg = [$this, 'noOp'];
            inScenario($some_setup_arg, [$this, 'noOp']);
        });
    });

    describe('setUp', function () {
        it('should return given argument', function () {
            assert(setUp($this->argument) === $this->argument);
        });
        inScenario(['argument' => 1]);
        inScenario(['argument' => 'test']);
        inScenario(['argument' => function () {}]);
    });

    describe('tearDown', function () {
        it('should return given argument', function () {
            assert(setUp($this->argument) === $this->argument);
        });
        inScenario(['argument' => 1]);
        inScenario(['argument' => 'test']);
        inScenario(['argument' => function () {}]);
    });

});
