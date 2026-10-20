<?php

declare(strict_types=1);

namespace App\Model;

use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\ShipmentInterface;

class ExportAccountModel
{
    private string $orderNumber;

    private string $orderCheckoutCompletedAt;

    private string $channelName;

    private ?string $orderShipmentDate;

    private ?string $orderDeliveredDate;

    private float $orderTotalTTC;

    private float $orderTotalHT;

    public function __construct(OrderInterface $order)
    {
        $this->setOrderNumber($order);
        $this->setOrderCheckoutCompletedAt($order);
        $this->setChannelName($order);
        $this->setOrderShipmentDate($order);
        $this->setOrderDeliveredDate($order);
        $this->setOrderTotalTTC($order);
        $this->setOrderTotalHT($order);
    }

    public function setOrderNumber(OrderInterface $order): void
    {
        $this->orderNumber = $order->getNumber();
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderCheckoutCompletedAt(OrderInterface $order): void
    {
        $this->orderCheckoutCompletedAt = $order->getCheckoutCompletedAt()->format('d-m-Y');
    }

    public function getOrderCheckoutCompletedAt(): string
    {
        return $this->orderCheckoutCompletedAt;
    }

    public function setChannelName(OrderInterface $order): void
    {
        $this->channelName = $order->getChannel()->getName();
    }

    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function setOrderShipmentDate(OrderInterface $order): void
    {
        if ($order->getShipments()->isEmpty()) {
            return;
        }
        /** @var ShipmentInterface $shipment */
        $shipment = $order->getShipments()->first();
        $shippingDate = $shipment->getShippingDate()?->format('d-m-Y');

        if ($shippingDate < $order->getCheckoutCompletedAt()) {
            $shippingDate = $order->getCheckoutCompletedAt()?->format('d-m-Y');
        }
        $this->orderShipmentDate = $shippingDate;
    }

    public function getOrderShipmentDate(): string
    {
        return $this->orderShipmentDate;
    }

    public function setOrderDeliveredDate(OrderInterface $order): void
    {
        if ($order->getShipments()->isEmpty()) {
            return;
        }

        $this->orderDeliveredDate = $order->getShipments()->first()->getShippedAt()?->format('d-m-Y');
    }

    public function getOrderDeliveredDate(): ?string
    {
        return $this->orderDeliveredDate;
    }

    public function setOrderTotalTTC(OrderInterface $order): void
    {
        $this->orderTotalTTC = $order->getTotal() / 100;
    }

    public function getOrderTotalTTC(): string
    {
        return number_format($this->orderTotalTTC, 2, ',', ' ');
    }

    public function setOrderTotalHT(OrderInterface $order): void
    {
        $totalGiftCard = 0;

        $this->orderTotalHT = (($order->getTotal() + abs($totalGiftCard)) - $order->getTaxTotal()) / 100;
    }

    public function getOrderTotalHT(): string
    {
        return number_format($this->orderTotalHT, 2, ',', ' ');
    }
}
