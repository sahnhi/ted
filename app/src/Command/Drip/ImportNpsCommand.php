<?php

namespace App\Command\Drip;

use App\Services\Drip\DripApiInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportNpsCommand extends Command
{
    protected static $defaultName = 'app:drip:import-nps';

    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly DripApiInterface $dripApi
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import NPS to Drip')
            ->setHelp('Send NPS to Drip')
            ->addArgument('start', InputArgument::REQUIRED, 'Starting index of the product reviews range')
            ->addArgument('limit', InputArgument::REQUIRED, 'Number of product reviews to sync (1000 max)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Preparing product reviews');
        $offset = $input->getArgument('start');
        $limit = $input->getArgument('limit');
        $productReviews = $this->orderRepository->findBy([], [], $limit, $offset);
        $output->writeln('Sending NPS...');

        $results = $this->dripApi->sendBatchNPS($productReviews);

        $output->writeln('Results:');
        $output->writeln($results);

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
