<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Intl\Currencies;

class RefundConfirmationEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    /**
     * @param Order            $order
     * @param PaymentInterface $payment
     */
    public function generateEmail($order, $payment): void
    {
        // get data for mail
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found for this refund');
        }

        $templateName = 'dartagnan-confirmation-remboursement';

        $templateVars = [
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'MONTANT',
                'content' => sprintf('%01.2f', $payment->getAmount() / 100),
            ],
            [
                'name' => 'DEVISE_SYMBOL',
                'content' => Currencies::getSymbol($order->getCurrencyCode()),
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

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_MAILCHIMP);
    }
}
