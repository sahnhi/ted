<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\OrderItem;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'sylius_order_item_product_combo')]
#[ORM\Entity]
class OrderItemProductCombo
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'order_item_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: OrderItem::class, inversedBy: 'orderItemProductCombo')]
    private ?OrderItem $orderItem = null;

    #[ORM\JoinColumn(name: 'product_combo_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductCombo::class)]
    private ?ProductCombo $productCombo = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(OrderItem $orderItem): void
    {
        $this->orderItem = $orderItem;
    }

    public function getProductCombo(): ?ProductCombo
    {
        return $this->productCombo;
    }

    public function setProductCombo(ProductCombo $productCombo): void
    {
        $this->productCombo = $productCombo;
    }
}
