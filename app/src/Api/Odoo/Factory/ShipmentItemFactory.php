<?php

declare(strict_types=1);

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\ShipmentItem;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Provider\TaxRateProviderInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;

class ShipmentItemFactory implements ShipmentItemFactoryInterface
{
    public function __construct(
        private readonly TaxRateProviderInterface $taxRateProvider
    ) {
    }

    public function createNew(OrderInterface $syliusOrder): ShipmentItem
    {
        $shipment = $syliusOrder->getShipments()->first();
        $taxTotal = 0;

        foreach ($shipment->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT) as $adjustment) {
            $taxTotal += $adjustment->getAmount();
        }

        return new ShipmentItem(
            priceTTC: $syliusOrder->getShippingTotal(),
            discountedPriceTTC: $syliusOrder->getShippingTotal(),
            ecotax: 0,
            taxes: $taxTotal,
            totalHT: $syliusOrder->getShippingTotal() - $taxTotal,
            totalTTC: $syliusOrder->getShippingTotal(),
            vat: $this->taxRateProvider->provideTaxRateFromZone($syliusOrder)->getCode(),
            qty: 1,
            sku: 'Frais de livraison',
            label: 'Frais de livraison'
        );
    }
}
