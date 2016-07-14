<?php

function convertValueToFizzBuzz($value)
{
    $response = '';

    if ($value % 3 === 0) {
        $response .= 'Fizz';
    }

    if ($value % 5 === 0) {
        $response .= 'Buzz';
    }

    return ($response ?: $value);
}


describe('convertValueToFizzBuzz($value)', function() {

    context('when $value is divisible by 3 and 5', function() {
        it('should return "FizzBuzz"', function() {
            assert(convertValueToFizzBuzz($this->expected_fizzbuzz_value) === 'FizzBuzz');
        });
        inScenario(['expected_fizzbuzz_value'=>0]);
        inScenario(
            setUp(function() { echo "S2S\n"; $this->expected_fizzbuzz_value = 15; }),
            function () {echo "S2T\n";}
        );
        inScenario(
            setUp(function() { echo "S3S\n";$this->expected_fizzbuzz_value = 45; }),
            function () {echo "S3T\n";}
        );
        inScenario(
            setUp(function() { echo "S4S\n";$this->expected_fizzbuzz_value = 60; }),
            function () {echo "S4T\n";}
        );
    });

    context('when $value is divisible by 3 but not 5', function() {
        it('should return "Fizz"', function() {
            assert(convertValueToFizzBuzz(3) === 'Fizz');
        });
    });

    context('when $value is divisible by 5 but not 3', function() {
        it('should return "Buzz"', function() {
            assert(convertValueToFizzBuzz($this->expected_buzz_value) === 'Buzz');
        });
        inScenario(
            setUp(function() { $this->expected_buzz_value = 65; })
        );inScenario(
            setUp(function() { $this->expected_buzz_value = 10; })
        );
    });

    context('when $value is not divisible by 3 or 5', function() {
        it('should return the original value', function() {
            assert(convertValueToFizzBuzz(1) === 1);
        });
    });

});
