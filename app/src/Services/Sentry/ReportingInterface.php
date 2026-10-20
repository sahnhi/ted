<?php

namespace App\Services\Sentry;

use Sentry\Severity;

interface ReportingInterface
{
    public function reportIssue(mixed $issue, Severity $severity, array $tags = [], array $extras = []): void;
}
