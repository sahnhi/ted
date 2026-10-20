<?php

namespace App\Handler;

use App\Business\Command\ChangeItemQuantity;

interface ChangeItemQuantityHandlerInterface
{
    public function handle(ChangeItemQuantity $changeItemQuantity): void;
}
