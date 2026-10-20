<?php

namespace App\Services\Channel;

abstract class ChannelCode
{
    public const CHANNEL_BIS = 'bis';
    public const CHANNEL_RETURN = 'return';
    public const CHANNEL_B2B = 'b2b';
    public const CHANNEL_WEB = 'default';
    public const CHANNEL_OPERATION = 'operation';
    public const CHANNEL_SAV = 'sav';
    public const CHANNEL_CARREFOUR = 'carrefour';
    public const CHANNEL_RETAIL = 'boutique';

    public const NOT_SELL_CHANNELS = [self::CHANNEL_BIS, self::CHANNEL_RETURN, self::CHANNEL_OPERATION];
    public const FREE_DELIVERY = [self::CHANNEL_BIS, self::CHANNEL_RETURN, self::CHANNEL_RETAIL, self::CHANNEL_B2B, self::CHANNEL_SAV];
    public const SYLIUS_FRONT_END = [self::CHANNEL_RETAIL, self::CHANNEL_B2B, self::CHANNEL_SAV];
}
