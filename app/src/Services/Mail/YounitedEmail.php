<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Legacy\Younited\YounitedApiInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class YounitedEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    /**
     * @param Order $order
     */
    public function cancelOrder($order): void
    {
        Assert::eq($order->getState(), OrderInterface::STATE_CANCELLED, 'The order is not cancelled.');

        /** @var Customer $customer */
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found for this payment');
        }

        if ($order->getPayments()->isEmpty()) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $order->getLastPayment(PaymentInterface::STATE_COMPLETED) ?? $order->getLastPayment(PaymentInterface::STATE_CANCELLED);

        if (null === $payment || !in_array($payment->getMethod()->getCode(), PaymentMethodInterface::YOUNITED_METHODS)) {
            return;
        }

        if (null === $templateName = $this->getEmailTemplateName($payment)) {
            return;
        }

        $templateVars = [
            [
                'name' => 'EMAIL',
                'content' => $customer->getEmail(),
            ],
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'NOM',
                'content' => $customer->getLastName(),
            ],
            [
                'name' => 'COMMANDE',
                'content' => $order->getNumber(),
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
    }

    private function getEmailTemplateName(PaymentInterface $payment): ?string
    {
        $canceledByYounited = 'dartagnan-younited-credit-refus';
        $canceledByCustomer = 'dartagnan-credit-client-annul';

        $details = $payment->getDetails();

        if (PaymentMethodInterface::YOUNITED_PAY === $payment->getMethod()->getCode()) {
            if (!empty($details['younitedpay']['cancel_source']) && PaymentMethodInterface::YOUNITED === $details['younitedpay']['cancel_source']) {
                return $canceledByCustomer;
            }

            return null;
        }

        // it should be removed when we switch completely to the new method YOUNITED_PAY
        if (!isset($details['cancelledByYounited']) || !isset($details['ReasonCode'])) {
            return null;
        }

        switch ($details['ReasonCode']) {
            case YounitedApiInterface::REASON_CODE_REFUSAL:
                return $canceledByYounited;
            case YounitedApiInterface::REASON_CODE_BOC_CANCEL:
                return $canceledByCustomer;
            default:
                throw new \InvalidArgumentException(sprintf('invalid reason :%s', $details['ReasonCode']));
        }
    }
}
