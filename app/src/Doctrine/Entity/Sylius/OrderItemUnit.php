<?php

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitInterface as SetonoSyliusGiftCardOrderItemUnitInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitTrait as SetonoSyliusGiftCardOrderItemUnitTrait;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

#[Entity]
#[Table(name: 'sylius_order_item_unit')]
class OrderItemUnit extends BaseOrderItemUnit implements SetonoSyliusGiftCardOrderItemUnitInterface
{
    use SetonoSyliusGiftCardOrderItemUnitTrait;

    #[ORM\OneToOne(mappedBy: 'orderItemUnit', targetEntity: GiftCardInterface::class, cascade: ['persist', 'remove'])]
    protected ?GiftCardInterface $giftCard = null;
}
