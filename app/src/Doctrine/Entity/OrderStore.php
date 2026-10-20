<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

#[Entity]
#[Table(name: 'sylius_order_store')]
class OrderStore implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'orderStore', targetEntity: Order::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'cascade')]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Seller::class, inversedBy: 'orderStore')]
    private ?Seller $seller = null;

    #[ORM\ManyToOne(targetEntity: Store::class, inversedBy: 'orderStore')]
    private ?Store $store = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(Seller $seller): void
    {
        $this->seller = $seller;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(Store $store): void
    {
        $this->store = $store;
    }
}
