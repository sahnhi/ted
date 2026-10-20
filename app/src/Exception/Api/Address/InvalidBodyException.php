<?php

declare(strict_types=1);

namespace App\Exception\Api\Address;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidBodyException extends BadRequestHttpException implements \Throwable
{
}
