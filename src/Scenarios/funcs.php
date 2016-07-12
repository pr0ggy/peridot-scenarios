<?php

namespace Peridot\Plugin\Scenarios;

/**
 * Returns an empty no-op callable
 *
 * @return callable
 */
function getNoOp()
{
    return function () {
        /* no-op */
    };
}
