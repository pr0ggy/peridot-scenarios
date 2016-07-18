<?php

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/specs/helper.funcs.php';

return function (EventEmitterInterface $event_emitter)
{
    Scenarios\Plugin::createAndRegisterSingletonWithConstructionArgs(
        new Scenarios\ScenarioFactory(),
        new Scenarios\ContextListener($event_emitter),
        new Scenarios\Reporter($event_emitter, new ConsoleOutput())
    );
};
