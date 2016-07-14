<?php

namespace Peridot\Plugin\Scenarios\Test;

use Mockery as m;

function getFakeTest()
{
    $fake_test = m::mock('Peridot\Core\AbstractTest')->shouldIgnoreMissing();
    $fake_test_scope = m::mock('Peridot\Core\Scope')->shouldIgnoreMissing();
    $fake_test->shouldReceive('getScope')->andReturn($fake_test_scope);
    return $fake_test;
}
