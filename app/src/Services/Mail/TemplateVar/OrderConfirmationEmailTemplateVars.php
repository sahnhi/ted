<?php

declare(strict_types=1);

namespace App\Services\Mail\TemplateVar;

use App\Doctrine\Entity\ShippingMethodCategoryClaim;
use App\Doctrine\Entity\ShippingMethodCategoryClaimTranslation;
use App\Message\DTO\AddressDTO;
use App\Message\DTO\OrderItemDTO;
use Symfony\Component\Intl\Countries;

final class OrderConfirmationEmailTemplateVars implements TemplateVarsInterface
{
    /**
     * @param array<ShippingMethodCategoryClaim> $claims
     */
    public function __construct(
        private readonly ?string $phoneNumber,
        private readonly ?string $email,
        private readonly ?\DateTime $startDeliverySlot,
        private readonly ?\DateTime $endDeliverySlot,
        private readonly ?string $firstName,
        private readonly ?string $lastName,
        private readonly AddressDTO $shippingAddress,
        private readonly AddressDTO $billingAddress,
        private readonly bool $isFirstCommand,
        private readonly ?string $orderNumber,
        private readonly array $orderItems,
        private readonly string $shippingMethod,
        private readonly string $currencySymbol,
        private readonly string $subtotal,
        private readonly string $total,
        private readonly string $taxesAmount,
        private readonly ?string $paymentMethod,
        private readonly bool $paymentNTimes,
        private readonly array $monthlyAmount,
        private readonly string $orderToken,
        private readonly bool $hasTrackingLink,
        private readonly bool $isFloor,
        private readonly bool $isTuned,
        private readonly bool $buildOption,
        private readonly ?string $giftCardAmount,
        private readonly ?string $company,
        private readonly int $customerId,
        private readonly bool $isRelayShipping,
        private readonly ?string $shippingMethodCategoryName,
        private readonly array $claims,
        private readonly int $shippingTotal,
        private readonly string $templateName
    ) {
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function toArray(): array
    {
        return [
            [
                'name' => 'TELEPHONE',
                'content' => $this->phoneNumber,
            ],
            [
                'name' => 'EMAIL',
                'content' => $this->email,
            ],
            [
                'name' => 'DATE',
                'content' => $this->startDeliverySlot ? $this->startDeliverySlot->format('d/m/Y') : null,
            ],
            [
                'name' => 'HEUREDEBUT',
                'content' => $this->startDeliverySlot ? $this->startDeliverySlot->format('H:i') : null,
            ],
            [
                'name' => 'HEUREFIN',
                'content' => $this->endDeliverySlot ? $this->endDeliverySlot->format('H:i') : null,
            ],
            [
                'name' => 'PRENOM',
                'content' => $this->firstName,
            ],
            [
                'name' => 'NOM',
                'content' => $this->lastName,
            ],
            [
                'name' => 'ADRESSE_LIVRAISON',
                'content' => $this->addressFormatter($this->shippingAddress),
            ],
            [
                'name' => 'ADRESSE_FACTURATION',
                'content' => $this->addressFormatter($this->billingAddress),
            ],
            [
                'name' => 'PREMIERECOMMANDE',
                'content' => $this->isFirstCommand,
            ],
            [
                'name' => 'COMMANDE',
                'content' => $this->orderNumber,
            ],
            [
                'name' => 'DETAIL_COMMANDE',
                'content' => $this->orderItemsFormatter($this->orderItems),
            ],
            [
                'name' => 'XPORJOYAU',
                'content' => 'XP' === $this->shippingMethod || 'JOYAU' === $this->shippingMethod || 'UPS-INTER' === $this->shippingMethod,
            ],
            [
                'name' => 'DPD',
                'content' => 'DPD' === $this->shippingMethod,
            ],
            [
                'name' => 'NOUVELLECOMMANDE',
                'content' => $this->isFirstCommand,
            ],
            [
                'name' => 'DEVISE_SYMBOL',
                'content' => $this->currencySymbol,
            ],
            [
                'name' => 'MONTANT_SOUS_TOTAL',
                'content' => $this->subtotal,
            ],
            [
                'name' => 'MONTANT_TOTAL',
                'content' => $this->total,
            ],
            [
                'name' => 'MONTANT_TAXE_TOTAL',
                'content' => $this->taxesAmount,
            ],
            [
                'name' => 'METHODE_PAIEMENT',
                'content' => $this->paymentMethod,
            ],
            [
                'name' => 'NFOISSANSFRAIS',
                'content' => $this->paymentNTimes,
            ],
            [
                'name' => 'NFOISSANSFRAIS_MONTANTS',
                'content' => $this->monthlyAmount,
            ],
            [
                'name' => 'TOKEN',
                'content' => $this->orderToken,
            ],
            [
                'name' => 'TRACKING',
                'content' => $this->hasTrackingLink,
            ],
            [
                'name' => 'FLOOR',
                'content' => $this->isFloor,
            ],
            [
                'name' => 'TUNED',
                'content' => $this->isTuned,
            ],
            [
                'name' => 'BUILD',
                'content' => $this->buildOption,
            ],
            [
                'name' => 'MONTANT_CARTE_CADEAU',
                'content' => $this->giftCardAmount,
            ],
            [
                'name' => 'SOCIETE',
                'content' => $this->company,
            ],
            [
                'name' => 'USER_ID',
                'content' => $this->customerId,
            ],
            [
                'name' => 'METHOD_RELAY',
                'content' => $this->isRelayShipping,
            ],
            [
                'name' => 'METHODE_DE_LIVRAISON',
                'content' => $this->shippingMethodCategoryName,
            ],
            [
                'name' => 'SERVICE_AVEC_LA_LIVRAISON',
                'content' => $this->claimsFormatter($this->claims),
            ],
            [
                'name' => 'FRAIS_DE_PORT',
                'content' => $this->shippingTotal,
            ],
        ];
    }

    private function orderItemsFormatter(array $orderItems): array
    {
        $orderDetails = [];

        foreach ($orderItems as $orderItem) {
            if (!$orderItem instanceof OrderItemDTO) {
                throw new \LogicException('Invalid order item to format');
            }
            $compositions = [];
            foreach ($orderItem->getVariantCompositions() as $bundleVariant) {
                $quantity = $bundleVariant->getQuantity();
                $suffixName = $quantity > 1 ? " (X$quantity)" : '';
                $compositions[] = [
                    'productName' => sprintf('%s%s', $bundleVariant->getProductName(), $suffixName),
                    'optionDetails' => $bundleVariant->getOptionsDetails(),
                ];
            }

            $productName = empty($orderItem->getVariantCompositions()) ? $orderItem->getVariantName() : $orderItem->getProductName();

            $orderDetails[] = [
                'productName' => $productName,
                'quantity' => $orderItem->getQuantity(),
                'unitPrice' => sprintf('%01.2f', $orderItem->getUnitPrice() / 100),
                'total' => sprintf('%01.2f', $orderItem->getTotal() / 100),
                'compositions' => $compositions,
            ];
        }

        return $orderDetails;
    }

    private function addressFormatter(AddressDTO $address): string
    {
        // prenom nom, numéro + rue, code postal ville, pays
        $fullName = $address->getFullName();
        $street = $address->getStreet();
        $postCode = $address->getPostcode();
        $city = $address->getCity();
        $countryCode = $address->getCountryCode();
        $countryName = Countries::getName($countryCode);

        return sprintf('%s<br><a href="https://maps.google.com/?q=%2$s&entry=gmail&source=g">%s</a><br>%s %s %s<br>%s', $fullName, $street, $postCode, $city, $countryName, $address->getDetails());
    }

    private function claimsFormatter(array $claims): array
    {
        $claimsFormatted = [];
        /** @var ShippingMethodCategoryClaim $claim */
        foreach ($claims as $claim) {
            /** @var ShippingMethodCategoryClaimTranslation $translation */
            $translation = $claim->getTranslation();
            $claimsFormatted[] = $translation->getContent();
        }

        return $claimsFormatted;
    }
}
