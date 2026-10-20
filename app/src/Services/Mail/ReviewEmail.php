<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Order;

class ReviewEmail
{
    public const MATTRESS = 'mattress';
    public const SMALL_MATTRESS = 'small-mattress';
    public const SLIM_MATTRESS = 'slim-mattress';
    public const BED_FRAME = 'bed-frame';
    public const SOFA_BED = 'sofa-bed';

    public const MATTRESS_GIFT_LIST = [
        self::MATTRESS,
        self::SMALL_MATTRESS,
        self::SLIM_MATTRESS,
    ];

    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    /**
     * @param Order $order
     */
    public function generateEmail($order): void
    {
        // get data for mail
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found');
        }

        $channel = $order->getChannel();

        if ('b2b' === $channel->getCode()) {
            $templateName = 'mail-demande-avis-b2b';
        } else {
            $templateName = 'dartagnan-mail-demande-d-avis';
        }

        $productType = 'phenix';

        foreach ($order->getItems() as $item) {
            if (!str_contains((string) $item->getVariant()->getProduct()->getCode(), 'phenix')) {
                $productType = '';
            }
        }

        $templateVars = [
            [
                'name' => 'TOKEN',
                'content' => $order->getTokenValue(),
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
                'name' => 'TYPEPRODUIT',
                'content' => $productType,
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_MAILCHIMP);
    }
}
