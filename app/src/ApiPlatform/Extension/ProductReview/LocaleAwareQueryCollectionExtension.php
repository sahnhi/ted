<?php

declare(strict_types=1);

namespace App\ApiPlatform\Extension\ProductReview;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\Entity\Sylius\ProductReview;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;

class LocaleAwareQueryCollectionExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (ProductReview::class !== $resourceClass || !isset($context[ContextKeys::LOCALE_CODE])) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0] ?? 'o';

        $queryBuilder
            ->andWhere(sprintf('%s.locale = :locale', $alias))
            ->setParameter('locale', $context[ContextKeys::LOCALE_CODE]);
    }
}
