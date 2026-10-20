<?php

declare(strict_types=1);

namespace App\Notification;

use App\Doctrine\Entity\Notification;
use App\PaymentReport\Repository\NotificationRepositoryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class SaveWebhookNotificationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if (!$message instanceof WebhookNotification) {
            return $stack->next()->handle($envelope, $stack);
        }

        $this->notificationRepository->save(new Notification($message->getType(), $message->getContent(), new \DateTimeImmutable()));

        return $stack->next()->handle($envelope, $stack);
    }
}
