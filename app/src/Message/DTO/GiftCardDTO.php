<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\GiftCard;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Setono\SyliusGiftCardPlugin\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class GiftCardDTO extends ArrayCollection
{
    /** @var int */
    protected $id;

    /** @var OrderItemUnitInterface|null */
    protected $orderItemUnit;

    /** @var CustomerInterface|null */
    protected $customer;

    /** @var Collection|OrderInterface[] */
    protected $appliedOrders;

    /** @var string|null */
    protected $code;

    /** @var int */
    protected $initialAmount;

    /** @var int */
    protected $amount = 0;

    /** @var string */
    protected $currencyCode;

    /** @var ChannelInterface */
    protected $channel;

    /** @var string */
    protected $emailBuyer;

    /** @var string */
    protected $emailRecipient;

    /** @var string */
    protected $nameBuyer;

    /** @var string */
    protected $nameRecipient;

    /** @var string */
    protected $message;

    public function __construct(GiftCard $giftCard)
    {
        $this->id = $giftCard->getId();

        if ($giftCard->getOrderItemUnit()) {
            $this->orderItemUnit = new OrderItemUnitDTO($giftCard->getOrderItemUnit());
        }

        $this->customer = $giftCard->getCustomer();
        $this->appliedOrders = $giftCard->getAppliedOrders();
        $this->code = $giftCard->getCode();
        $this->initialAmount = $giftCard->getInitialAmount();
        $this->amount = $giftCard->getAmount();
        $this->currencyCode = $giftCard->getCurrencyCode();
        $this->channel = $giftCard->getChannel();
        $this->emailBuyer = $giftCard->getEmailBuyer();
        $this->emailRecipient = $giftCard->getEmailRecipient();
        $this->nameBuyer = $giftCard->getNameBuyer();
        $this->nameRecipient = $giftCard->getNameRecipient();
        $this->message = $giftCard->getMessage();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderItemUnit(): ?OrderItemUnitInterface
    {
        return $this->orderItemUnit;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @return Collection|OrderInterface[]
     */
    public function getAppliedOrders()
    {
        return $this->appliedOrders;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getInitialAmount(): ?int
    {
        return $this->initialAmount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function getEmailBuyer(): ?string
    {
        return $this->emailBuyer;
    }

    public function getEmailRecipient(): ?string
    {
        return $this->emailRecipient;
    }

    public function getNameBuyer(): ?string
    {
        return $this->nameBuyer;
    }

    public function getNameRecipient(): ?string
    {
        return $this->nameRecipient;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
