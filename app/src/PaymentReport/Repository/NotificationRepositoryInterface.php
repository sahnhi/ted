<?php

namespace App\PaymentReport\Repository;

use App\Doctrine\Entity\Notification;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification);
}
