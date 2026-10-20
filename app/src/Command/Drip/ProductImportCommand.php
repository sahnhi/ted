<?php

namespace App\Command\Drip;

use App\Services\Drip\DripApiInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ProductImportCommand extends Command
{
    protected static $defaultName = 'app:drip:import-products';

    public function __construct(
        private readonly RepositoryInterface $productVariantRepository,
        private readonly RepositoryInterface $channelRepository,
        private readonly DripApiInterface $dripApi
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import Products to Drip')
            ->addArgument('start', InputArgument::REQUIRED, 'Starting index of the product range')
            ->addArgument('limit', InputArgument::REQUIRED, 'Number of products to sync (1000 max)')
            ->addArgument('channel', InputArgument::REQUIRED, 'Get The Product From specific channel')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching products');
        $offset = $input->getArgument('start');
        $limit = $input->getArgument('limit');
        $channel = $input->getArgument('channel');
        $channel = $this->channelRepository->findOneByCode($channel);

        if (null === $channel) {
            $output->writeln('Channel ' . $channel . ' does not exists');

            return 1;
        }

        if ($limit > 1000) {
            $output->writeln('Limit set to 1000, it cannot be more');
            $limit = 1000;
        }

        $productVariants = $this->productVariantRepository->findByChannelWithPrices($channel, $limit, $offset);
        $output->writeln('Sending Products...');

        $results = $this->dripApi->sendBatchProducts($productVariants, $channel);

        $output->writeln('Results:');
        $output->writeln($results);

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
