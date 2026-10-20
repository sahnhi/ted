<?php

declare(strict_types=1);

namespace App\Command\Orders;

use App\Doctrine\Repository\Sylius\OrderRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOldOrdersCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:orders:delete-old')
            ->setDescription('Delete old orders that have not been modified since the delay passed in parameter (in months, default : 10)')
            ->addOption('delay', 'd', InputOption::VALUE_REQUIRED, 'Delay we want to keep data', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $delay = $input->getOption('delay');
        $maxDate = new \DateTimeImmutable('-' . $delay . ' months');

        $io->note(sprintf('Deleting orders which are not modified before %s (%d months)', $maxDate->format('d/m/Y'), $delay));

        $total = $this->orderRepository->countOldBefore($maxDate);
        $ordersToDelete = $this->orderRepository->findOldBefore($maxDate);

        $io->progressStart($total);
        foreach ($ordersToDelete as $order) {
            $this->orderRepository->remove($order);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(sprintf('Deleted %d orders.', $total));

        return 0;
    }
}
