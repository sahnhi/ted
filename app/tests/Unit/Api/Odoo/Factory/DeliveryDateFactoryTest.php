<?php

namespace App\Tests\Unit\Api\Odoo\Factory;

use App\Api\Odoo\Factory\DeliveryDateFactory;
use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Tests\TestCase;

class DeliveryDateFactoryTest extends TestCase
{
    public const TEMPORARY_PHP_TIME_FIX = 2;

    /** @var DeliveryDateFactory */
    private $factory;

    /** @var ShipmentInterface */
    private $shipment;

    public function setUpFactory()
    {
        $this->factory = new DeliveryDateFactory(self::TEMPORARY_PHP_TIME_FIX);
    }

    private function setUpCreateNew()
    {
        $this->shipment = new Shipment();
        $this->shipment->setStartDeliverySlot(new \DateTime());
        $this->shipment->setEndDeliverySlot(new \DateTime());

        $this->setUpFactory();
    }

    public function testCreateNew(): void
    {
        $this->setUpCreateNew();
        $deliveryDate = $this->factory->createNew($this->shipment);

        $this->assertInstanceOf(DeliveryDateInterface::class, $deliveryDate);
    }
}
