<?php

namespace App\Command\Drip;

use App\Services\Drip\DripApiInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncOrdersCommand extends Command
{
    protected static $defaultName = 'app:drip:sync-orders';

    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly DripApiInterface $dripApi
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Sync Sylius orders to Drip')
            ->setHelp('This command will send Orders from the selected range to Drip via its batch API')
            ->addArgument('start', InputArgument::REQUIRED, 'Starting index of the Orders range')
            ->addArgument('limit', InputArgument::REQUIRED, 'Number of Orders to sync (1000 max)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Preparing orders');
        $offset = $input->getArgument('start');
        $limit = $input->getArgument('limit');
        $orders = $this->orderRepository->findAllWithItemsAndCustomer($limit, $offset);
        $output->writeln('Sending orders…');

        $results = $this->dripApi->sendBatchOrders($orders);

        $output->writeln('Results:');
        $output->writeln($results);

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
