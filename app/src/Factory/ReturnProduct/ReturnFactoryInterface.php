<?php

namespace App\Factory\ReturnProduct;

interface ReturnFactoryInterface
{
    public function createNewFromExisting($original);
}
