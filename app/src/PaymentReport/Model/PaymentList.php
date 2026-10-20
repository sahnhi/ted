<?php

declare(strict_types=1);

namespace App\PaymentReport\Model;

use IteratorAggregate;
use Webmozart\Assert\Assert;

/** @implements IteratorAggregate<Payment> */
final class PaymentList implements \Countable, \IteratorAggregate
{
    public const ADYEN_PAYMENT_STATE_RECEIVED = 'Received';

    /** @var Payment[] */
    private $payments;

    public function __construct(array $payments)
    {
        Assert::allIsInstanceOf($payments, Payment::class);
        $this->payments = $payments;
    }

    /**
     * @return \Generator<Payment>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->payments;
    }

    public function count(): int
    {
        return count($this->payments);
    }

    public function add(Payment $payment): self
    {
        $payments = $this->payments;
        $payments[] = $payment;

        return new self($payments);
    }

    public function removePaymentWithSpecificPspReference(string $pspReference): self
    {
        $payments = $this->payments;

        return new self(array_filter($payments, static fn (Payment $newPayment): bool => $pspReference !== $newPayment->getPspReference()));
    }

    public function getPaymentsWithSpecificMerchantReference(string $merchantReference): self
    {
        return new self(array_filter(
            $this->payments,
            static fn (Payment $payment): bool => $merchantReference === $payment->getMerchantReference()
        ));
    }

    public function getPaymentsWithSpecificPaymentState(string $paymentState): self
    {
        return new self(array_filter(
            $this->payments,
            static fn (Payment $payment): bool => $paymentState === $payment->getResultCode()
        ));
    }

    public function getAmount(): int
    {
        $result = 0;
        foreach ($this->payments as $payment) {
            $result += $payment->getAmount();
        }

        return $result;
    }

    public function sanitize(): self
    {
        $receivedPaymentList = $this->getPaymentsWithSpecificPaymentState(PaymentList::ADYEN_PAYMENT_STATE_RECEIVED);

        foreach ($receivedPaymentList as $payment) {
            if (null === $payment->getFirstName() && 'alma' === $payment->getLastName()) {
                $merchantReference = $payment->getMerchantReference();
                $paymentAlma = $receivedPaymentList->getPaymentsWithSpecificMerchantReference($merchantReference);

                $buyerPayment = Payment::fromArray(
                    array_values(
                        array_filter($paymentAlma->toArray(), static fn (array $payment): bool => 'alma' !== $payment['lastName'])
                    )[0]
                );

                $receivedPaymentList = $receivedPaymentList->add(new Payment(
                    $merchantReference,
                    $buyerPayment->getFirstName(),
                    $buyerPayment->getLastName(),
                    $paymentAlma->getAmount(),
                    $buyerPayment->getCreatedAt()->format('Y-m-d H:i:s'),
                    $buyerPayment->getPspReference(),
                    $buyerPayment->getResultCode()
                ));

                $receivedPaymentList = $receivedPaymentList->removePaymentWithSpecificPspReference($payment->getPspReference());
            }
        }

        return $receivedPaymentList;
    }

    public function toArray(): array
    {
        return array_map(static fn (Payment $payment): array => $payment->toArray(), $this->payments);
    }
}
