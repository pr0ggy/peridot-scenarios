Peridot Scenarios Plugin
====================
Execute a single test definition against multiple scenarios

Usage
--------
Imagine a simple FizzBuzz function:
```php
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
```

A simple test to ensure the function produces 'Fizz' as expected may read as:
```php
describe('convertValueToFizzBuzz($value)', function() {

    context('when $value is divisible by 3 but not 5', function() {
        it('should return "Fizz"', function() {
            assert(convertValueToFizzBuzz(3) === 'Fizz');
        });
    });

});
```

It would be nice to be able to run this test against more than 1 value.  There are various ways to do this without any plugins using loops or simply repeating the assertion for different values, but all of these add either duplication or noise to the test.  The scenario plugin aims to provide this functionality while keeping test definitions succinct:
```php
describe('convertValueToFizzBuzz($value)', function() {

    context('when $value is divisible by 3 but not 5', function() {
        it('should return "Fizz"', function() {
            assert(convertValueToFizzBuzz($this->expected_fizz_value) === 'Fizz');
        });
		inScenario(['expected_fizz_value' => 3]);
		inScenario(['expected_fizz_value' => 6]);
		inScenario(['expected_fizz_value' => 9]);
		inScenario(['expected_fizz_value' => 99]);
    });

});
```

Scenarios may be defined via a simple associative array as shown above, where each key/value pair will be applied as members of the context during execution of the test definition.  In more complex scenarios, specific setup and teardown callbacks may be given:
```php
it('should return "Fizz"', function() {
	assert(convertValueToFizzBuzz($this->expected_fizz_value) === 'Fizz');
});
inScenario(
	setUp(function () {
		$this->expected_fizz_value = 3;
	}),
	tearDown(function () {
		unset($this->expected_fizz_value);
	})
);
inScenario(
	setUp(function () {
		$this->expected_fizz_value = 6;
	}),
	tearDown(function () {
		unset($this->expected_fizz_value);
	})
);
// ...
```
Note that the teardown function as defined above is technically unnecessary, it's merely shown as a usage example.

Each scenario execution hooks into the test execution flow as follows:

1. Execution of all setup callbacks associated with test
2. Execution of scenario setup
3. Execution of test definition
4. Execution of scenario teardown
5. Execution of all teardown callbacks associated with test

If all scenarios associated with a given test pass, you should see the standard success output from your reporter.  If any fail, you will see the standard failure messages, along with a scenario report denoting which scenarios in each failing test caused the failure:

![Scenario Failure Message Example](http://i.imgur.com/CfRJ9Z9.png)

Gotchas
----------
Be careful to only add scenarios immediately after a test definition.  For example, attempting to add a scenario to the context in the example above:
```php
describe('convertValueToFizzBuzz($value)', function() {

    context('when $value is divisible by 3 but not 5', function() {
        it('should return "Fizz"', function() {
            assert(convertValueToFizzBuzz($this->expected_fizz_value) === 'Fizz');
        });
    });
    inScenario(['expected_fizz_value' => 3]);
	inScenario(['expected_fizz_value' => 6]);
	inScenario(['expected_fizz_value' => 9]);
	inScenario(['expected_fizz_value' => 99]);

});
```
...will result in an exception:

![Scenario Addition Exception](http://imgur.com/TSLsXErl.png)

Installation
---------------
`composer require --dev pr0ggy/peridot-scenarios`

Register the plugin in your `peridot.php` file via:
```php
<?php

use Peridot\EventEmitterInterface;
use Peridot\Plugin\Scenarios;

return function (EventEmitterInterface $event_emitter)
{
    Scenarios\Plugin::register($event_emitter);
};
```

FizzBuzz Example Tests
-------------------------------
`./vendor/bin/peridot ./fixtures/scenario-usage.spec.php`

Plugin Tests
----------------
`./vendor/bin/peridot ./specs/`
