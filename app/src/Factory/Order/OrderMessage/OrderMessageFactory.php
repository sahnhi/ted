<?php

declare(strict_types=1);

namespace App\Factory\Order\OrderMessage;

use App\Enum\OrderMessageLevelEnum;
use App\Model\Order\OrderMessage;

class OrderMessageFactory
{
    public function createNew(string $message, OrderMessageLevelEnum $messageLevel): OrderMessage
    {
        return new OrderMessage($message, $messageLevel->value);
    }

    public function createNewAlert(string $message): OrderMessage
    {
        return $this->createNew($message, OrderMessageLevelEnum::ALERT);
    }
}
