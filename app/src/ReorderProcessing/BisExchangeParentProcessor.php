<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Factory\ShipmentFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Webmozart\Assert\Assert;

final class BisExchangeParentProcessor implements ReorderProcessor
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ShipmentFactoryInterface $shipmentFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Order $order
     * @param Order $reorder
     */
    public function process(BaseOrderInterface $order, BaseOrderInterface $reorder): void
    {
        $realParent = $this->orderRepository->findInitialOrder($order->getParentNumber());
        Assert::notNull($realParent, 'Initial order cannot be found');

        Assert::notNull($realParent->getNumber(), 'Initial order number cannot be null');
        $reorder->setParentNumber($realParent->getNumber());

        $this->duplicateShipment($order, $reorder);
    }

    private function duplicateShipment(BaseOrderInterface $order, BaseOrderInterface $reorder): void
    {
        $reorder->getShipments()->clear();

        $firstShipment = $order->getShipments()->first();
        Assert::isInstanceOf($firstShipment, ShipmentInterface::class, 'Exchange and bis must have a shipment');

        $newShipment = $this->shipmentFactory->createFromExisting($firstShipment);
        foreach ($reorder->getItemUnits() as $itemUnit) {
            $newShipment->addUnit($itemUnit);
        }

        $this->entityManager->persist($newShipment);

        $reorder->addShipment($newShipment);
    }
}
