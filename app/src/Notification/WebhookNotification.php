<?php

declare(strict_types=1);

namespace App\Notification;

interface WebhookNotification
{
    public function getType(): string;

    public function getContent(): string;
}
