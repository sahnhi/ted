<?php

namespace App\Command\Cron\Order;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\Transmission\Tracking\TrackingService;
use App\Services\Vir\VirApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetTrackingUrlCommand extends Command
{
    protected static $defaultName = 'app:cron:order:tracking-url';

    public const SHIPPING_METHOD = 'VIR';
    private string|false $virApiToken = false;

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly TrackingService $trackingService,
        private readonly EntityManagerInterface $entityManager,
        private readonly VirApi $virApi
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Order Tracking Url');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = $this->virApi->getToken();
        if ($token) {
            /* Remove the bom */
            $token = str_replace("\xEF\xBB\xBF", '', $token);
            /* Remove %22 */
            $this->virApiToken = str_replace("\x22", '', $token);
        }

        $orders = $this->orderRepository->findAllShippedByShippingMethod(self::SHIPPING_METHOD);
        foreach ($orders as $order) {
            $url = $this->virApi->getUrlTracking($order->getNumber(), $this->virApiToken);
            $this->trackingService->setTrackingVir($url, $order);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        if ($this->virApiToken) {
            $this->virApi->releaseToken($this->virApiToken);
        }

        return 0;
    }
}
