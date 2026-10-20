<?php

namespace App\Services\Mail;

use App\Message\DTO\OrderDTO;

class PayByLinkEmail
{
    private const TEMPLATE_NAME = 'lien-de-paiement';

    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    public function generateEmail(OrderDTO $order, string $link): void
    {
        $link = str_replace('https://', '', $link);

        $templateVars = [
            [
                'name' => 'URL',
                'content' => $link,
            ],
            [
                'name' => 'ORDER',
                'content' => $order->getNumber(),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, self::TEMPLATE_NAME, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
    }
}
