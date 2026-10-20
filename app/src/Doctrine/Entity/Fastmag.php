<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'fastmag')]
#[ORM\Entity]
class Fastmag implements \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'fastmag_id', type: 'integer', nullable: false)]
    private ?int $fastmagId = null;

    #[ORM\Column(name: 'seller', type: 'string', nullable: true)]
    private ?string $seller = null;

    #[ORM\JoinColumn(name: 'sylius_order_id', referencedColumnName: 'id', unique: true, onDelete: 'cascade')]
    #[ORM\OneToOne(inversedBy: 'fastmag', targetEntity: Order::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?Order $order = null;

    public function __toString(): string
    {
        return (string) $this->getFastmagId();
    }

    public function getFastmagId(): ?int
    {
        return $this->fastmagId;
    }

    public function setFastmagId(int $fastmagId): void
    {
        $this->fastmagId = $fastmagId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): ?string
    {
        return $this->seller;
    }

    public function setSeller(string $seller): void
    {
        $this->seller = $seller;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }
}
