<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Warehouse;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ExceptionalScheduleRepository extends EntityRepository implements RepositoryInterface
{
    public function getOneByWarehouseAndDate(Warehouse $warehouse, \DateTime $datetime)
    {
        $startDate = clone $datetime;
        $endDate = clone $datetime;

        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('es')
            ->where('es.warehouse = :warehouse')
            ->andWhere('es.start >= :startDate') // TODO: to correct when date is correct for es.end
            ->andWhere('es.start <= :endDate')
            ->setParameter('warehouse', $warehouse)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getNonCalendarUntilDate(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('es')
            ->where('es.end < :date')
            ->andWhere('es.calendarDays = :empty')
            ->setParameter('date', $date)
            ->setParameter('empty', serialize([]));

        return $qb->getQuery()->getResult();
    }
}
