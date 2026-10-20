<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Lock;

use Predis\Client;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

final class BlockingLockFactory extends LockFactory
{
    public function __construct(string $redisDsn)
    {
        $store = new RedisStore(new Client($redisDsn));
        $store = new RetryTillSaveStore($store);

        parent::__construct($store);
    }
}
