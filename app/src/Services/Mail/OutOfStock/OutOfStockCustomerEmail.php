<?php

namespace App\Services\Mail\OutOfStock;

use App\Doctrine\Entity\Sylius\Order;
use App\Services\Mail\MandrillEmail;

class OutOfStockCustomerEmail
{
    private const TEMPLATE = 'dartagnan-confirmation-rupture';

    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    public function generateEmail(Order $order, \DateTime $maxDate): void
    {
        // get data for mail
        $customer = $order->getCustomer();
        if (!$customer) {
            throw new \LogicException('No customer found for this Order');
        }

        $templateVars = [
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'NOM',
                'content' => $customer->getLastName(),
            ],
            [
                'name' => 'DATE',
                'content' => $maxDate->format('d/m/Y'),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, self::TEMPLATE, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
    }
}
