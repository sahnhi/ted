<?php

declare(strict_types=1);

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use JMS\Serializer\Annotation as Serializer;

class ShipmentItem implements ObjectMessageOdooInterface, OrderItem
{
    public function __construct(
        /**
         * @Serializer\Type("integer")
         *
         * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
         *
         * @Serializer\SerializedName("unit_price_with_tax")
         */
        public int $priceTTC,
        /**
         * @Serializer\Type("integer")
         *
         * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
         *
         * @Serializer\SerializedName("discounted_unit_price_with_tax")
         */
        public int $discountedPriceTTC,
        /**
         * @Serializer\Type("integer")
         *
         * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
         *
         * @Serializer\SerializedName("ecotax_amount")
         */
        public int $ecotax,
        public int $taxes,
        /**
         * @Serializer\Type("integer")
         *
         * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
         *
         * @Serializer\SerializedName("total_price_without_tax")
         */
        public int $totalHT,
        /**
         * @Serializer\Type("integer")
         *
         * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
         *
         * @Serializer\SerializedName("total_price_with_tax")
         */
        public int $totalTTC,
        /**
         * @Serializer\Type("string")
         *
         * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
         *
         * @Serializer\SerializedName("tax_code")
         */
        public string $vat,
        /**
         * @Serializer\Type("integer")
         */
        public int $qty = 1,
        /**
         * @Serializer\Type("string")
         */
        public string $sku = 'Frais_livraison',
        /**
         * @Serializer\Type("string")
         */
        public string $label = 'Frais de livraison'
    ) {
    }
}
