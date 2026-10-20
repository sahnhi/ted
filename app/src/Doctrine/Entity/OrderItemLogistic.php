<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_order_item_logistic')]
#[ORM\Entity]
class OrderItemLogistic implements ResourceInterface, \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'batch_number', type: 'string', nullable: true)]
    private ?string $batchNumber = null;

    #[ORM\OneToOne(targetEntity: Quality::class, mappedBy: 'orderItemLogistic')]
    private ?Quality $quality = null;

    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: OrderItem::class, inversedBy: 'orderItemLogistic')]
    private ?OrderItem $orderItem = null;

    #[ORM\JoinColumn(name: 'productVariantLogistic_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    private ?ProductVariant $productVariantLogistic = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuality(): ?Quality
    {
        return $this->quality;
    }

    public function setQuality(?Quality $quality): OrderItemLogistic
    {
        $this->quality = $quality;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getOrderItem()->getOrder()->getNumber()
            . ' - '
            . $this->getProductVariantLogistic()->getName()
            . ' - '
            . $this->getBatchNumber();
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(OrderItem $orderItem): void
    {
        $this->orderItem = $orderItem;
    }

    public function getProductVariantLogistic(): ?ProductVariant
    {
        return $this->productVariantLogistic;
    }

    public function setProductVariantLogistic(ProductVariant $productVariantLogistic): void
    {
        $this->productVariantLogistic = $productVariantLogistic;
    }

    public function getBatchNumber(): ?string
    {
        return $this->batchNumber;
    }

    public function setBatchNumber(string $batchNumber): void
    {
        $this->batchNumber = $batchNumber;
    }
}
