<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Doctrine\Entity\ExportAccountingData;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Model\ExportAccountModel;
use Symfony\Component\Serializer\SerializerInterface;

class ExportAccountingDataExporter
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function fetchDataByDateRange(ExportAccountingData $exportAccountingData): string
    {
        $startDate = $exportAccountingData->getStartDate();

        // Add 1 day to the end date to include the last day
        $endDate = $exportAccountingData->getEndDate()->modify('+1 day midnight');

        if (ExportAccountingData::SCOPE_CHECKOUT === $exportAccountingData->getScope()) {
            $orders = $this->orderRepository->findIdsByDateRangeByCheckoutOrders($startDate, $endDate);
        } elseif (ExportAccountingData::SCOPE_DELIVERED === $exportAccountingData->getScope()) {
            $orders = $this->orderRepository->findIdsByDateRangeByShippedOrders($startDate, $endDate);
        }

        if (empty($orders)) {
            throw new \RuntimeException('No orders found');
        }

        $exportOrders = [];
        foreach ($orders as $key => $orderId) {
            $order = $this->orderRepository->find($orderId);

            if (!$order instanceof OrderInterface) {
                continue;
            }

            $exportAccountModel = new ExportAccountModel($order);
            $exportOrders[] = $exportAccountModel;
            unset($order, $orders[$key]);
        }

        return $this->serializer->serialize($exportOrders, 'csv');
    }
}
