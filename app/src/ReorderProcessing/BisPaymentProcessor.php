<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Webmozart\Assert\Assert;

final class BisPaymentProcessor implements ReorderProcessor
{
    public function __construct(
        private readonly PaymentFactoryInterface $paymentFactory,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
    }

    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $method = $this->paymentMethodRepository->findOneBy(['code' => 'BIS']);
        Assert::notNull($method);

        /** @var PaymentInterface $newPayment */
        $newPayment = $this->paymentFactory->createNew();
        $newPayment->setOrder($reorder);
        $newPayment->setState(PaymentInterface::STATE_COMPLETED);
        $newPayment->setMethod($method);
        $newPayment->setCurrencyCode($order->getCurrencyCode());

        $reorder->addPayment($newPayment);
    }
}
