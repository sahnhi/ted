<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Message\DTO\OrderDTO;
use App\Message\DTO\ShippingMethodDTO;
use App\Services\Admin\InvoiceService;
use App\Services\Channel\ChannelCode;
use App\Services\Sentry\ReportingInterface;
use Gaufrette\Exception\FileNotFound;
use Sentry;

class TrackingEmail
{
    private const TEMPLATE_TRACKING_ORDER_B2C = 'dartagnan-suivi-livraison-fr-fr';

    public function __construct(
        private readonly MandrillEmail $mandrillEmail,
        private readonly InvoiceService $invoiceService,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function generateEmail(OrderDTO $order): void
    {
        // get data for mail
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found for this order');
        }

        $lastShipment = $order->getShipments()->first();
        /** @var ShippingMethodDTO $shippingMethod */
        $shippingMethod = $lastShipment->getMethod();

        $templateName = self::TEMPLATE_TRACKING_ORDER_B2C;
        if (null !== $order->getChannel() && 'b2b' === $order->getChannel()->getCode()) {
            $templateName = 'b2b-suivi-livraison-' . str_replace('_', '-', strtolower((string) $order->getLocaleCode()));
        }

        $mattress = ['mattress', 'small-mattress', 'slim-mattress'];
        $mattressItem = $sofaItem = $bedFrame = false;
        $build = false;

        foreach ($order->getItems() as $item) {
            if (in_array($item->getProductCode(), $mattress)) {
                $mattressItem = true;
            }
            if ('sofa-bed' === $item->getProductCode()) {
                $sofaItem = true;
            }
            if (str_contains((string) $item->getVariantCode(), 'MONT')) {
                $build = true;
            }
            if (str_contains((string) $item->getProductCode(), 'bed-frame')) {
                $bedFrame = true;
            }
        }

        $files = [];

        if (ChannelCode::CHANNEL_B2B !== $order->getChannel()->getCode()) {
            try {
                $invoiceFile = $this->invoiceService->getFileFromOrderDTO($order);

                if (null !== $invoiceFile) {
                    $files[] = [
                        'type' => 'application/pdf',
                        'name' => basename((string) $invoiceFile->getName()),
                        'content' => base64_encode((string) $invoiceFile->getContent()),
                    ];
                }
            } catch (FileNotFound) {
                $this->reporting->reportIssue(
                    'Invoice not found in Google Storage.',
                    Sentry\Severity::warning(),
                    ['service' => 'tracking_email'],
                    ['order_number' => $order->getNumber()]
                );
            }
        }

        $templateVars = [
            [
                'name' => 'COMMANDE',
                'content' => $order->getNumber(),
            ],
            [
                'name' => 'DATE',
                'content' => $lastShipment && $lastShipment->getStartDeliverySlot() ? $lastShipment->getStartDeliverySlot()->format('d/m/Y') : null,
            ],
            [
                'name' => 'HEUREDEBUT',
                'content' => $lastShipment && $lastShipment->getStartDeliverySlot() ? $lastShipment->getStartDeliverySlot()->format('H:i') : null,
            ],
            [
                'name' => 'HEUREFIN',
                'content' => $lastShipment && $lastShipment->getEndDeliverySlot() ? $lastShipment->getEndDeliverySlot()->format('H:i') : null,
            ],
            [
                'name' => 'EMAIL',
                'content' => $customer->getEmail(),
            ],
            [
                'name' => 'MATELAS',
                'content' => $mattressItem,
            ],
            [
                'name' => 'CANAPE',
                'content' => $sofaItem,
            ],
            [
                'name' => 'TOKEN',
                'content' => $order->getTokenValue(),
            ],
            [
                'name' => 'TUNED',
                'content' => $shippingMethod->isTuned(),
            ],
            [
                'name' => 'BUILD',
                'content' => $build,
            ],
            [
                'name' => 'DPD',
                'content' => str_contains((string) $shippingMethod->getCode(), ShippingMethodInterface::METHOD_DPD),
            ],
            [
                'name' => 'SOMMIER',
                'content' => $bedFrame,
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
            [
                'name' => 'TEDIBER_TRACKING',
                'content' => sprintf('https://www.tediber.com/suivi-de-commande?orderNumber=%s&email=%s', $order->getNumber(), $customer->getEmail()),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_MAILCHIMP, null, $files);
    }
}
