<?php

declare(strict_types=1);

namespace App\MessageHandler\Order;

use App\Business\Command\SaveOrderItemReason;
use App\Calendar\Command\InsertItemsReasonForAutonomousReturn;
use App\Services\Token\Checker\Autonomous;
use App\Services\Token\TokenException;
use App\Services\Token\TokenService;
use League\Tactician\CommandBus;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class InsertItemsReasonForAutonomousReturnHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly TokenService $tokenService,
        private readonly CommandBus $commandBus
    ) {
    }

    public function __invoke(InsertItemsReasonForAutonomousReturn $command): void
    {
        $token = $command->getOrderTokenValue();

        try {
            $this->tokenService->checkToken(Autonomous::class, $token, $command->getEmail());
        } catch (TokenException $e) {
            throw new UnauthorizedHttpException('Basic', $e->getMessage());
        }

        $this->commandBus->handle(SaveOrderItemReason::saveReasons($command, $token));
    }
}
