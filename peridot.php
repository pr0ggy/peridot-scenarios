<?php

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios;

return function (EventEmitterInterface $event_emitter)
{
    Scenarios\Plugin::registerSingletonInstance(
        new Scenarios\Plugin(
            new Scenarios\ScenarioFactory(),
            new Scenarios\ContextListener($event_emitter),
            new Scenarios\Reporters\SpecReporter($event_emitter)
        )
    );
};
