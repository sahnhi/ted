<?php

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Shipping\Model\ShippingMethodTranslation as BaseShippingMethodTranslation;
use Sylius\Component\Shipping\Model\ShippingMethodTranslationInterface;

#[ORM\Table(name: 'sylius_shipping_method_translation')]
#[ORM\Entity]
class ShippingMethodTranslation extends BaseShippingMethodTranslation implements ShippingMethodTranslationInterface
{
    #[ORM\Column(name: 'delivery_conditions', type: 'text', nullable: true)]
    private ?string $deliveryConditions = null;

    public function getDeliveryConditions(): ?string
    {
        return $this->deliveryConditions;
    }

    public function setDeliveryConditions(string $deliveryConditions): ShippingMethodTranslation
    {
        $this->deliveryConditions = $deliveryConditions;

        return $this;
    }
}
