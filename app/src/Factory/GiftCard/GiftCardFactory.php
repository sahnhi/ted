<?php

namespace App\Factory\GiftCard;

use App\Doctrine\Entity\Sylius\GiftCard;
use App\Model\GiftCardAddress;
use Setono\SyliusGiftCardPlugin\Factory\GiftCardFactory as BaseGiftCardFactory;
use Setono\SyliusGiftCardPlugin\Factory\GiftCardFactoryInterface;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ChannelInterface;

final class GiftCardFactory implements GiftCardFactoryInterface
{
    public function __construct(
        private readonly BaseGiftCardFactory $decoratedFactory
    ) {
    }

    public function createNew(): GiftCardInterface
    {
        return $this->decoratedFactory->createNew();
    }

    public function createForChannel(ChannelInterface $channel): GiftCardInterface
    {
        return $this->decoratedFactory->createForChannel($channel);
    }

    public function createFromOrderItemUnit(OrderItemUnitInterface $orderItemUnit): GiftCardInterface
    {
        return $this->decoratedFactory->createFromOrderItemUnit($orderItemUnit);
    }

    public function createFromOrderAndGiftCardAddress(OrderItemUnitInterface $orderItemUnit, GiftCardAddress $giftCardAddress)
    {
        /** @var GiftCard $giftCard */
        $giftCard = $this->decoratedFactory->createFromOrderItemUnit($orderItemUnit);

        $giftCard->setEmailBuyer($giftCardAddress->emailBuyer());
        $giftCard->setEmailRecipient($giftCardAddress->emailRecipient());
        $giftCard->setNameBuyer($giftCardAddress->nameBuyer());
        $giftCard->setNameRecipient($giftCardAddress->nameRecipient());
        $giftCard->setMessage($giftCardAddress->message());

        return $giftCard;
    }
}
