<?php

namespace App\Doctrine\Entity\Sylius;

use App\Api\Odoo\Model\OrderInterface as OdooOrder;
use App\Doctrine\Entity\Fastmag;
use App\Doctrine\Entity\FrontData;
use App\Doctrine\Entity\OrderOutOfStock;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\OrderReturn;
use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\OrderVip;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\OrderInterface as AppOrderInterface;
use App\Doctrine\Entity\Trait\Order\OrderMessagesTrait;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Enum\AdjustmentTypeEnum;
use App\Promotion\Checker\LingotRuleChecker;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderInterface as SetonoSyliusGiftCardPluginOrderInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderTrait as SetonoSyliusGiftCardPluginOrderTrait;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Promotion\Model\PromotionCouponInterface;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Webmozart\Assert\Assert;

/**
 * @method CustomerInterface|null getCustomer()
 * @method Collection<OrderItem>  getItems()
 */
#[ORM\Table(name: 'sylius_order')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Index(columns: ['checkout_completed_at'])]
#[ORM\Index(columns: ['state'])]
#[ORM\Index(columns: ['odoo_state'])]
#[ORM\Index(columns: ['parent_number', 'updated_at'])]
#[ORM\Index(columns: ['number', 'updated_at'])]
#[ORM\Index(columns: ['created_at'])]
class Order extends BaseOrder implements SetonoSyliusGiftCardPluginOrderInterface, AppOrderInterface, \Stringable
{
    use SetonoSyliusGiftCardPluginOrderTrait {
        SetonoSyliusGiftCardPluginOrderTrait::__construct as private __giftCardTraitConstruct;
    }

    use OrderMessagesTrait;

    #[ORM\JoinTable(name: 'setono_sylius_gift_card__order_gift_cards')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'gift_card_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: GiftCardInterface::class, inversedBy: 'appliedOrders')]
    protected Collection $giftCards;

    public const REVIEW_TYPE_MANDRILL = 'mandrill';
    public const REVIEW_TYPE_TRUST_PILOT = 'trust_pilot';
    public const REVIEW_TYPE_VERIFIED_REVIEWS = 'verified_reviews';

    #[ORM\Column(name: 'bis', type: 'integer')]
    private int $bis = 0;

    #[ORM\Column(name: 'parent_number', type: 'string', nullable: true)]
    private ?string $parentNumber = null;

    #[ORM\Column(name: 'return_number', type: 'integer')]
    private int $return = 0;

    #[ORM\Column(name: 'erp_forwarded', type: 'boolean')]
    private bool $erpForwarded = false;

    #[ORM\OneToOne(mappedBy: 'order', targetEntity: Fastmag::class, cascade: ['persist', 'remove'])]
    private ?Fastmag $fastmag = null;

    #[ORM\Column(name: 'NPS', type: 'integer', nullable: true)]
    private ?int $NPS = null;

    #[ORM\Column(name: 'review_email_sent', type: 'boolean')]
    private bool $reviewEmailSent = false;

    #[ORM\OneToOne(mappedBy: 'order', targetEntity: OrderStore::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?OrderStore $orderStore = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderReason::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $orderReason;

    #[ORM\OneToOne(mappedBy: 'order', targetEntity: OrderVip::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?OrderVip $orderVip = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: FrontData::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private ?Collection $frontData;

    #[ORM\OneToOne(mappedBy: 'order', targetEntity: OrderOutOfStock::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private ?OrderOutOfStock $orderOutOfStock = null;

    #[ORM\Column(name: 'stock_priority', type: 'integer', nullable: false)]
    private int $stockPriority = 0;

    #[ORM\OneToOne(mappedBy: 'order', targetEntity: OrderReturn::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private ?OrderReturn $orderReturn = null;

    #[ORM\Column(name: 'younited_activated', type: 'boolean')]
    private bool $younitedActivated = false;

    #[ORM\Column(name: 'review_type', type: 'string', length: 20, nullable: true)]
    private ?string $reviewType = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: \App\Doctrine\Entity\Invoice::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private ?Collection $invoice = null;

    #[ORM\Column(name: 'odoo_sent', type: 'integer')]
    private int $odooSent = OdooOrder::LEGACY_ODOO_STATE_READY;

    #[ORM\Column(name: 'odoo_state', type: 'string', options: ['default' => OdooOrder::ODOO_STATE_READY])]
    private ?string $odooState = OdooOrder::ODOO_STATE_READY;

    public function __construct()
    {
        $this->__giftCardTraitConstruct();

        parent::__construct();
        $this->orderReason = new ArrayCollection();
        $this->frontData = new ArrayCollection();
        $this->giftCards = new ArrayCollection();
    }

