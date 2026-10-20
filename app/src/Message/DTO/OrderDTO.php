<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Fastmag;
use App\Doctrine\Entity\FrontData;
use App\Doctrine\Entity\OrderOutOfStock;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\OrderVip;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Promotion\Model\PromotionCouponInterface as BaseCouponInterface;
use Sylius\Component\Promotion\Model\PromotionInterface as BasePromotionInterface;

final class OrderDTO extends ArrayCollection
{
    public $id;

    /** @var \DateTimeInterface|null */
    public $checkoutCompletedAt;

    /** @var string|null */
    public $number;

    /** @var string|null */
    public $notes;

    /** @var Collection|OrderItemDTO[] */
    public $items;

    /** @var int */
    public $itemsTotal = 0;

    /** @var Collection|AdjustmentInterface[] */
    public $adjustments;

    /** @var int */
    public $adjustmentsTotal = 0;

    /** @var int */
    public $total = 0;

    /** @var int */
    public $taxTotal;

    /** @var string */
    public $state;

    /** @var CustomerInterface */
    public $customer;

    /** @var ChannelInterface */
    public $channel;

    /** @var AddressInterface */
    public $shippingAddress;

    /** @var AddressInterface */
    public $billingAddress;

    /** @var Collection|BasePaymentInterface[] */
    public $payments;

    /** @var Collection|ShipmentInterface[] */
    public $shipments;

    /** @var string */
    public $currencyCode;

    /** @var string */
    public $localeCode;

    /** @var BaseCouponInterface */
    public $promotionCoupon;

    /** @var string */
    public $checkoutState;

    /** @var string */
    public $paymentState;

    /** @var string */
    public $shippingState;

    /** @var Collection|BasePromotionInterface[] */
    public $promotions;

    /** @var string */
    public $tokenValue;

    /** @var string */
    public $customerIp;

    /** @var int */
    public $bis = 0;

    public $parentNumber;

    /** @var int */
    public $return = 0;

    /** @var bool */
    public $erpForwarded = false;

    /** @var Fastmag */
    public $fastmag;

    /** @var int|null */
    public $NPS;

    /** @var bool */
    public $reviewEmailSent = false;

    /** @var OrderStore|null */
    public $orderStore;

    /** @var OrderReason[]|null */
    public $orderReason;

    /** @var OrderVip|null */
    public $orderVip;

    /** @var FrontData[]|null */
    public $frontData;

    /** @var OrderOutOfStock */
    public $orderOutOfStock;

    /** @var int */
    public $stockPriority = 0;

    /** @var Collection|GiftCardInterface[] */
    public $giftCards;

    /** @var ShippingMethodCategory[] */
    public array $shippingMethodCategories = [];

