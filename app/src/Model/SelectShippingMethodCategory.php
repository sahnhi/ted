<?php

declare(strict_types=1);

namespace App\Model;

use Sylius\Bundle\ApiBundle\Command\OrderTokenValueAwareInterface;

class SelectShippingMethodCategory implements OrderTokenValueAwareInterface
{
    public function __construct(
        public readonly string $shippingMethodCategoryCode,
        public readonly array $serviceCodes,
        private string|null $tokenValue = null,
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
