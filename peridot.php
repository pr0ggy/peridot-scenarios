<?php

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios;

require __DIR__.'/specs/helper.funcs.php';

return function (EventEmitterInterface $event_emitter)
{
    Scenarios\Plugin::register($event_emitter);
};
