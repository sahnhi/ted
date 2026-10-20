<?php

declare(strict_types=1);

namespace App\Exception\Api\Cart;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidCouponCode extends BadRequestHttpException implements \Throwable
{
}
