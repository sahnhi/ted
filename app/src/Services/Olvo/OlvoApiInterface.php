<?php

namespace App\Services\Olvo;

use App\Doctrine\Entity\Sylius\Order;

interface OlvoApiInterface
{
    public function sendOrder(Order $order): void;

    public function setArrayDatas(Order $order): array;
}
