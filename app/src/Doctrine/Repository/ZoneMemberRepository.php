<?php

namespace App\Doctrine\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Addressing\Model\ZoneMemberInterface;
use Sylius\Component\Core\Model\Scope;

class ZoneMemberRepository extends EntityRepository
{
    /**
     * @return ($findOne is true ? ZoneMemberInterface : array<ZoneMemberInterface>)|null
     *
     * @throws NonUniqueResultException
     */
    public function getZoneMemberByCode(string $cp, $findOne = false): ZoneMemberInterface|array|null
    {
        $query = $this->createQueryBuilder('z')
            ->innerJoin('z.belongsTo', 'zone')
            ->andWhere('z.code = :cp')
            ->andWhere('zone.scope = :scope')
            ->setParameter('cp', $cp)
            ->setParameter('scope', Scope::SHIPPING);

        if (true === $findOne) {
            try {
                $query->andWhere('z.code != zone.code');
                $query->andWhere('zone.type = :type');
                $query->setParameter('type', 'country');

                return $query->getQuery()->getOneOrNullResult()->getBelongsTo();
            } catch (\Exception) {
                return null;
            }
        } else {
            return $query->getQuery()->getResult();
        }
    }
}
