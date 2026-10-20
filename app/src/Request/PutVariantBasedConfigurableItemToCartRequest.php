<?php

namespace App\Request;

use App\Business\Command\PutVariantBasedConfigurableItemToCart;
use Symfony\Component\HttpFoundation\Request;

final class PutVariantBasedConfigurableItemToCartRequest
{
    private function __construct(
        /**
         * @var string
         */
        private readonly ?string $channelCode,
        /**
         * @var string
         */
        private readonly ?string $token,
        /**
         * @var string
         */
        private readonly ?string $productCode,
        /**
         * @var string
         */
        private readonly ?string $variantCode,
        /**
         * @var int
         */
        private readonly ?int $quantity
    ) {
    }

    public static function fromArray(array $item): PutVariantBasedConfigurableItemToCartRequest
    {
        return new self($item['channelCode'] ?? null, $item['token'] ?? null, $item['productCode'] ?? null, $item['variantCode'] ?? null, $item['quantity'] ?? null);
    }

    public static function fromRequest(Request $request): PutVariantBasedConfigurableItemToCartRequest
    {
        return new self($request->attributes->get('channelCode'), $request->attributes->get('token'), $request->request->get('productCode'), $request->request->get('variantCode'), $request->request->getInt('quantity', 1));
    }

    /**
     * @return PutVariantBasedConfigurableItemToCart
     */
    public function getCommand()
    {
        return new PutVariantBasedConfigurableItemToCart($this->channelCode, $this->token, $this->productCode, $this->variantCode, $this->quantity);
    }
}
