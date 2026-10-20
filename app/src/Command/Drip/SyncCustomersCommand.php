<?php

namespace App\Command\Drip;

use App\Services\Drip\DripApiInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCustomersCommand extends Command
{
    protected static $defaultName = 'app:drip:sync-customers';

    public function __construct(
        private readonly RepositoryInterface $customerRepository,
        private readonly DripApiInterface $dripApi
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Sync Sylius customers to Drip')
            ->setHelp('This command will send Customers from the selected range to Drip via its batch API')
            ->addArgument('start', InputArgument::REQUIRED, 'Starting index of the Customers range')
            ->addArgument('limit', InputArgument::REQUIRED, 'Number of Customers to sync (1000 max)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Preparing customers');
        $offset = $input->getArgument('start');
        $limit = $input->getArgument('limit');
        $customers = $this->customerRepository->findAllWithAddress($limit, $offset);
        $output->writeln('Sending customers…');

        $results = $this->dripApi->sendBatchCustomers($customers);

        $output->writeln('Results:');
        $output->writeln($results);

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
