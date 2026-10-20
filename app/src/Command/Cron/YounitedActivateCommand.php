<?php

namespace App\Command\Cron;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Legacy\Younited\YounitedPaymentProcessorInterface;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class YounitedActivateCommand extends Command
{
    protected static $defaultName = 'app:cron:younited-activate';

    public function __construct(
        private readonly YounitedPaymentProcessorInterface $younitedPaymentProcessor,
        private readonly OrderRepository $orderRepository,
        private readonly ReportingInterface $reporting
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Younited Activate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->orderRepository->findYounitedContractsToActivate();
        $bis = $this->orderRepository->findYounitedBisContractsToActivate();

        $orders = array_merge($orders, $bis);

        foreach ($orders as $order) {
            try {
                $this->younitedPaymentProcessor->activateAllByOrder($order);
            } catch (\Exception $e) {
                $this->reporting->reportIssue(
                    $e,
                    Sentry\Severity::error(),
                    ['YounitedActivation' => $order->getNumber()]
                );
            }
        }

        return 0;
    }
}
