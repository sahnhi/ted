<?php

namespace App\Tests\Unit\Odoo\StateMachine\Guard;

use App\Api\Odoo\StateMachine\Guard\CancelOrder;
use App\Doctrine\Entity\Sylius\Order;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Order\Model\OrderInterface;

class CancelOrderTest extends TestCase
{
    public function testGuardReturnFalseWhenOrderIsNotReady(): void
    {
        $order = new Order();
        $order->setOdooState(\App\Api\Odoo\Model\Order::ODOO_STATE_NOT_READY);
        $this->assertFalse((new CancelOrder())($order));
    }

    public function testGuardReturnFalseWhenOrderIsReady(): void
    {
        $order = new Order();
        $order->setOdooState(\App\Api\Odoo\Model\Order::ODOO_STATE_READY);
        $this->assertFalse((new CancelOrder())($order));
    }

    public function testGuardReturnFalseWhenOrderIsNotCancelled(): void
    {
        $order = new Order();
        $order->setOdooState(\App\Api\Odoo\Model\Order::ODOO_STATE_READY);
        $order->setState(OrderInterface::STATE_NEW);
        $this->assertFalse((new CancelOrder())($order));
    }

    public function testGuardReturnTrueWhenOrderIsCancelled(): void
    {
        $order = new Order();
        $order->setOdooState(\App\Api\Odoo\Model\Order::ODOO_STATE_CREATED);
        $order->setState(OrderInterface::STATE_CANCELLED);
        $this->assertTrue((new CancelOrder())($order));
    }
}
