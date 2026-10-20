<?php

namespace App\Request;

use App\Business\Command\RemoveItemFromCart;
use Symfony\Component\HttpFoundation\Request;

final class RemoveItemFromCartRequest
{
    /**
     * @var string
     */
    private $token;

    private $id;

    public function __construct(Request $request)
    {
        $this->token = $request->attributes->get('token');
        $this->id = $request->attributes->get('id');
    }

    public function getCommand(): RemoveItemFromCart
    {
        return new RemoveItemFromCart($this->token, $this->id);
    }
}
