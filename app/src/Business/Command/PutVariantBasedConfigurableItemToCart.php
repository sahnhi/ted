<?php

namespace App\Business\Command;

use Webmozart\Assert\Assert;

final class PutVariantBasedConfigurableItemToCart
{
    /**
     * @var string
     */
    private $channelCode;

    /**
     * @var string
     */
    private $orderToken;
    /**
     * @var string
     */
    private $product;
    /**
     * @var int
     */
    private $quantity;

    /**
     * @var string
     */
    private $productVariant;

    public function __construct(string $channelCode, string $orderToken, string $product, string $productVariant, int $quantity)
    {
        Assert::string($channelCode, 'Expected channel code to be string, got %s');
        Assert::string($orderToken, 'Expected order token to be string, got %s');
        Assert::string($product, 'Expected product code to be string, got %s');
        Assert::string($productVariant, 'Expected product variant code to be string, got %s');
        Assert::integer($quantity, 'Expected quantity to be integer, got %s');
        Assert::greaterThan($quantity, 0, 'Quantity should be greater than 0');

        $this->channelCode = $channelCode;
        $this->orderToken = $orderToken;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->productVariant = $productVariant;
    }

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    /**
     * @return string
     */
    public function orderToken()
    {
        return $this->orderToken;
    }

    /**
     * @return string
     */
    public function product()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function productVariant()
    {
        return $this->productVariant;
    }

    /**
     * @return int
     */
    public function quantity()
    {
        return $this->quantity;
    }
}