    public function isOdooSent(): int
    {
        return $this->odooSent;
    }

    public function __toString(): string
    {
        return null === $this->getNumber() || '' === $this->getNumber() || '0' === $this->getNumber() ? '' : $this->getNumber();
    }

    public function getBis(): int
    {
        return $this->bis;
    }

    public function setBis(int $bis): self
    {
        $this->bis = $bis;

        return $this;
    }

    public function getParentNumber(): ?string
    {
        return $this->parentNumber;
    }

    public function setParentNumber(?string $parentNumber): static
    {
        $this->parentNumber = $parentNumber;

        return $this;
    }

    public function setTotal($total): static
    {
        $this->total = $total;

        return $this;
    }

    public function removePayments(): static
    {
        $this->payments->clear();

        return $this;
    }

    public function getErpForwarded(): bool
    {
        return $this->erpForwarded;
    }

    public function setErpForwarded(bool $erpForwarded): void
    {
        $this->erpForwarded = $erpForwarded;
    }

    public function getFastmag(): ?Fastmag
    {
        return $this->fastmag;
    }

    public function setFastmag(Fastmag $fastmag): void
    {
        $this->fastmag = $fastmag;
    }

    public function getNPS(): ?int
    {
        return $this->NPS;
    }

    public function setNPS(?int $NPS = null): static
    {
        $this->NPS = $NPS;

        return $this;
    }

    public function getReviewEmailSent(): bool
    {
        return $this->reviewEmailSent;
    }

    public function setReviewEmailSent(bool $reviewEmailSent): void
    {
        $this->reviewEmailSent = $reviewEmailSent;
    }

    public function getOrderStore(): ?OrderStore
    {
        return $this->orderStore;
    }

    public function setOrderStore(?OrderStore $orderStore): void
    {
        $this->orderStore = $orderStore;
    }

    public function getEcotaxTotal(): int
    {
        $ecotaxTotal = 0;

        /** @var OrderItem $item */
        foreach ($this->items as $item) {
            $variant = $item->getVariant();

            foreach ($variant->getProductVariantEcotax() as $ecotax) {
                $ecotaxTotal += $ecotax->getEcotax()->getAmountTTC() * $ecotax->getQuantity();
            }
        }

        return $ecotaxTotal;
    }

    public function setItemsTotal($rate): void
    {
        foreach ($this->items as $item) {
            $amount = (int) round($item->getUnitPrice() / (1 + $rate));

            $item->setItemTotal($amount);
        }
    }

    public function getOrderReason(): ?Collection
    {
        return $this->orderReason;
    }

    public function addOrderReason(OrderReason $orderReason): void
    {
        if (!$this->orderReason->contains($orderReason)) {
            $this->orderReason[] = $orderReason;
            $orderReason->setOrder($this);
        }
    }

    public function removeOrderReason(OrderReason $orderReason): void
    {
        $this->orderReason->removeElement($orderReason);
    }

    public function getFrontData(): ?Collection
    {
        return $this->frontData;
    }

    public function addFrontData(FrontData $frontData): void
    {
        if (!$this->frontData->contains($frontData)) {
            $frontData->setOrder($this);
            $this->frontData[] = $frontData;
        }
    }

    public function removeFrontData(OrderReason $frontData): void
    {
        $this->frontData->removeElement($frontData);
    }

    public function getReturn(): int
    {
        return $this->return;
    }

    public function setReturn(int $return): void
    {
        $this->return = $return;
    }

    public function getOrderOutOfStock(): ?OrderOutOfStock
    {
        return $this->orderOutOfStock;
    }

    public function setOrderOutOfStock(?OrderOutOfStock $orderOutOfStock): void
    {
        $this->orderOutOfStock = $orderOutOfStock;
    }

    public function getOrderVip(): ?OrderVip
    {
        return $this->orderVip;
    }

    public function setOrderVip(?OrderVip $orderVip): void
    {
        $this->orderVip = $orderVip;
    }

    public function getStockPriority(): int
    {
        return $this->stockPriority;
    }

    public function setStockPriority(int $stockPriority): void
    {
        $this->stockPriority = $stockPriority;
    }

    public function getGiftCards(): Collection
    {
        return $this->giftCards;
    }

    public function hasGiftCards(): bool
    {
        return !$this->giftCards->isEmpty();
    }

