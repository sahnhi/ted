<?php

declare(strict_types=1);

namespace App\Services\Sentry;

use App\Context\AdminUserContext;
use Sentry;
use Sentry\Severity;
use Sentry\State\Scope;

final class Reporting implements ReportingInterface
{
    public function __construct(
        private readonly AdminUserContext $adminUserContext
    ) {
    }

    public function reportIssue(
        mixed $issue,
        Severity $severity,
        array $tags = [],
        array $extras = []
    ): void {
        Sentry\configureScope(function (Scope $scope) use ($issue, $severity, $tags, $extras): void {
            $scope->setLevel($severity);
            foreach ($tags as $key => $value) {
                if (is_object($value)) {
                    if (method_exists($value, 'getNumber')) {
                        $value = $value->getNumber();
                    } elseif (method_exists($value, 'getCode')) {
                        $value = $value->getCode();
                    } elseif (method_exists($value, 'getId')) {
                        $value = $value->getId();
                    }
                }
                $scope->setTag($key, (string) $value);
            }
            foreach ($extras as $key => $value) {
                if ($value instanceof \ArrayObject) {
                    $value = (array) $value;
                }
                $scope->setExtra($key, $value);
            }
            if (null !== $admin = $this->adminUserContext->getAdmin()) {
                $scope->setUser(['email' => $admin->getEmail()]);
            }

            if ($issue instanceof \Throwable) {
                Sentry\captureException($issue);
            } else {
                Sentry\captureMessage($issue);
            }
        });
    }
}
