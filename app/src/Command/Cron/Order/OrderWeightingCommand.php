<?php

namespace App\Command\Cron\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\OutOfStock\WeightingCalculation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class OrderWeightingCommand extends Command
{
    protected static $defaultName = 'app:cron:order:weighting';

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $orderManager,
        private readonly WeightingCalculation $weightingCalculation
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Mail Review');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queryBuilder = $this->orderRepository->createOutOfStockListQueryBuilder();
        $results = $queryBuilder->getQuery()->getResult();

        /** @var Order $order */
        foreach ($results as $order) {
            $parent = null !== $order->getParentNumber() ? $this->orderRepository->findOneByNumber($order->getParentNumber()) : null;
            $weighting = $this->weightingCalculation->recalculate($order, $parent);

            $order->setStockPriority(max($weighting, 0));
        }

        $this->orderManager->flush();

        return 0;
    }
}
