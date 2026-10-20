<?php

namespace App\Services\Mail\ReturnMail;

use App\Doctrine\Entity\Reason;
use App\Services\Mail\MandrillEmail;

class LabelEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    public function generateEmail($order, $returnOrder, $templateOptions, $files): void
    {
        $singular = (count($order->getItems()) > 1) ? false : true;
        $customer = $order->getCustomer();
        $quality = true;
        if (!$customer) {
            throw new \LogicException('No customer found');
        }

        if (!empty($files)) {
            foreach ($order->getItems() as $item) {
                if (str_contains((string) $item->getVariant()->getCode(), 'RTED')) {
                    $quality = false;
                    break;
                }
            }
            $templateName = 'dartagnan-etiquette-retour';
            $templateVars = [
                [
                    'name' => 'TITREETIQUETTE',
                    'content' => $singular ? 'VOTRE ÉTIQUETTE' : 'VOS ÉTIQUETTES',
                ],
                [
                    'name' => 'PRENOM',
                    'content' => $customer->getFirstName(),
                ],
                [
                    'name' => 'ETIQUETTE',
                    'content' => $singular ? "l'étiquette" : 'les étiquettes',
                ],
                [
                    'name' => 'DETERMINANT',
                    'content' => $singular ? 'la' : 'les',
                ],
                [
                    'name' => 'PACKAGE',
                    'content' => $singular ? 'votre' : 'vos',
                ],
                [
                    'name' => 'PRODUIT',
                    'content' => $singular ? 'votre produit' : 'vos produits',
                ],
                [
                    'name' => 'REMBOURSEMENT',
                    'content' => (in_array(Reason::TEMPLATE_OPTIONS['sylius.ui.reason.options.refund_needed'], $templateOptions)) ? true : false,
                ],
                [
                    'name' => 'ECHANGE',
                    'content' => ('RE' === $returnOrder) ? true : false,
                ],
                [
                    'name' => 'QUALITE',
                    'content' => (in_array(Reason::TEMPLATE_OPTIONS['sylius.ui.reason.options.100_nights'], $templateOptions) || true === $quality) ? true : false,
                ],
                [
                    'name' => 'GARANTIE',
                    'content' => (in_array(Reason::TEMPLATE_OPTIONS['sylius.ui.reason.options.100_nights'], $templateOptions) || true === $quality) ? false : true,
                ],
                [
                    'name' => 'USER_ID',
                    'content' => $customer->getId(),
                ],
            ];

            $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_MAILCHIMP, ['Tediber SAV' => 'sav@tediber.com'], $files);
        }
    }
}
