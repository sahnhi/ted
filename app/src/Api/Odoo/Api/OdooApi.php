<?php

namespace App\Api\Odoo\Api;

use App\Api\Odoo\Model\ObjectMessageOdooInterface;

class OdooApi implements OdooApiInterface
{
    public function request(?ObjectMessageOdooInterface $subject, string $apiCall, string $method = 'post'): string
    {
        // As a hotfix to disable Odoo, we just never send the actual request to Odoo for an order creation
        // In the future, hopefully, all the Odoo classes about sending orders to Odoo will be deleted
        return self::API_SUCCESS_MESSAGE;
    }
}
