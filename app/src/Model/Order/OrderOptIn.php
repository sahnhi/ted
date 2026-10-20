<?php

declare(strict_types=1);

namespace App\Model\Order;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

class OrderOptIn implements OrderTokenValueAwareInterface
{
    public function __construct(
        private string|null $tokenValue,
        public readonly string $email,
    ) {
    }

    public function getOrderTokenValue(): ?string
    {
        return $this->tokenValue;
    }

    public function setOrderTokenValue(?string $orderTokenValue): void
    {
        $this->tokenValue = $orderTokenValue;
    }
}
