<?php

namespace App\Business\Command;

use Webmozart\Assert\Assert;

final class ChangeItemQuantity
{
    /**
     * @var string
     */
    private $orderToken;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param string $orderToken
     * @param int    $quantity
     */
    public function __construct(
        $orderToken,
        private $itemIdentifier,
        $quantity
    ) {
        Assert::string($orderToken, 'Expected order token to be string, got %s');
        Assert::integer($quantity, 'Expected quantity to be integer, got %s');
        Assert::greaterThan($quantity, 0, 'Quantity should be greater than 0');
        $this->orderToken = $orderToken;
        $this->quantity = $quantity;
    }

    public function orderToken(): string
    {
        return $this->orderToken;
    }

    public function itemIdentifier()
    {
        return $this->itemIdentifier;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
