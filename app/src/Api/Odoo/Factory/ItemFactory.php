<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\Item;
use App\Api\Odoo\Model\ItemInterface;
use App\Provider\TaxRateProviderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

class ItemFactory implements ItemFactoryInterface
{
    public function __construct(
        private readonly TaxRateProviderInterface $taxRateProvider
    ) {
    }

    public function createNew(OrderItemInterface $syliusItem): ItemInterface
    {
        return new Item($syliusItem, $this->taxRateProvider->provideTaxRateFromZone($syliusItem->getOrder()));
    }
}
