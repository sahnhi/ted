<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Doctrine\Repository\Sylius\PaymentRepositoryInterface;
use App\PaymentReport\Model\Payment;
use App\PaymentReport\Model\PaymentList;
use App\Services\Payment\Model\PaymentDetails;
use App\Utils\DateRange;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Payment\Model\Payment as SyliusPayment;

final class PaymentUpdater implements PaymentUpdaterInterface
{
    private const DATE_RANGE = 'PT15M';
    private const DELAY_BETWEEN_ADYEN_AND_SYLIUS = 'PT2H';

    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly ObjectManager $paymentManager
    ) {
    }

    public function updateMultiplePayments(PaymentList $paymentList): void
    {
        foreach ($paymentList as $payment) {
            $this->updateSinglePayment($payment);
        }

        $this->paymentManager->flush();
    }

    private function updateSinglePayment(Payment $payment): void
    {
        $from = $payment->getCreatedAt()->sub(new \DateInterval(self::DELAY_BETWEEN_ADYEN_AND_SYLIUS));
        $to = $from->add(new \DateInterval(self::DATE_RANGE));

        $paymentOrder = $this->paymentRepository->findByDateRangeAmountAndSellerName(new DateRange($from, $to), $payment->getAmount(), $payment->getLastName(), $payment->getFirstName());

        if (!$paymentOrder instanceof SyliusPayment) {
            return;
        }

        $paymentDetailsToAdd = new PaymentDetails($payment->getPspReference(), $payment->getMerchantReference());
        $paymentOrder->setDetails(array_merge($paymentOrder->getDetails(), $paymentDetailsToAdd->toArray()));
        $this->paymentManager->persist($paymentOrder);
    }
}
