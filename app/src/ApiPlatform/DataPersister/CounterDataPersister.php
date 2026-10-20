<?php

declare(strict_types=1);

namespace App\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Doctrine\Entity\Counter;
use Doctrine\ORM\EntityManagerInterface;

class CounterDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Counter && 'counter_put' === $context['item_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        /* @var Counter $data */
        $data->incrementCounter();
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    public function remove($data, array $context = [])
    {
    }
}
