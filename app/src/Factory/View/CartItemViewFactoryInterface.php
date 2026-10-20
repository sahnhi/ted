<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\View\ItemTransmissionView;
use App\View\ItemView;
use Sylius\Component\Core\Model\ChannelInterface;

interface CartItemViewFactoryInterface
{
    public function create(OrderItem $item, ChannelInterface $channel, string $locale, bool $withVariants = false): ItemView;

    public function createTransmission(ChannelInterface $channel, string $locale, array $arrayItem): ItemTransmissionView;
}
