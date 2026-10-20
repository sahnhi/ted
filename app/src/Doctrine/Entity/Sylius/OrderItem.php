<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\OrderItemProductCombo;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\ProductCombo;
use App\Doctrine\Repository\Sylius\OrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

/**
 * @method ProductVariantInterface|null getVariant()
 * @method Order                        getOrder()
 */
#[ORM\Table(name: 'sylius_order_item')]
#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem extends BaseOrderItem
{
    #[ORM\OneToMany(targetEntity: OrderItemProductCombo::class, mappedBy: 'orderItem', cascade: ['persist', 'remove'])]
    private ?Collection $orderItemProductCombo;

    #[ORM\OneToMany(targetEntity: OrderItemLogistic::class, mappedBy: 'orderItem', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?Collection $orderItemLogistics;

    public function __construct()
    {
        parent::__construct();
        $this->orderItemProductCombo = new ArrayCollection();
        $this->orderItemLogistics = new ArrayCollection();
    }

    public function getEcotaxTotal(): int
    {
        return $this->variant->getEcotax() * $this->getQuantity();
    }

    public function getDiscountAmount(): int
    {
        return $this->getUndiscountedPrice() - $this->getTotal();
    }

    public function getUndiscountedPrice(): int
    {
        return $this->getUnitPrice() * $this->getQuantity();
    }

    public function setItemTotal($amount): void
    {
        $this->unitPrice = $amount;

        $this->recalculateUnitsTotal();
    }

    public function addOrderItemProductCombo(OrderItemProductCombo $orderItemProductCombo): void
    {
        $this->orderItemProductCombo[] = $orderItemProductCombo;
    }

    public function removeOrderItemProductCombo(OrderItemProductCombo $orderItemProductCombo): void
    {
        $this->orderItemProductCombo->removeElement($orderItemProductCombo);
    }

    public function getOrderReason(): ?OrderReason
    {
        /** @var ProductCombo $combo */
        $combo = $this->getOrderItemProductCombo()->first() ? $this->getOrderItemProductCombo()->first()->getProductCombo() : null;
        $variant = $combo ? $combo->getProductVariant() : $this->variant;

        return $this->order->getOrderReasonByVariant($variant->getCode());
    }

    public function getOrderItemProductCombo(): ?Collection
    {
        return $this->orderItemProductCombo;
    }

    public function getOrderItemLogistics(): Collection
    {
        return $this->orderItemLogistics;
    }

    public function addOrderItemLogistic(OrderItemLogistic $orderItemLogistic): void
    {
        $this->orderItemLogistics[] = $orderItemLogistic;
    }

    public function removeOrderItemLogistic(OrderItemLogistic $orderItemLogistic): void
    {
        $this->orderItemLogistics->removeElement($orderItemLogistic);
    }

    public function containsLogisticVariant(string $logisticCode): bool
    {
        if ($this->variant->getCode() === $logisticCode) {
            return true;
        }

        $logisticItems = $this->orderItemLogistics->filter(fn (OrderItemLogistic $orderItemLogistic): bool => $orderItemLogistic->getProductVariantLogistic()->getCode() === $logisticCode);

        return !$logisticItems->isEmpty();
    }

    public function getDeepestDecomposition(): ?array
    {
        if ($this->getOrderItemProductCombo()) {
            $variantCombo = [];
            foreach ($this->getOrderItemProductCombo() as $combo) {
                $comboVariant = $combo->getProductCombo()->getProductVariantLogistic() ?? $combo->getProductCombo()->getProductVariant();
                $variantCombo[] = $comboVariant->getCode();
            }

            return $variantCombo;
        }

        return null;
    }

    public function getOrderItemLogisticsDisplayable(): array
    {
        $items = [];
        /** @var ProductVariant $variant */
        $variant = $this->getVariant();
        /** @var OrderItemLogistic $orderItemLogistic */
        foreach ($this->getOrderItemLogistics() as $orderItemLogistic) {
            $productVariantCode = $orderItemLogistic->getProductVariantLogistic()->getCode();
            if (array_key_exists($productVariantCode, $items)) {
                ++$items[$productVariantCode]['quantity'];
                if (!str_contains((string) $items[$productVariantCode]['batch'], (string) $orderItemLogistic->getBatchNumber())) {
                    $items[$productVariantCode]['batch'] .= ', ' . $orderItemLogistic->getBatchNumber();
                }
                continue;
            }
            $items[$productVariantCode]['quantity'] = 1;
            $items[$productVariantCode]['batch'] = $orderItemLogistic->getBatchNumber();
            if ($variant->isBundle()) {
                $bundleAssociation = $variant->getBundleAssociation($productVariantCode);
                $unitPrice = $bundleAssociation->getPriceAtDate($this->getOrder()->getCreatedAt());
                $discountedPrice = $this->getAdjustmentsTotalRecursively() / $variant->getBundleAssociations()->count();
                $items[$productVariantCode]['unitPrice'] = $unitPrice;
                $items[$productVariantCode]['unitDiscount'] = $discountedPrice;
            }
        }

        return $items;
    }

    public function getTotalWithoutAdjustments(): int
    {
        return $this->getTotal() - $this->getAdjustmentsTotalRecursively();
    }
}
