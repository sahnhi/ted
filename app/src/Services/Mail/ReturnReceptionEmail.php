<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;

class ReturnReceptionEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    public function generateEmail(Order $order, array $templateOptions = []): void
    {
        // get data for mail
        $customer = $order->getCustomer();
        if (!$customer) {
            throw new \LogicException('No customer found for this refund');
        }

        $templateName = 'dartagnan-confirmation-commande-retour';

        $templateVars = [
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'COMMANDE',
                'content' => $order->getParentNumber(),
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
            [
                'name' => 'REMBOURSEMENT',
                'content' => (in_array(Reason::TEMPLATE_OPTIONS['sylius.ui.reason.options.refund_needed'], $templateOptions)) ? true : false,
            ],
        ];

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_MAILCHIMP);
    }
}
