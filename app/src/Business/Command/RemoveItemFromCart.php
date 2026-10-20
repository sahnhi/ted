<?php

namespace App\Business\Command;

use Webmozart\Assert\Assert;

final class RemoveItemFromCart
{
    /**
     * @var string
     */
    private $orderToken;

    public function __construct(
        string $orderToken,
        private $itemIdentifier
    ) {
        Assert::string($orderToken, 'Expected order token to be string, got %s');
        $this->orderToken = $orderToken;
    }

    public function orderToken(): string
    {
        return $this->orderToken;
    }

    public function itemIdentifier()
    {
        return $this->itemIdentifier;
    }
}
