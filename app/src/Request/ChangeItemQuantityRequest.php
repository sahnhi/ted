<?php

namespace App\Request;

use App\Business\Command\ChangeItemQuantity;
use Symfony\Component\HttpFoundation\Request;

final class ChangeItemQuantityRequest
{
    /**
     * @var string
     */
    private $token;

    private $id;

    /**
     * @var int
     */
    private $quantity;

    public function __construct(Request $request)
    {
        $this->token = $request->attributes->get('token');
        $this->id = $request->attributes->get('id');
        $this->quantity = $request->request->getInt('quantity');
    }

    /**
     * @return ChangeItemQuantity
     */
    public function getCommand()
    {
        return new ChangeItemQuantity($this->token, $this->id, $this->quantity);
    }
}
