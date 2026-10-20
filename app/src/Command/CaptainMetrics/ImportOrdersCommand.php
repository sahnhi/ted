<?php

namespace App\Command\CaptainMetrics;

use App\Services\CaptainMetrics\CaptainMetricsApi;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportOrdersCommand extends Command
{
    protected static $defaultName = 'app:captainmetrics:import-orders';

    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly CaptainMetricsApi $captainMetricsApi
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import Orders to CaptainMetrics')
            ->addArgument('start', InputArgument::REQUIRED, 'Starting index of orders')
            ->addArgument('limit', InputArgument::REQUIRED, 'Number of orders to import (max 10 000)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Preparing orders');

        $offset = $input->getArgument('start');
        $limit = $input->getArgument('limit');
        $limit = $limit > 10000 ? 10000 : $limit;
        $count = 0;

        while ($limit > 0) {
            $orders = $this->orderRepository->findAllNonAnonymousPaidInitialOrders($offset, $limit > 100 ? 100 : $limit);
            $output->writeln('Sending to CaptainMetrics...');

            $results = $this->captainMetricsApi->sendBatchOrders($orders);

            $output->writeln('Results:');
            $output->writeln($results);

            $total = count($orders);
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
