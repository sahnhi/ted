<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_front_data')]
#[ORM\Entity]
class FrontData implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'frontVersion', type: 'string', nullable: true)]
    private ?string $frontVersion = null;

    #[ORM\Column(name: 'userAgent', type: 'text', nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'state', type: 'string', nullable: false)]
    private ?string $state = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'frontData')]
    private ?Order $order = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrontVersion(): ?string
    {
        return $this->frontVersion;
    }

    public function setFrontVersion(?string $frontVersion): void
    {
        $this->frontVersion = $frontVersion;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }
}
