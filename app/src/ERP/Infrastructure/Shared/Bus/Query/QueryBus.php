<?php

declare(strict_types=1);

namespace App\ERP\Infrastructure\Shared\Bus\Query;

use App\ERP\Application\Query\QueryBusInterface;
use App\ERP\Infrastructure\Shared\Bus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class QueryBus implements QueryBusInterface
{
    use MessageBusExceptionTrait;

    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function handle(QueryInterface $query): mixed
    {
        try {
            $envelope = $this->messageBus->dispatch($query);

            /** @var HandledStamp $stamp */
            $stamp = $envelope->last(HandledStamp::class);

            return $stamp->getResult();
        } catch (HandlerFailedException $e) {
            $this->throwException($e);
        }

        return null;
    }
}
