<?php

namespace App\Business\Command;

use App\Calendar\Command\InsertItemsReasonForAutonomousReturn;
use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

final class SaveOrderItemReason implements OrderTokenValueAwareInterface
{
    public function __construct(
        private string|null $orderToken,
        private readonly array $itemReasons,
    ) {
    }

    public function getOrderTokenValue(): ?string
    {
        return $this->orderToken;
    }

    public function setOrderTokenValue(?string $orderTokenValue): void
    {
        $this->orderToken = $orderTokenValue;
    }

    public function itemReasons(): ?array
    {
        return $this->itemReasons;
    }

    public static function saveReasons(InsertItemsReasonForAutonomousReturn $command, $orderToken): self
    {
        return new self($orderToken, $command->getItems());
    }
}
