<?php

declare(strict_types=1);

namespace App\Analytic\CommandHandler;

use App\Analytic\Client\AnalyticClient;
use App\Analytic\Command\ImportOrderCommand;
use App\Analytic\Model\Factory\OrderFactory;
use App\Services\Sentry\ReportingInterface;
use Psr\Log\LoggerInterface;
use Sentry\Severity;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImportOrderHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly AnalyticClient $analyticClient,
        private readonly OrderFactory $orderFactory,
        private readonly LoggerInterface $rimdianLogger,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(ImportOrderCommand $command): void
    {
        $this->rimdianLogger->info(sprintf('New Sylius -> Rimdian import handler single order with id %s', $command->getOrder()->getId()));

        try {
            $order = $this->orderFactory->create($command->getOrder());
        } catch (\Exception $e) {
            $this->rimdianLogger->error(sprintf('Failed -> Rimdian import single order with id %s, failed creating body', $command->getOrder()->getId()));
            $this->reporting->reportIssue(
                $e->getMessage(),
                Severity::error(),
                ['rimdian' => 'ImportOrderHandler:instanciate body failed']
            );

            return;
        }

        try {
            $this->analyticClient->sendOrder($order);
        } catch (\Throwable $e) {
            $this->rimdianLogger->error(sprintf('Failed -> Rimdian import single order with id %s', $command->getOrder()->getId()));
            $this->reporting->reportIssue(
                $e->getMessage(),
                Severity::error(),
                ['rimdian' => 'ImportOrderHandler:clientFailed'],
                ['order' => array_merge($order->orderToArray(), $order->userToArray())]
            );

            return;
        }

        $this->rimdianLogger->info(sprintf('Success -> Rimdian import single order with id %s', $command->getOrder()->getId()));
    }
}
