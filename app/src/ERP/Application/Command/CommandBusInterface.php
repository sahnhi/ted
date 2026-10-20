<?php

namespace App\ERP\Application\Command;

use App\ERP\Infrastructure\Shared\Bus\Command\CommandInterface;

interface CommandBusInterface
{
    public function handle(CommandInterface $command): void;
}
