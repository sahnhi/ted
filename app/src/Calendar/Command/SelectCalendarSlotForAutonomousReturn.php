<?php

declare(strict_types=1);

namespace App\Calendar\Command;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

final class SelectCalendarSlotForAutonomousReturn implements OrderTokenValueAwareInterface
{
    public function __construct(
        private string|null $tokenValue,
        private readonly string $email,
        private readonly string $method,
        private readonly ?string $startSlot,
        private readonly ?string $endSlot,
        private readonly ?int $erpId,
        private readonly ?string $relayId,
        private readonly ?string $relayAddress,
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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getStartDeliverySlot(): ?\DateTime
    {
        return $this->startSlot ? \DateTime::createFromFormat('Y-m-d H:i:s', $this->startSlot) : null;
    }

    public function getEndDeliverySlot(): ?\DateTime
    {
        return $this->endSlot ? \DateTime::createFromFormat('Y-m-d H:i:s', $this->endSlot) : null;
    }

    public function getErpId(): ?int
    {
        return $this->erpId;
    }

    public function getRelayId(): ?string
    {
        return $this->relayId;
    }

    public function getRelayAddress(): ?string
    {
        return !empty($this->relayAddress) ? $this->relayAddress : null;
    }
}
