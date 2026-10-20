<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Command;

use App\Doctrine\Entity\Notification;
use App\Notification\WebhookNotification;

final class ShipupWebhookCommand implements WebhookNotification
{
    private string $type = Notification::SHIPUP_TRACK_EVENT;

    public function __construct(
        private readonly string $content
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
