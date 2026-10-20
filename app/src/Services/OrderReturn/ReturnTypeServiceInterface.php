<?php

namespace App\Services\OrderReturn;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;

interface ReturnTypeServiceInterface
{
    public const RETURN_PREFIXES = [
        Reason::RETURN_TYPE_EXCHANGE => 'RE',
        Reason::RETURN_TYPE_NORMAL => 'RT',
        Reason::RETURN_TYPE_QUALITY => 'RTQ',
        Reason::RETURN_TYPE_REFUND => 'RB',
        Reason::RETURN_TYPE_SENDER => 'RX',
        Reason::RETURN_TYPE_WAREHOUSE => 'REX',
        Reason::RETURN_TYPE_QUALITY_EXCHANGE => 'REQ',
        Reason::RETURN_TYPE_AUTONOMOUS => 'RA',
    ];

    public const RETURN_TYPE_EXCHANGE = 'exchange';
    public const RETURN_TYPE_NORMAL = 'simple';
    public const RETURN_TYPE_QUALITY = 'quality';
    public const RETURN_TYPE_REFUND = 'refund';
    public const RETURN_TYPE_SENDER = 'sender';
    public const RETURN_TYPE_WAREHOUSE = 'warehouse';
    public const RETURN_TYPE_QUALITY_EXCHANGE = 'quality-exchange';
    public const RETURN_TYPE_AUTONOMOUS = 'autonomous';

    public const REASON_RETURN_TYPE_MATCHES = [
        Reason::RETURN_TYPE_EXCHANGE => self::RETURN_TYPE_EXCHANGE,
        Reason::RETURN_TYPE_NORMAL => self::RETURN_TYPE_NORMAL,
        Reason::RETURN_TYPE_QUALITY => self::RETURN_TYPE_QUALITY,
        Reason::RETURN_TYPE_REFUND => self::RETURN_TYPE_REFUND,
        Reason::RETURN_TYPE_SENDER => self::RETURN_TYPE_SENDER,
        Reason::RETURN_TYPE_WAREHOUSE => self::RETURN_TYPE_WAREHOUSE,
        Reason::RETURN_TYPE_QUALITY_EXCHANGE => self::RETURN_TYPE_QUALITY_EXCHANGE,
        Reason::RETURN_TYPE_AUTONOMOUS => self::RETURN_TYPE_AUTONOMOUS,
    ];

    public function getReturnOrderPrefix(Order $order): ?string;

    public function getTemplateOptions(Order $order): array;

    public function getReturnType(Order $order): ?int;
}
