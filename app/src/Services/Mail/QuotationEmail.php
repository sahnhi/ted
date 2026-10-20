<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Order;
use Symfony\Component\Intl\Currencies;

class QuotationEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail
    ) {
    }

    private function orderItemsFormatter($order): array
    {
        $orderDetails = [];

        foreach ($order->getItems() as $orderItem) {
            $orderDetails[] = [
                'productName' => $orderItem->getVariantName(),
                'quantity' => $orderItem->getQuantity(),
                'unitPrice' => sprintf('%01.2f', $orderItem->getUnitPrice() / 100),
                'total' => sprintf('%01.2f', $orderItem->getTotal() / 100),
            ];
        }

        return $orderDetails;
    }

    /**
     * @param Order $order
     */
    public function generateEmail($order): void
    {
        // get data for mail
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found for this payment');
        }

        $seller = $order->getOrderStore()->getSeller();
        $store = $order->getOrderStore()->getStore();
        if (null !== $store->getChannel() && 'sav' === $store->getChannel()->getCode()) {
            $templateName = 'dartagnan-quotation-sav';
        } else {
            $templateName = 'dartagnan-quotation-retail';
        }

        $templateVars = [
            [
                'name' => 'EMAIL',
                'content' => $customer->getEmail(),
            ],
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'DETAIL_COMMANDE',
                'content' => $this->orderItemsFormatter($order),
            ],
            [
                'name' => 'DEVISE_SYMBOL',
                'content' => Currencies::getSymbol($order->getCurrencyCode()),
            ],
            [
                'name' => 'MONTANT_SOUS_TOTAL',
                'content' => sprintf('%01.2f', ($order->getTotal() - $order->getTaxTotal()) / 100),
            ],
            [
                'name' => 'MONTANT_TOTAL',
                'content' => sprintf('%01.2f', $order->getTotal() / 100),
            ],
            [
                'name' => 'MONTANT_TAXE_TOTAL',
                'content' => sprintf('%01.2f', $order->getTaxTotal() / 100),
            ],
            [
                'name' => 'TOKEN',
                'content' => $order->getTokenValue(),
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
            [
                'name' => 'SELLER',
                'content' => $seller->getName(),
            ],
            [
                'name' => 'CITY',
                'content' => $order->getOrderStore()->getStore()->getLocation(),
            ],
            [
                'name' => 'PHONE_NUMBER',
                'content' => $order->getOrderStore()->getStore()->getPhoneNumber(),
            ],
        ];

        $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS);
    }
}
