<?php

declare(strict_types=1);

namespace App\Channable\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class ChannableFileLogRepository extends EntityRepository
{
    public function findAllOrderedDesc(): iterable
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