    public function addGiftCard(GiftCardInterface $giftCard): void
    {
        assert($this instanceof AppOrderInterface);

        if (!$this->hasGiftCard($giftCard)) {
            $this->giftCards->add($giftCard);
            $giftCard->addAppliedOrder($this);
        }
    }

    public function hasGiftCard(GiftCardInterface $giftCard): bool
    {
        return $this->giftCards->contains($giftCard);
    }

    public function removeGiftCard(GiftCardInterface $giftCard): void
    {
        assert($this instanceof AppOrderInterface);

        if ($this->hasGiftCard($giftCard)) {
            $this->giftCards->removeElement($giftCard);
            $giftCard->removeAppliedOrder($this);
        }
    }

    public function isYounitedActivated(): bool
    {
        return $this->younitedActivated;
    }

    public function setYounitedActivated(bool $younitedActivated): void
    {
        $this->younitedActivated = $younitedActivated;
    }

    public function getLastPaymentByStateOrReturnLast(?string $state = null): ?PaymentInterface
    {
        if ($this->payments->isEmpty()) {
            return null;
        }

        $payment = $this->payments->filter(fn (BasePaymentInterface $payment): bool => $payment->getState() === $state)->last();

        if (false === $payment) {
            return $this->payments->last();
        }

        return $payment;
    }

    public function getOrderReturn(): ?OrderReturn
    {
        return $this->orderReturn;
    }

    public function setOrderReturn(?OrderReturn $orderReturn): void
    {
        $this->orderReturn = $orderReturn;
    }

    public function isFromChannel(string $channelCode): bool
    {
        return $this->getChannel()->getCode() === $channelCode;
    }

    public function getHighestPayment(string $state = OrderPaymentStates::STATE_PAID): ?BasePaymentInterface
    {
        $highestPayment = null;

        /** @var BasePaymentInterface $payment */
        foreach ($this->getPayments() as $payment) {
            if (null === $highestPayment || ($payment->getAmount() > $highestPayment->getAmount()
                    && $payment->getState() === $state)) {
                $highestPayment = $payment;
            }
        }

        return $highestPayment;
    }

    public function getOrderReasonByVariant(string $code): ?OrderReason
    {
        $reason = null;

        $reasons = $this->orderReason->filter(function (OrderReason $reason) use ($code): bool {
            if (null === $reason->getVariant()) {
                return false;
            }

            if ($reason->getVariant()->getCode() === $code) {
                return true;
            }

            return str_contains((string) $reason->getVariant()->getCode(), $code);
        });

        // Check Order Reason
        if (!$reasons->first()) {
            if ($this->orderReason->first()) {
                $reason = $this->orderReason->first() ?: null;
            }
        } else {
            $reason = $reasons->first() ?: null;
        }

        return $reason;
    }

    public function getReviewType(): ?string
    {
        return $this->reviewType;
    }

    public function setReviewType(?string $reviewType): Order
    {
        $this->reviewType = $reviewType;

        return $this;
    }

    public function getWarrantyDays(): array
    {
        $warrantyDays = [];

        if ($this->hasShipments()) {
            foreach ($this->getShipments() as $key_shipment => $shipment) {
                if ($shipment->getEnddeliverySlot()) {
                    $trialPeriodDays = \DateTime::createFromFormat('Y-m-d H:i:s', $shipment->getEndDeliverySlot()->format('Y-m-d H:i:s'));
                    $trialPeriodDays->add(new \DateInterval('P100D'));
                    $now = new \DateTime();
                    $days = $now->diff($trialPeriodDays);

                    $warrantyDays[$key_shipment]['days'] = $days->format('%r%a');
                }
            }
        }

        return $warrantyDays;
    }

    public function getLingotPromotionMessage(): ?string
    {
        $message = null;

        if ($this->getPromotionCoupon() instanceof PromotionCouponInterface) {
            $promotion = $this->getPromotionCoupon()->getPromotion();

            $rule = $promotion->getRules()->filter(fn (PromotionRuleInterface $rule): bool => LingotRuleChecker::TYPE === $rule->getType())->first() ?: null;

            if (null !== $rule) {
                $message = $rule->getConfiguration()['message'] ?? $message;
            }
        }

        return $message;
    }

    public function getInvoice(): ?Collection
    {
        return $this->invoice;
    }

    public function setInvoice(Collection $invoice): Order
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getOrderItemLogistics(): array
    {
        $items = [];

        /** @var OrderItem $item */
        foreach ($this->items as $item) {
            foreach ($item->getOrderItemLogistics() as $orderItemLogistic) {
                $items[] = $orderItemLogistic;
            }
        }

        return $items;
    }

