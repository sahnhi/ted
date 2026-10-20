<?php

namespace App\View;

class CartSummaryView
{
    public string $tokenValue;

    public string|null $number = null;

    public string $channel;

    public string $currency;

    public string $locale;

    public string $checkoutState;

    public \DateTimeInterface|null $checkoutCompletedAt = null;

    /**
     * @var ItemView[]
     */
    public array $items = [];

    public TotalsView $totals;

    public AddressView $shippingAddress;

    public AddressView $billingAddress;

    public CustomerView $customer;

    /**
     * @var PaymentView[]
     */
    public array $payments = [];

    /**
     * @var ShipmentView[]
     */
    public array $shipments = [];

    /**
     * @var AdjustmentView[]
     */
    public array $cartDiscounts = [];

    public string|null $lingotMessage = null;

    /**
     * @var RelatedProductView[]
     */
    public array $relatedProducts = [];

    public array $gamification = [];

    public function __construct()
    {
        $this->totals = new TotalsView();
    }
}
