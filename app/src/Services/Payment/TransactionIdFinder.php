<?php

namespace App\Services\Payment;

use Sylius\Component\Core\Model\PaymentInterface;

class TransactionIdFinder
{
    public function getTransactionId(PaymentInterface $payment): string
    {
        $details = $payment->getDetails();
        $transactionId = '';

        if (isset($details['pspReference'])) {
            $transactionId = $details['originalReference'] ?? $details['pspReference'];
        } elseif (isset($details['sale']) && isset($details['sale']['transaction']) && isset($details['sale']['transaction']['id'])) {
            $transactionId = $details['sale']['transaction']['id'];
        } elseif (isset($details['TRANSACTIONID'])) {
            $transactionId = $details['TRANSACTIONID'];
        }

        return $transactionId;
    }
}
