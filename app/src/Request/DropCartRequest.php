<?php

namespace App\Request;

use App\Business\Command\DropCart;
use Symfony\Component\HttpFoundation\Request;

final class DropCartRequest
{
    /**
     * @var string
     */
    private $token;

    public function __construct(Request $request)
    {
        $this->token = $request->attributes->get('token');
    }

    /**
     * @return DropCart
     */
    public function getCommand()
    {
        return new DropCart($this->token);
    }
}
