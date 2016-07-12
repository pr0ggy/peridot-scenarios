<?php
/**
 * Tests for functions defined in src/Scenarios/funcs.php
 */

use function Peridot\Plugin\Scenarios\getNoOp;

describe('Peridot\Plugin\Scenarios\\', function () {
    describe('getNoOp()', function () {
        it ('should return an empty callable', function () {
            assert(is_callable(getNoOp()));
        });
    });
});