    public function __construct(Order $order)
    {
        $this->id = $order->getId();
        $this->checkoutCompletedAt = $order->getCheckoutCompletedAt();
        $this->number = $order->getNumber();
        $this->notes = $order->getNotes();

        $this->items = new ArrayCollection();
        foreach ($order->getItems() as $item) {
            $this->items->add(new OrderItemDTO($item, $order->getLocaleCode()));
        }

        $this->itemsTotal = $order->getItemsTotal();

        $this->adjustments = new ArrayCollection();
        foreach ($order->getAdjustments() as $adjustment) {
            $this->adjustments->add(new AdjustmentDTO($adjustment));
        }

        $this->adjustmentsTotal = $order->getAdjustmentsTotal();
        $this->total = $order->getTotal();
        $this->taxTotal = $order->getTaxTotal();
        $this->state = $order->getState();

        $this->customer = new CustomerDTO($order->getCustomer());

        $this->channel = $order->getChannel();

        $this->shippingAddress = new AddressDTO($order->getShippingAddress());
        $this->billingAddress = new AddressDTO($order->getBillingAddress());

        $this->payments = new ArrayCollection();
        foreach ($order->getPayments() as $payment) {
            $this->payments->add(new PaymentDTO($payment));
        }

        $this->shipments = new ArrayCollection();
        foreach ($order->getShipments() as $shipment) {
            $this->shipments->add(new ShipmentDTO($shipment));
        }

        $this->currencyCode = $order->getCurrencyCode();
        $this->localeCode = $order->getLocaleCode();
        $this->promotionCoupon = $order->getPromotionCoupon();
        $this->checkoutState = $order->getCheckoutState();
        $this->paymentState = $order->getPaymentState();
        $this->shippingState = $order->getShippingState();

        $this->promotions = new ArrayCollection();
        foreach ($order->getPromotions() as $promotion) {
            $this->promotions->add(new PromotionDTO($promotion));
        }

        $this->tokenValue = $order->getTokenValue();
        $this->customerIp = $order->getCustomerIp();
        $this->bis = $order->getBis();
        $this->parentNumber = $order->getParentNumber();
        $this->return = $order->getReturn();
        $this->erpForwarded = $order->getErpForwarded();
        $this->fastmag = $order->getFastmag();
        $this->NPS = $order->getNPS();
        $this->reviewEmailSent = $order->getReviewEmailSent();
        $this->orderStore = $order->getOrderStore();
        $this->orderReason = $order->getOrderReason();
        $this->orderVip = $order->getOrderVip();
        $this->orderOutOfStock = $order->getOrderOutOfStock();
        $this->stockPriority = $order->getStockPriority();

        $this->giftCards = new ArrayCollection();
        foreach ($order->getGiftCards() as $giftCard) {
            $this->giftCards->add(new GiftCardDTO($giftCard));
        }

        $this->shippingMethodCategories = $order->getShippingMethodCategories();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCheckoutCompletedAt(): ?\DateTimeInterface
    {
        return $this->checkoutCompletedAt;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    public function getItemsTotal(): int
    {
        return $this->itemsTotal;
    }

    /**
     * @return Collection|AdjustmentInterface[]
     */
    public function getAdjustments()
    {
        return $this->adjustments;
    }

    public function getAdjustmentsTotal(): int
    {
        return $this->adjustmentsTotal;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTaxTotal(): int
    {
        return $this->taxTotal;
    }

    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return ChannelInterface
     */
    public function getChannel()
    {
        return $this->channel;
    }

    public function getShippingAddress(): ?AddressDTO
    {
        return $this->shippingAddress;
    }

    public function getBillingAddress(): ?AddressDTO
    {
        return $this->billingAddress;
    }

    /**
     * @return Collection|BasePaymentInterface[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @return Collection|ShipmentInterface[]
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function getLocaleCode(): ?string
    {
        return $this->localeCode;
    }

    public function getPromotionCoupon(): ?BaseCouponInterface
    {
        return $this->promotionCoupon;
    }

    public function getCheckoutState(): ?string
    {
        return $this->checkoutState;
    }

    public function getPaymentState(): ?string
    {
        return $this->paymentState;
    }

    public function getShippingState(): ?string
    {
        return $this->shippingState;
    }

    /**
     * @return Collection|BasePromotionInterface[]
     */
    public function getPromotions()
    {
        return $this->promotions;
    }

    public function getTokenValue(): ?string
    {
        return $this->tokenValue;
    }

    public function getCustomerIp(): ?string
    {
        return $this->customerIp;
    }

    public function getBis(): int
    {
        return $this->bis;
    }

    public function getParentNumber()
    {
        return $this->parentNumber;
    }

    public function getReturn(): int
    {
        return $this->return;
    }

    public function isErpForwarded(): bool
    {
        return $this->erpForwarded;
    }

    public function getFastmag(): Fastmag
    {
        return $this->fastmag;
    }

    public function getNPS(): ?int
    {
        return $this->NPS;
    }

    public function isReviewEmailSent(): bool
    {
        return $this->reviewEmailSent;
    }

    public function getOrderStore(): ?OrderStore
    {
        return $this->orderStore;
    }

    /**
     * @return OrderReason[]|null
     */
    public function getOrderReason()
    {
        return $this->orderReason;
    }

    public function getOrderVip(): ?OrderVip
    {
        return $this->orderVip;
    }

    /**
     * @return FrontData[]|null
     */
    public function getFrontData(): ?array
    {
        return $this->frontData;
    }

    public function getOrderOutOfStock(): ?OrderOutOfStock
    {
        return $this->orderOutOfStock;
    }

    public function getStockPriority(): int
    {
        return $this->stockPriority;
    }

    /**
     * @return Collection|GiftCardInterface[]
     */
    public function getGiftCards()
    {
        return $this->giftCards;
    }

    /** @return ShippingMethodCategory[] */
    public function getShippingMethodCategories(): array
    {
        return $this->shippingMethodCategories;
    }
}
