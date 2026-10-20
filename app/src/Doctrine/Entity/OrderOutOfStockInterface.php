<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\Component\Resource\Model\ResourceInterface;

interface OrderOutOfStockInterface extends ResourceInterface
{
    public const STATE_OUT_OF_STOCK = 'out_of_stock';
    public const STATE_SOLVED = 'solved';
    public const STATE_PARTIALLY_PLANNED = 'partially_planned';
    public const STATE_TO_SEND = 'to_send';
    public const STATE_PLANNED = 'planned';
    public const VIP_WEIGHTING_VALUE = 10;

    public function getId(): ?int;

    public function getOrder(): ?Order;

    public function setOrder(Order $order): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(\DateTimeInterface $createdAt): void;

    public function getStatus(): string;

    public function setStatus(string $status): void;
}
