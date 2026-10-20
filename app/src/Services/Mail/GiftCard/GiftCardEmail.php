<?php

namespace App\Services\Mail\GiftCard;

use App\Doctrine\Entity\Sylius\Order;
use App\Generator\GiftCardPdfFileGeneratorInterface;
use App\Services\Mail\MandrillEmail;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Intl\Currencies;

class GiftCardEmail
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail,
        private readonly GiftCardPdfFileGeneratorInterface $pdfGenerator
    ) {
    }

    public function generateEmail(Order $order, OrderItemInterface $item, string $recipient = '0'): void
    {
        $amount = $order->getTotal() / 100;
        $customer = $order->getCustomer();
        $productName = $item->getProduct()->getMainTaxon()->getTranslation('fr_FR')->getName();

        $unit = $item->getUnits()->first();
        $code = $unit->getGiftCard()->getCode();
        $createdAt = new \DateTime($unit->getGiftCard()->getCreatedAt()->format('Y-m-d'));
        $expired = $createdAt->add(new \DateInterval('P12M'));
        $giftCardAddress = $unit->getGiftCard();
        $file = $this->pdfGenerator->generate($giftCardAddress->getId());
        $files[] = [
            'type' => 'application/pdf',
            'name' => $file->filename(),
            'content' => base64_encode($file->content()),
        ];

        $paymentMethodName = 'NA';

        if (!$order->getPayments()->isEmpty()) {
            /** @var PaymentInterface $payment */
            foreach ($order->getPayments() as $payment) {
                if ('completed' === $payment->getState()) {
                    $paymentMethodName = $payment->getMethod()->getName();
                }
            }
        }
        if (!$customer) {
            throw new \LogicException('No customer found');
        }

        if ('0' === $recipient) {
            $templateName = 'dartagnan-confirmation-commande-carte-cadeau';
            $templateVars = [
                [
                    'name' => 'ACHETEUR',
                    'content' => $customer->getFirstName(),
                ],
                [
                    'name' => 'DESTINATAIRE',
                    'content' => $giftCardAddress->getNameRecipient(),
                ],
                [
                    'name' => 'EMAIL_DESTINATAIRE',
                    'content' => $giftCardAddress->getEmailRecipient(),
                ],
                [
                    'name' => 'CODE',
                    'content' => $code,
                ],
                [
                    'name' => 'COMMANDE',
                    'content' => $order->getNumber(),
                ],
                [
                    'name' => 'PRODUCTNAME',
                    'content' => $productName,
                ],
                [
                    'name' => 'QUANTITY',
                    'content' => 1,
                ],
                [
                    'name' => 'TOTAL',
                    'content' => $amount,
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
                    'name' => 'METHODE_PAIEMENT',
                    'content' => $paymentMethodName,
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
                    'name' => 'USER_ID',
                    'content' => $customer->getId(),
                ],
            ];

            $this->mandrillEmail->generateEmail($order, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS, [], $files);
        } else {
            $templateName = 'dartagnan-reception-carte-cadeau';
            $templateVars = [
                [
                    'name' => 'DESTINATAIRE',
                    'content' => $giftCardAddress->getNameRecipient(),
                ],
                [
                    'name' => 'MESSAGE',
                    'content' => $giftCardAddress->getMessage(),
                ],
                [
                    'name' => 'CODE',
                    'content' => $code,
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
                    'name' => 'TOTAL',
                    'content' => $amount,
                ],
                [
                    'name' => 'DEVISE_SYMBOL',
                    'content' => Currencies::getSymbol($order->getCurrencyCode()),
                ],
                [
                    'name' => 'VALIDITE',
                    'content' => $expired->format('d/m/Y'),
                ],
                [
                    'name' => 'USER_ID',
                    'content' => $customer->getId(),
                ],
            ];

            $customer =
                [
                    'email' => $giftCardAddress->getEmailRecipient(),
                    'name' => $giftCardAddress->getNameRecipient(),
                    'id' => '',
                ];

            $this->mandrillEmail->generateToAnOtherEmail($order, $customer, $templateName, $templateVars, MandrillEmail::MERGE_LANGUAGE_HANDLEBARS, [], $files);
        }
    }
}
