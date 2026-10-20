<?php

namespace App\MessageHandler;

use App\Message\Drip;
use App\Services\Drip\DripApiInterface;

class DripHandler
{
    public function __construct(
        private readonly DripApiInterface $dripApi
    ) {
    }

    public function __invoke(Drip $message): void
    {
        $this->dripApi->request($message->getApiUrl(), $message->getPost());
    }
}
