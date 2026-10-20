<?php

declare(strict_types=1);

namespace App\ApiPlatform\Extension\ProductReview;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\Entity\Sylius\ProductReview;
use Doctrine\ORM\QueryBuilder;

class EmptyCommentAndTitleQueryCollectionExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (ProductReview::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0] ?? 'o';

        $expr = $queryBuilder->expr();

        $queryBuilder
            ->andWhere(
                $expr->andX(
                    $expr->isNotNull($alias . '.comment'),
                    $expr->neq($alias . '.comment', "''"),
                )
            )
            ->andWhere(
                $expr->andX(
                    $expr->isNotNull($alias . '.title'),
                    $expr->neq($alias . '.title', "''"),
                )
            );
    }
}
