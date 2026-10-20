<?php

namespace App\Services\Sentry;

use Sentry\Tracing\SamplingContext;

class Config
{
    public function getTracesSampler(): callable
    {
        return function (SamplingContext $context): float {
            // Drop liveness check transaction, by setting its sample rate to 0
            if ('GET liveness_check' === $context->getTransactionContext()->getName()) {
                return 0;
            }

            return (float) getenv('SENTRY_TRACE_SAMPLE_RATE');
        };
    }
}
