<?php

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios;

return function (EventEmitterInterface $event_emitter)
{
    Scenarios\Plugin::registerSingletonInstance(
        new Scenarios\Plugin(
            $event_emitter,
            new Scenarios\ContextListener(
                $event_emitter,
                new Scenarios\Reporter($event_emitter)
            )
        )
    );

    Scenarios\ScenarioFactory::createAndRegisterSingletonWithConstructionArgs();
};
