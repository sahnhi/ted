<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class ProductVariantExists extends Constraint
{
    /**
     * @var string
     */
    public $message = 'sylius.shop_api.product.configurable';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'sylius_shop_api_configurable_product_validator';
    }
}
