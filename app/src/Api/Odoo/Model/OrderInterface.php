<?php

namespace App\Api\Odoo\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

interface OrderInterface extends ObjectMessageOdooInterface
{
    public const LEGACY_ODOO_STATE_NOT_READY = -1;
    public const LEGACY_ODOO_STATE_READY = 0;
    public const LEGACY_ODOO_STATE_CREATED = 1;
    public const LEGACY_ODOO_STATE_SHIPPING = 2;
    public const LEGACY_ODOO_STATE_SHIPPED = 3;
    public const LEGACY_ODOO_STATE_REFUNDED = 4;
    public const LEGACY_ODOO_STATE_CANCELLED = 5;
    public const ODOO_STATE_NOT_READY = 'not_ready';
    public const ODOO_STATE_READY = 'ready';
    public const ODOO_STATE_CREATED = 'created';
    public const ODOO_STATE_DESTOCKED = 'destocked';
    public const ODOO_STATE_SHIPPED = 'shipped';
    public const ODOO_STATE_REFUNDED = 'refunded';
    public const ODOO_STATE_CANCELLED = 'cancelled';
    public const ODOO_TRANSITION_CREATE = 'create';
    public const ODOO_TRANSITION_CREATE_RETURN = 'create_return';
    public const ODOO_TRANSITION_DESTOCK = 'destock';
    public const ODOO_TRANSITION_SHIP = 'ship';
    public const ODOO_TRANSITION_CONFIRM_RETURN = 'confirm_return';
    public const ODOO_TRANSITION_CANCEL = 'cancel';
    public const PAYMENT_CODE_FREE = 'offert';
    public const GRAPH = 'sylius_order_odoo';
    public const ODOO_RETURN_CODE_RGC_ID = '17';
    public const ODOO_RETURN_CODE_RR_ID = '5';
    public const ODOO_RETURN_CODE_RGC = 'RGC';
    public const ODOO_RETURN_CODE_RR = 'RR';

    public function __construct(
        BaseOrderInterface $order,
        ArrayCollection $items,
        ?AddressInterface $shippingAddress = null,
        ?AddressInterface $invoicingAddress = null,
        ?DeliveryDateInterface $deliveryDate = null,
        ?Customer $customer = null,
        ?string $paymentId = null,
        ?string $taxRateCode = null
    );
}
