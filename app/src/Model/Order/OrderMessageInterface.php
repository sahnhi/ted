<?php

declare(strict_types=1);

namespace App\Model\Order;

interface OrderMessageInterface
{
    public const TYPE_ERROR = 'error';

    public function getMessage(): string;

    public function getType(): string;
}
