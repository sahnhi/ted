<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Pack;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ChannelInterface;

class PackRepository extends EntityRepository implements PackRepositoryInterface
{
    public function findOneBySlug(ChannelInterface $channel, string $localeCode, string $slug): ?Pack
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('translation')
            ->innerJoin('p.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere('p.code = :code')
            ->setParameter('locale', $localeCode)
            ->setParameter('code', $slug);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllByVariant(ChannelInterface $channel, string $localeCode, string $slug): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('translation')
            ->innerJoin('p.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->innerJoin('p.variants', 'variants', 'WITH', 'variants.code = :variant')
            ->setParameter('locale', $localeCode)
            ->setParameter('variant', $slug);

        return $qb->getQuery()->getResult();
    }

    public function findAllUpsell(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.code LIKE :code')
            ->setParameter('code', 'upsell_%');

        return $qb->getQuery()->getResult();
    }
}
