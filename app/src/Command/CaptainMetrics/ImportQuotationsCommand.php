<?php

namespace App\Command\CaptainMetrics;

use App\Services\CaptainMetrics\CaptainMetricsApi;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportQuotationsCommand extends Command
{
    protected static $defaultName = 'app:captainmetrics:import-quotations';

    public function __construct(
        private readonly RepositoryInterface $orderStoreRepository,
        private readonly CaptainMetricsApi $captainMetricsApi
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import Quotations to CaptainMetrics')
            ->addArgument('start', InputArgument::REQUIRED, 'Starting index of quotations')
            ->addArgument('limit', InputArgument::REQUIRED, 'Number of quotations to import (max 10 000)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Preparing quotations');

        $offset = $input->getArgument('start');
        $limit = $input->getArgument('limit');
        $limit = $limit > 10000 ? 10000 : $limit;
        $count = 0;

        while ($limit > 0) {
            // CM max items is 100, we need to put max 33 because quotations is splitted between 3 different items
            $orderStores = $this->orderStoreRepository->getQuotations($limit > 33 ? 33 : $limit, $offset);
            $output->writeln('Sending to CaptainMetrics...');

            $results = $this->captainMetricsApi->sendBatchOrderStores($orderStores);

            $output->writeln('Results:');
            $output->writeln($results);

            $total = count($orderStores);
            $output->writeln('Offset : ' . $offset);

            $count += $total;
            $limit -= $total;
            $offset += $total;

            $output->writeln('Done [' . $count . '] of [' . $input->getArgument('limit') . ']');
        }

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
