<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use JMS\Serializer\Annotation as Serializer;

class Promotion implements PromotionInterface
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_NEW_ORDER})
     */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
