<?php

namespace App\Context;

use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;

class CliBasedChannelContext implements CliBasedChannelContextInterface
{
    private ChannelInterface|null $channel = null;

    public function getChannel(): ChannelInterface
    {
        if ('cli' !== PHP_SAPI || null === $this->channel) {
            throw new ChannelNotFoundException();
        }

        return $this->channel;
    }

    public function setChannel(ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }
}
