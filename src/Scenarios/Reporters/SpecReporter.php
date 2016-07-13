<?php

namespace Peridot\Plugin\Scenarios\Reporters;

use Peridot\Reporter\SpecReporter as PeridotSpecReporter;

class SpecReporter extends AbstractReporter
{
    public function getScenarioFailureReportingCallbackForMessage($scenario_failure_message)
    {
        return function (PeridotSpecReporter $reporter) use ($scenario_failure_message) {
            $reporter->getOutput()->write(sprintf(
                "  %s%s",
                (method_exists($reporter, 'indent') ? $reporter->indent() : ''),
                $reporter->color('error', sprintf("%s", $scenario_failure_message))
            ));
        };
    }
}
