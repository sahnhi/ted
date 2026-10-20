<?php

namespace App\Services\Transmission\Transporteur;

use App\Edeliv\Client\EdelivClientInterface;
use App\Services\Sentry\ReportingInterface;
use App\Services\Transmission\AbstractTransmissionService;
use Sentry;

class EDelivService extends AbstractTransmissionService
{
    protected array $normalizeArray;
    protected array $headerLine;

    public function __construct(
        private readonly EdelivClientInterface $edelivClient,
        private readonly ReportingInterface $reporting
    ) {
        parent::__construct();
    }

    public function callUrbantz(string $number): void
    {
        try {
            $this->edelivClient->cancelTask($number);
        } catch (\Throwable $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['cancel_urbantz_api' => $number]
            );
        }
    }
}
