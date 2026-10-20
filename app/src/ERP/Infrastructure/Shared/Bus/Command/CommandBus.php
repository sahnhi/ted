<?php

declare(strict_types=1);

namespace App\ERP\Infrastructure\Shared\Bus\Command;

use App\ERP\Application\Command\CommandBusInterface;
use App\ERP\Infrastructure\Shared\Bus\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus implements CommandBusInterface
{
    use MessageBusExceptionTrait;

    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /** @throws \Throwable */
    public function handle(CommandInterface $command): void
    {
        try {
            $this->messageBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            $this->throwException($e);
        }
    }
}
