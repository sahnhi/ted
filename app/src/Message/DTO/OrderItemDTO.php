<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\Sylius\OrderItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemUnitInterface;

final class OrderItemDTO extends ArrayCollection
{
    public $id;
    /** @var OrderInterface|null */
    public $order;
    /** @var int */
    public $quantity = 0;
    /** @var int */
    public $unitPrice = 0;
    /** @var int */
    public $total = 0;
    /** @var bool */
    public $immutable = false;
    /** @var Collection|OrderItemUnitInterface[] */
    public $units;
    /** @var int */
    public $unitsTotal = 0;
    /** @var Collection|AdjustmentInterface[] */
    public $adjustments;
    /** @var int */
    public $adjustmentsTotal = 0;
    /** @var ProductVariantInterface */
    public $variant;
    /** @var string */
    public $productName;
    /** @var string */
    public $variantName;

    public $orderItemProductCombo;
    /** @var string|null */
    public $productCode;
    /** @var string|null */
    public $variantCode;
    /** @var array */
    private $variantCompositions;

    public function __construct(
        OrderItem $item,
        private readonly ?string $local = null
    ) {
        $this->id = $item->getId();
        $this->order = null;
        $this->quantity = $item->getQuantity();
        $this->unitPrice = $item->getUnitPrice();
        $this->total = $item->getTotal();
        $this->immutable = $item->isImmutable();
        $this->units = null;
        $this->adjustments = $item->getAdjustments();
        $this->adjustmentsTotal = $item->getAdjustmentsTotal();
        $this->variant = $item->getVariant();
        $this->productName = $item->getProductName();
        $this->variantName = $item->getVariantName();
        $this->orderItemProductCombo = $item->getOrderItemProductCombo();
        $this->productCode = $item->getProduct()->getCode();
        $this->variantCode = $item->getVariant()->getCode();
        $this->variantCompositions = $this->createVariantCompositions();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    /**
     * @return Collection|OrderItemUnitInterface[]
     */
    public function getUnits()
    {
        return $this->units;
    }

    public function getUnitsTotal(): int
    {
        return $this->unitsTotal;
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

    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function getVariantName(): ?string
    {
        return $this->variantName;
    }

    public function getOrderItemProductCombo(): ArrayCollection
    {
        return $this->orderItemProductCombo;
    }

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function setProductCode(?string $productCode): OrderItemDTO
    {
        $this->productCode = $productCode;

        return $this;
    }

    public function getVariantCode(): ?string
    {
        return $this->variantCode;
    }

    public function setVariantCode(?string $variantCode): OrderItemDTO
    {
        $this->variantCode = $variantCode;

        return $this;
    }

    // todo: à refactorer orderItemDTO pour envoyer que les informations dont le mailer a besoin et pas le tout (order, product, variant ...)
    public function createVariantCompositions(): array
    {
        $variants = [];

        if (null === $this->local) {
            return $variants;
        }

        foreach ($this->variant->getBundleAssociations() as $variantBundle) {
            /** @var BundleAssociation $variantBundle */
            $productName = $variantBundle->getVariant()->getProduct()->getTranslation($this->local)->getName();
            $optionNames = [];
            foreach ($variantBundle->getVariant()->getOptionValues() as $option) {
                $optionNames[] = $option->getTranslation($this->local)->getValue();
            }

            $variants[] = new VariantDTO($productName, $optionNames, $variantBundle->getPrice(), $variantBundle->getQuantity());
        }

        return $variants;
    }

    public function getVariantCompositions(): array
    {
        return $this->variantCompositions;
    }
}
