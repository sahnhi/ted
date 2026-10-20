<?php

namespace App\Request;

use App\Business\Command\PutOptionBasedConfigurableItemToCart;
use Symfony\Component\HttpFoundation\Request;

final class PutOptionBasedConfigurableItemToCartRequest
{
    /**
     * @param string       $token
     * @param string       $productCode
     * @param mixed[]|null $options
     * @param int          $quantity
     */
    private function __construct(
        private $token,
        private $productCode,
        private $options,
        private $quantity
    ) {
    }

    public static function fromArray(array $item): PutOptionBasedConfigurableItemToCartRequest
    {
        return new self($item['token'] ?? null, $item['productCode'] ?? null, $item['options'] ?? null, $item['quantity'] ?? null);
    }

    public static function fromRequest(Request $request): PutOptionBasedConfigurableItemToCartRequest
    {
        return new self($request->attributes->get('token'), $request->request->get('productCode'), $request->request->get('options'), $request->request->getInt('quantity', 1));
    }

    /**
     * @return PutOptionBasedConfigurableItemToCart
     */
    public function getCommand()
    {
        return new PutOptionBasedConfigurableItemToCart($this->token, $this->productCode, $this->options, $this->quantity);
    }
}
