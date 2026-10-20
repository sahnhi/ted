<?php

declare(strict_types=1);

namespace App\Calendar\Command;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

final class InsertItemsReasonForAutonomousReturn implements OrderTokenValueAwareInterface
{
    public function __construct(
        private string|null $tokenValue,
        private readonly string $email,
        private readonly array $items,
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
