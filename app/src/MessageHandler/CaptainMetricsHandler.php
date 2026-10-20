<?php

namespace App\MessageHandler;

use App\Message\CaptainMetrics;
use App\Services\CaptainMetrics\CaptainMetricsApiInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CaptainMetricsHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly CaptainMetricsApiInterface $captainMetricsApi
    ) {
    }

    public function __invoke(CaptainMetrics $message)
    {
        $this->captainMetricsApi->request($message->getPost(), $message->getTag());
    }
}
