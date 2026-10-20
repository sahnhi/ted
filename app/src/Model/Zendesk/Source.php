<?php

namespace App\Model\Zendesk;

final class Source
{
    public const CHANNEL_MAIL = 'email';
    public const CHANNEL_VOICE = 'voice';

    public const SUPPORTED_CHANNELS = [
        self::CHANNEL_MAIL,
        self::CHANNEL_VOICE,
    ];
}
