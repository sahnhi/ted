<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Controller;

use App\Api\ChronoPost\Command\ShipupWebhookCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

final class GetNotification
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly Security $security
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_API')) {
            return new JsonResponse(
                ['code' => Response::HTTP_FORBIDDEN, 'message' => Response::$statusTexts[Response::HTTP_FORBIDDEN]],
                Response::HTTP_FORBIDDEN
            );
        }

        $this->bus->dispatch(new ShipupWebhookCommand($request->getContent()));

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
