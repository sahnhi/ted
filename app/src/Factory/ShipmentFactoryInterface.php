<?php

declare(strict_types=1);

namespace App\Factory;

use App\Doctrine\Entity\Sylius\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface ShipmentFactoryInterface extends FactoryInterface
{
    public function createFromExisting(ShipmentInterface $shipment): ShipmentInterface;
}
