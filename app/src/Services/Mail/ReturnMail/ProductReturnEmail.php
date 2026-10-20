<?php

namespace App\Services\Mail\ReturnMail;

use App\Doctrine\Entity\Reason;
use App\Message\DTO\OrderDTO;
use App\Services\Mail\MandrillEmail;
use App\Services\OrderReturn\ReturnTypeServiceInterface;

class ProductReturnEmail
{
    public const MATTRESS = 'mattress';
    public const BED_FRAME = 'bed-frame';
    public const SOFA_BED = 'sofa-bed';

    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    public function generateEmail(OrderDTO $order, string $returnOrder, string $returnType, array $templateOptions = []): void
    {
        // get data for mail
        $customer = $order->getCustomer();
        $shipment = $order->getShipments()[0];
        $templateName = null;
        $quality = true;

        if (!$customer) {
            throw new \LogicException('No customer found');
        }

        if (in_array($returnType, [ReturnTypeServiceInterface::RETURN_TYPE_NORMAL, ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS])) {
            $templateName = 'dartagnan-retour-matelas-edeliv-vir';
        } elseif (ReturnTypeServiceInterface::RETURN_TYPE_EXCHANGE === $returnType) {
            $templateName = 'dartagnan-echange-produit-edeliv-vir';
        }

        $productType = null;
        foreach ($order->getItems() as $item) {
            if (null === $productType) {
                if (str_contains((string) $item->getVariant()->getProduct()->getCode(), self::MATTRESS)) {
                    $productType = 'matelas';
                }
                if (str_contains((string) $item->getVariant()->getProduct()->getCode(), self::BED_FRAME)) {
                    $productType = 'sommier';
                }
                if (str_contains((string) $item->getVariant()->getProduct()->getCode(), self::SOFA_BED)) {
                    $productType = 'canapé-lit';
                }
            }

            if (str_contains((string) $item->getVariant()->getCode(), 'RTED')) {
                $quality = false;
                break;
            }
        }

        $templateVars = [
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'DATE',
                'content' => $shipment->getStartDeliverySlot()->format('d/m/Y'),
            ],
            [
                'name' => 'CRENEAU',
                'content' => $shipment->getStartDeliverySlot()->format('H:i') . ' et ' . $shipment->getEndDeliverySlot()->format('H:i'),
            ],
            [
                'name' => 'PRODUIT',
                'content' => $productType ?: 'produit',
            ],
            [
                'name' => 'QUALITE',
                'content' => (in_array(Reason::TEMPLATE_OPTIONS['sylius.ui.reason.options.100_nights'], $templateOptions) || true === $quality) ? true : false,
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
