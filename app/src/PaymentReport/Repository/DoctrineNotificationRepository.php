<?php

declare(strict_types=1);

namespace App\PaymentReport\Repository;

use App\Doctrine\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineNotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Notification $notification)
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
