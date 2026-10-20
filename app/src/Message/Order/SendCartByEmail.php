<?php

declare(strict_types=1);

namespace App\Message\Order;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

final class SendCartByEmail implements OrderTokenValueAwareInterface
{
    public function __construct(
        private string $email,
        private string $orderToken
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getOrderTokenValue(): ?string
    {
        return $this->orderToken;
    }

    public function setOrderTokenValue(?string $orderTokenValue): void
    {
        $this->orderToken = $orderTokenValue;
    }
}
