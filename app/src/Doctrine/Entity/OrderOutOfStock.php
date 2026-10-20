<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\OrderOutOfStockRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_order_outofstock')]
#[ORM\Entity(repositoryClass: OrderOutOfStockRepository::class)]
class OrderOutOfStock implements ResourceInterface
{
    public const OUTOFSTOCK_CUSTOMER_STATE_CALLED_NO = 'called_no';
    public const OUTOFSTOCK_CUSTOMER_STATE_CALLED = 'called';
    public const OUTOFSTOCK_CUSTOMER_STATE_NEED_CALL = 'need_call';
    public const OUTOFSTOCK_CUSTOMER_STATE_CALLEDBACK = 'calledback';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'customerState', type: 'string', nullable: true)]
    private ?string $customerState = self::OUTOFSTOCK_CUSTOMER_STATE_CALLED_NO;

    #[ORM\Column(name: 'status', type: 'string', nullable: true)]
    private ?string $status = null;

    #[ORM\Column(name: 'deliver_date', type: 'datetime', nullable: true)]
    private ?\DateTime $deliverDate = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'history', type: 'text', nullable: true)]
    private ?string $history = null;

    #[ORM\Column(name: 'dateConfirmed', type: 'boolean')]
    private bool $dateConfirmed = false;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', unique: true)]
    #[ORM\OneToOne(targetEntity: Order::class, inversedBy: 'orderOutOfStock')]
    private ?Order $order = null;

    public function getCode(): ?string
    {
        return '#' . $this->order->getNumber();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getDeliverDate(): ?\DateTime
    {
        return $this->deliverDate;
    }

    public function setDeliverDate(?\DateTime $deliverDate): void
    {
        $this->deliverDate = $deliverDate;
    }

    public function getCustomerState(): ?string
    {
        return $this->customerState;
    }

    public function setCustomerState(?string $customerState): void
    {
        $this->customerState = $customerState;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): void
    {
        $this->history = $history;
    }

    public function isDateConfirmed(): bool
    {
        return $this->dateConfirmed;
    }

    public function setDateConfirmed(bool $dateConfirmed): void
    {
        $this->dateConfirmed = $dateConfirmed;
    }
}
