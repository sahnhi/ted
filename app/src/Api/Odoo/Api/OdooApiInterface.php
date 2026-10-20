<?php

namespace App\Api\Odoo\Api;

use App\Api\Odoo\Model\ObjectMessageOdooInterface;

interface OdooApiInterface
{
    public const API_CALL_DEFAULT = 'Default';

    public const API_CALL_NEW_ORDER = 'new';
    public const API_CALL_STOCK = 'shipping/new';
    public const API_CALL_RETURN = 'return/new';
    public const API_CALL_SHIPPED = 'shipping/confirm';
    public const API_CALL_CONFIRM_RETURN = 'return/confirm';
    public const API_CALL_CANCEL = 'delete';

    public const API_SUCCESS_MESSAGE = '<info>Odoo call : done !</info>';
    public const INTERNAL_FAILURE_MESSAGE = '<error>Odoo call : internal failure !</info>';

    public function request(?ObjectMessageOdooInterface $subject, string $apiCall, string $method = 'post'): string;
}
