<?php

declare(strict_types=1);

namespace App\Message\Order;

use Sylius\Bundle\ApiBundle\Command\ChannelCodeAwareInterface;

final class PickupAndAddItemToCart implements ChannelCodeAwareInterface
{
    use AddonsAwareTrait {
        __construct as initializeAddons;
    }

    private string|null $channelCode = null;

    public function __construct(
        public readonly string $localeCode,
        public readonly string $productVariantCode,
        public readonly int $quantity,
        array $addons = []
    ) {
        $this->initializeAddons($addons);
    }

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }

    public function setChannelCode(?string $channelCode): void
    {
        $this->channelCode = $channelCode;
    }
}
