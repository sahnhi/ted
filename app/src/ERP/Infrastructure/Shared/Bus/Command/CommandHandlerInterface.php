<?php

declare(strict_types=1);

namespace App\ERP\Infrastructure\Shared\Bus\Command;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

interface CommandHandlerInterface extends MessageHandlerInterface
{
}
