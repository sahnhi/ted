<?php

namespace App\Command\Cron\Transmission;

use App\Services\Cron\Transmission\TrackingProcessorInterface;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PrepareTrackingCommand extends Command
{
    protected static $defaultName = 'app:cron:transmission:prepare-tracking';

    public function __construct(
        private readonly TrackingProcessorInterface $trackingProcessor,
        private readonly ReportingInterface $reporting
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Transmission Prepare Tracking');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->trackingProcessor->prepare();
        } catch (\Exception $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['Error message' => $exception->getMessage()]
            );
        }

        return 0;
    }
}
