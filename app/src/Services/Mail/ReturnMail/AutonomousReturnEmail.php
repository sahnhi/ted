<?php

namespace App\Services\Mail\ReturnMail;

use App\Doctrine\Entity\Sylius\Order;
use App\Services\Mail\MandrillEmail;

class AutonomousReturnEmail
{
    public const TEMPLATE_NAME = 'retour-autonome';

    public function __construct(
        private readonly MandrillEmail $mandrillEmail,
        private readonly string $tediberFrontUrl
    ) {
    }

    public function generateEmail(Order $order)
    {
        $templateVars = [
            [
                'name' => 'RETURN_FRONT_URL',
                'content' => $this->generateUrl($order),
            ],
            [
                'name' => 'ORDER_NUMBER',
                'content' => $order->getParentNumber(),
            ],
            [
                'name' => 'FIRSTNAME',
                'content' => $order->getCustomer()->getFirstName(),
            ],
            [
                'name' => 'LASTNAME',
                'content' => $order->getCustomer()->getLastName(),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, self::TEMPLATE_NAME, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
    }

    private function generateUrl(Order $order): string
    {
        return $this->tediberFrontUrl
            . 'retour-autonome?cart='
            . $order->getTokenValue()
            . '&email='
            . $order->getCustomer()->getEmail();
    }
}
