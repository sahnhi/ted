<?php

namespace App\Factory\View;

use App\Doctrine\Entity\OrderReason;
use App\View\OrderReasonTransmissionView;
use Sylius\Component\Core\Model\ChannelInterface;

interface OrderReasonTransmissionViewFactoryInterface
{
    public function createTransmission(OrderReason $reason, ChannelInterface $channel, string $locale): OrderReasonTransmissionView;
}
