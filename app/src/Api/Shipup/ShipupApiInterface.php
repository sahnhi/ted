<?php

namespace App\Api\Shipup;

use App\Api\Shipup\DTO\ShipupMessageInterface;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface ShipupApiInterface
{
    public const SHIPUP_ORDER_PATH = '/v2/orders';
    public const SHIPUP_AVAILABLE_METHODS = [
        ShippingMethodInterface::METHOD_EDELIV,
        ShippingMethodInterface::METHOD_DHL,
        ShippingMethodInterface::METHOD_DPD,
        ShippingMethodInterface::METHOD_POSTE,
        ShippingMethodInterface::METHOD_FRANCE_EXPRESS,
        ShippingMethodInterface::METHOD_VIR,
        ShippingMethodInterface::METHOD_CHRONOPOST,
    ];

    public function request(ShipupMessageInterface $subject, string $path, string $method = 'post'): void;

    public function dispatchShipupMessage(ShipupMessageInterface $subject, string $path, string $method = 'post'): void;

    public function order(OrderInterface $order): void;

    public function tracker(ShipmentInterface $shipment): void;
}
