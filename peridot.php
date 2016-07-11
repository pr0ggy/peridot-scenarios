<?php

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios;

return function (EventEmitterInterface $event_emitter)
{
    Scenarios\Plugin::getInstance()->registerEmitter($event_emitter);
    Scenarios\Plugin::getInstance()->registerContextListener(
        new Scenarios\ContextListener(
            $event_emitter,
            new Scenarios\Reporter($event_emitter)
        )
    );
};
