<?php

namespace App\Context;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;

interface CliBasedChannelContextInterface extends ChannelContextInterface
{
    public function setChannel(ChannelInterface $channel): void;
}
