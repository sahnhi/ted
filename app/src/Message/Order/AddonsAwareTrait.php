<?php

declare(strict_types=1);

namespace App\Message\Order;

trait AddonsAwareTrait
{
    public function __construct(
        private readonly array $addons = [],
    ) {
    }

    public function getAddons(): array
    {
        return $this->addons;
    }
}
