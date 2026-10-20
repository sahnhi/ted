<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_order_return')]
#[ORM\Entity]
class OrderReturn implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', unique: true)]
    #[ORM\OneToOne(targetEntity: Order::class, inversedBy: 'orderReturn')]
    private ?Order $order = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: false)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(name: 'history', type: 'text', nullable: true)]
    private ?string $history = null;

    #[ORM\Column(name: 'variants', type: 'text', nullable: true)]
    private ?string $variants = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToMany(targetEntity: OrderReturnItem::class, mappedBy: 'orderReturn', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $items = null;

    #[ORM\JoinColumn(name: 'sav_status_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: OrderReturnStatus::class)]
    private ?OrderReturnStatus $savStatus = null;

    #[ORM\JoinColumn(name: 'warehouse_status_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: OrderReturnStatus::class)]
    private ?OrderReturnStatus $warehouseStatus = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getSavStatus(): ?OrderReturnStatus
    {
        return $this->savStatus;
    }

    public function setSavStatus(OrderReturnStatus $savStatus): void
    {
        $this->savStatus = $savStatus;
    }

    public function getWarehouseStatus(): ?OrderReturnStatus
    {
        return $this->warehouseStatus;
    }

    public function setWarehouseStatus(OrderReturnStatus $warehouseStatus): void
    {
        $this->warehouseStatus = $warehouseStatus;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): void
    {
        $this->history = $history;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    public function setVariants($variants): void
    {
        $this->variants = $variants;
    }

    public function getItems(): ?Collection
    {
        return $this->items;
    }

    public function addItem(OrderReturnItem $item): void
    {
        if ($this->hasItem($item)) {
            return;
        }
        $this->items->add($item);
        $item->setOrderReturn($this);
    }

    public function hasItem(OrderReturnItem $item): bool
    {
        return $this->items->contains($item);
    }

    public function removeItem(OrderReturnItem $item): void
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
            $item->setOrderReturn(null);
        }
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
