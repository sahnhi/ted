<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\Refund;
use App\Doctrine\Entity\Sylius\Order;
use App\Tests\TestCase;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\InvalidArgumentException;

class RefundTest extends TestCase
{
    /** @var Refund */
    private $refund;

    public function setUpModel(PaymentInterface $payment): void
    {
        $this->refund = new Refund($payment);
    }

    public function testConstruct(): void
    {
        $payment = new Payment();
        $payment->setOrder(new Order());
        $payment->getOrder()->setNumber('number');

        $payment->setAmount(50);
        $payment->setDetails(['REFUND' => 'transaction_ID']);

        $this->setUpModel($payment);
        $this->assertEquals($payment->getOrder()->getNumber(), $this->refund->name);
        $this->assertEquals($payment->getAmount(), $this->refund->amount);
        $this->assertEquals($payment->getDetails()['REFUND'], $this->refund->transactionId);
    }

    public function testConstructNoOrderError(): void
    {
        $payment = new Payment();

        $this->expectException(InvalidArgumentException::class);
        $this->setUpModel($payment);
    }

    public function testConstructNoTransactionIdError(): void
    {
        $payment = new Payment();
        $payment->setOrder(new Order());

        $payment->setAmount(50);

        $this->expectException(InvalidArgumentException::class);
        $this->setUpModel($payment);
    }
}
