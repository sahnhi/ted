<?php

namespace App\Api\Odoo\Model;

interface PromotionInterface extends ObjectMessageOdooInterface
{
    public function __construct(string $name);
}
