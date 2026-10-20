<?php

namespace App\Services\Mail;

interface MailerEmailInterface
{
    public const TYPE_ORDER_CONFIRMATION_EMAIL = 'order_confirmation_email';
    public const TYPE_QUOTATION_EMAIL = 'quotation_email';
    public const TYPE_RETURNING_PRODUCTS_EMAIL = 'returning_products_email';
    public const TYPE_REVIEW_EMAIL = 'review_email';
    public const TYPE_TRACKING_EMAIL = 'tracking_email';
    public const TYPE_LABEL_EMAIL = 'label_email';
    public const TYPE_PRODUCT_RETURN_EMAIL = 'product_return_email';
    public const TYPE_REFUND_CONFIRMATION_EMAIL = 'refund_confirmation_email';
    public const TYPE_RETURN_RECEPTION_EMAIL = 'return_reception_email';
    public const TYPE_GIFT_CARD_EMAIL = 'gift_card_email';
    public const TYPE_OUT_OF_STOCK_CUSTOMER_EMAIL = 'out_of_stock_customer_email';
}
