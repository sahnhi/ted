<?php

namespace App\MessageHandler;

use App\Api\Shipup\ShipupApi;
use App\Message\Shipup;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ShipupHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ShipupApi $api
    ) {
    }

    public function __invoke(Shipup $message): void
    {
        $this->api->request($message->getSubject(), $message->getPath(), $message->getMethod());
    }
}
