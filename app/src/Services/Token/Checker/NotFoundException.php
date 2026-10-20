<?php

namespace App\Services\Token\Checker;

class NotFoundException extends \Exception
{
    public function __construct(string $checker)
    {
        parent::__construct('Token checker not found for type : ' . $checker);
    }
}
