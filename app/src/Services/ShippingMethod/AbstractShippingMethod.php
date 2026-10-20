<?php

namespace App\Services\ShippingMethod;

use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Sylius\Component\Resource\Factory\FactoryInterface;

abstract class AbstractShippingMethod implements ShippingMethodInterface
{
    public function __construct(
        private readonly FactoryInterface $addressFactory,
    ) {
    }

    public function chooseShippingMethod(
        Order $order,
        Shipment $shipment,
        ShippingMethod $shippingMethod,
        ChooseShipping $chooseShipping
    ): void {
        if (!empty($chooseShipping->getRelayId())) {
            $shipment->setRelayId($chooseShipping->getRelayId());
            if (!empty($chooseShipping->getRelayAddress())) {
                $relayAddress = $chooseShipping->getRelayAddress();

                /** @var Address $relayShippingAddress */
                $relayShippingAddress = $this->addressFactory->createNew();
                $relayShippingAddress->setFirstName($relayAddress['firstName']);
                // Since the relay point name is in both the firstName and the lastName,
                // the lastName is left empty to avoid duplication the name of the relay point
                $relayShippingAddress->setLastName('');
                $relayShippingAddress->setCity($relayAddress['city']);
                $relayShippingAddress->setStreet($relayAddress['street']);
                $relayShippingAddress->setCountryCode($relayAddress['countryCode']);
                $relayShippingAddress->setPostcode($relayAddress['postcode']);
                $relayShippingAddress->setCompany($relayAddress['company']);
                $relayShippingAddress->setDetails($relayAddress['details']);

                $order->setShippingAddress($relayShippingAddress);
            }
        }

        $this->handleShippingStockDate($order, $shipment, $shippingMethod);
    }

    abstract protected function handleShippingStockDate(
        Order $order,
        Shipment $shipment,
        ShippingMethod $shippingMethod
    ): void;
}
