<?php

namespace App\Request;

use App\Business\Command\PutSimpleItemToCart;
use Symfony\Component\HttpFoundation\Request;

final class PutSimpleItemToCartRequest
{
    /**
     * PutSimpleItemToCartRequest constructor.
     *
     * @param string $token
     * @param string $productCode
     * @param int    $quantity
     */
    private function __construct(
        private $token,
        private $productCode,
        private $quantity
    ) {
    }

    public static function fromArray(array $item): PutSimpleItemToCartRequest
    {
        return new self($item['token'] ?? null, $item['productCode'] ?? null, $item['quantity'] ?? null);
    }

    public static function fromRequest(Request $request): PutSimpleItemToCartRequest
    {
        return new self($request->attributes->get('token'), $request->request->get('productCode'), $request->request->getInt('quantity', 1));
    }

    /**
     * @return PutSimpleItemToCart
     */
    public function getCommand()
    {
        return new PutSimpleItemToCart($this->token, $this->productCode, $this->quantity);
    }
}
