<?php

namespace App\Tests\Unit\Api\Odoo\Factory;

use App\Api\Odoo\Factory\RefundFactory;
use App\Api\Odoo\Model\RefundInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Tests\TestCase;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;

class RefundFactoryTest extends TestCase
{
    /** @var RefundFactory */
    private $factory;

    /** @var PaymentInterface */
    private $payment;

    public function setUpFactory()
    {
        $this->factory = new RefundFactory();
    }

    private function setUpCreateNew()
    {
        $this->payment = new Payment();
        $this->payment->setOrder(new Order());
        $this->payment->setDetails(['REFUND' => 'transaction_ID']);

        $this->setUpFactory();
    }

    public function testCreateNew(): void
    {
        $this->setUpCreateNew();
        $refund = $this->factory->createNew($this->payment);

        $this->assertInstanceOf(RefundInterface::class, $refund);
    }
}
