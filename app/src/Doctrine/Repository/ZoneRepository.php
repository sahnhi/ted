<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Sylius\Zone;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ZoneRepository extends EntityRepository
{
    /**
     * @throws NonUniqueResultException
     */
    public function findZoneByCpAndLocal(string $cp, string $local): Zone|null
    {
        return $this->createQueryBuilder('z')
            ->innerJoin('z.zipCode', 'zipcode')
            ->andWhere('zipcode.code = :cp')
            ->andWhere('zipcode.country = :local')
            ->setParameter('cp', $cp, ParameterType::STRING)
            ->setParameter('local', $local, ParameterType::STRING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
