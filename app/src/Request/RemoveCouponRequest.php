<?php

namespace App\Request;

use App\Business\Command\RemoveCoupon;
use Symfony\Component\HttpFoundation\Request;

final class RemoveCouponRequest
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
     * @return RemoveCoupon
     */
    public function getCommand()
    {
        return new RemoveCoupon($this->token);
    }
}