    public function findOrderItemByLogisticVariant(string $logisticCode): ?OrderItemInterface
    {
        $items = $this->items->filter(fn (OrderItem $item): bool => $item->containsLogisticVariant($logisticCode));

        return $items->first() ?: null;
    }

    public function getReturnType($human = false): string|int|null
    {
        if (!$this->getChannel() instanceof ChannelInterface) {
            return null;
        }
        if (ChannelCode::CHANNEL_RETURN !== $this->getChannel()->getCode()) {
            return null;
        }
        foreach ($this->getOrderReason() as $orderReason) {
            Assert::notNull($orderReason->getReason(), 'Reason cannot be null.');

            if ($human) {
                return ReturnTypeServiceInterface::REASON_RETURN_TYPE_MATCHES[$orderReason->getReason()->getReturnType()];
            }

            return $orderReason->getReason()->getReturnType();
        }

        return null;
    }

    public function getItemsByVariant(ProductVariantInterface $variant): Collection
    {
        return $this->getItems()->filter(fn (OrderItem $item): bool => $variant === $item->getVariant());
    }

    /**
     * @return Collection<OrderItem>
     */
    public function getPromotionableItems(): Collection
    {
        /** @var Collection<OrderItem> $filteredItems */
        $filteredItems = $this->items->filter(function (OrderItem $orderItem): bool {
            /** @var ProductVariantInterface $variant */
            $variant = $orderItem->getVariant();

            return false === $variant->getExcludeFromPromotions();
        });

        return $filteredItems;
    }

    public function getPromotionSubjectTotal(): int
    {
        $total = 0;

        /** @var OrderItemInterface $item */
        foreach ($this->getPromotionableItems() as $item) {
            $total += $item->getTotal();
        }

        return $total;
    }

    public function getItemsCombo(): array
    {
        $variantCombo = [];
        /** @var OrderItem $item */
        foreach ($this->getItems() as $item) {
            if ($item->getDeepestDecomposition()) {
                $variantCombo = array_merge($variantCombo, $item->getDeepestDecomposition());
            }
        }

        return $variantCombo;
    }

    public function getOdooState(): ?string
    {
        return $this->odooState;
    }

    public function setOdooState(?string $odooState): self
    {
        $this->odooState = $odooState;

        return $this;
    }

    public function isLocked(): bool
    {
        return in_array($this->getState(), [OrderInterface::STATE_NEW, OrderInterface::STATE_FULFILLED]) && OrderCheckoutStates::STATE_COMPLETED === $this->getCheckoutState() && in_array($this->getPaymentState(), [OrderPaymentStates::STATE_PAID, OrderPaymentStates::STATE_REFUNDED]);
    }

    /**
     * @return array<array-key, ShippingMethodCategory>
     */
    public function getShippingMethodCategories(): array
    {
        $shippingMethodCategories = [];
        /** @var Shipment $shipment */
        foreach ($this->getShipments() as $shipment) {
            $shippingMethodCategory = $shipment->getShippingMethodCategory();
            if (null === $shippingMethodCategory) {
                continue;
            }

            $shippingMethodCategories[$shippingMethodCategory->getCode()] = $shippingMethodCategory;
        }

        return $shippingMethodCategories;
    }

    public function getCrossSellProducts(): array
    {
        $crossSellProducts = [];

        foreach ($this->getItems() as $item) {
            /**
             * @var ProductInterface $product
             */
            $product = $item->getVariant()->getProduct();
            $crossSellProducts = array_merge($crossSellProducts, $product->getCrossSellProducts());
        }

        foreach ($this->getItems() as $item) {
            $crossSellProducts = array_filter($crossSellProducts, fn ($crossSellProduct): bool => $item->getProduct()->getCode() !== $crossSellProduct);
        }

        return array_values(array_unique($crossSellProducts));
    }

    public function getCoupon(): array|null
    {
        $promotion = $this->getPromotionCoupon()?->getPromotion();
        if (null === $promotion) {
            return null;
        }

        return [$promotion->getName() => $promotion->getActions()];
    }

    public function getCustomerEmail(): string|null
    {
        return $this->getCustomer()?->getEmail();
    }

    public function getPromotionCouponCode(): string|null
    {
        return $this->getPromotionCoupon()?->getCode();
    }

    /**
     * @return Collection<OrderItem>
     */
    public function getServiceItems(): Collection
    {
        return $this->getItems()
            ->filter(static fn (OrderItem $item): bool => !$item->getAdjustments(AdjustmentTypeEnum::SHIPPING_METHOD_SERVICE->value)->isEmpty());
    }
}
