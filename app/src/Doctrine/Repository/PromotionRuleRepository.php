<?php

namespace App\Doctrine\Repository;

use App\Promotion\Checker\LingotRuleChecker;
use App\Promotion\Checker\Rule\SponsoringRuleChecker;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Promotion\Model\PromotionRuleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class PromotionRuleRepository extends EntityRepository implements RepositoryInterface
{
    public function findOneByNthFilleul(int $nth): ?PromotionRuleInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.configuration LIKE :nth')
            ->andWhere('o.type = :type')
            ->setParameter('nth', '%"nth";i:' . $nth . ';%')
            ->setParameter('type', SponsoringRuleChecker::TYPE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByLingot(string $lingot): ?PromotionRuleInterface
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.configuration LIKE :lingot')
            ->andWhere('r.type = :type')
            ->setParameter('type', LingotRuleChecker::TYPE)
            ->setParameter('lingot', '%"dripCode";s:' . strlen($lingot) . ':"' . $lingot . '"%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
