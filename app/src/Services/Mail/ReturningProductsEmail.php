<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Order;
use App\Model\ProductReferenceInterface;

class ReturningProductsEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    /**
     * @param Order $order
     */
    public function generateEmail($order, $returningProducts): void
    {
        // get data for mail
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found');
        }

        $templateName = 'dartagnan-reprise-matelas-ou-sommier';

        $templateVars = [
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => ProductReferenceInterface::REP_MAT,
                'content' => in_array(ProductReferenceInterface::REP_MAT, $returningProducts),
            ],
            [
                'name' => ProductReferenceInterface::REP_SOM,
                'content' => in_array(ProductReferenceInterface::REP_SOM, $returningProducts),
            ],
            [
                'name' => ProductReferenceInterface::REP_TET,
                'content' => in_array(ProductReferenceInterface::REP_TET, $returningProducts),
            ],
            [
                'name' => ProductReferenceInterface::REP_SOF,
                'content' => in_array(ProductReferenceInterface::REP_SOF, $returningProducts),
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_MAILCHIMP);
    }
}
