<?php

namespace App\Command\Cron\Transmission;

use App\Services\Cron\Transmission\PackingProcessorInterface;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ProcessPackingCommand extends Command
{
    protected static $defaultName = 'app:cron:transmission:process-packing';

    public function __construct(
        private readonly PackingProcessorInterface $packingProcessor,
        private readonly ReportingInterface $reporting
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Transmission Process Packing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->packingProcessor->process();
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
