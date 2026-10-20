<?php

declare(strict_types=1);

namespace App\Enum;

enum EmailConfigurationName: string
{
    case SUPPORT = 'support';
    case DEV = 'dev';
    case SAV = 'sav';
    case REVIEW_EXPORT = 'review_export';
    case THANKS_EXPORT = 'thanks_export';
    case AUTOMATIC_SLOT_CLOSING = 'automatic_slot_closing';
    case PRODUCT_CHANGE = 'product_change';
    case INFLUENCER_ORDER = 'influencer_order';
    case TRANSMISSION_PROBLEM = 'transmission_problem';
    case INTERNATIONAL_RETURN = 'international_return';
    case ORDER_INTEGRATION_ERROR = 'order_integration_error';
    case BLOCKED_ORDER = 'blocked_order';
    case UPDATE_ORDER_DELIVERY_DATE = 'update_order_delivery_date';
    case VARIANT_DISABLED_EMAIL = 'variant_disabled_email';
    case SPIDY_EDI = 'spidy_edi';
    case PRINTEMPS_PAYMENT_CAPTURE = 'printemps_payment_capture';
    case PARTIALLY_PAID = 'partially_paid';
    case SENDER_RETURN = 'sender_return';
}
