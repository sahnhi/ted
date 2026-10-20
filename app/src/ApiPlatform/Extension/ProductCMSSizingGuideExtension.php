<?php

declare(strict_types=1);

namespace App\ApiPlatform\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\Entity\ProductCMSSizingGuide;
use Doctrine\ORM\QueryBuilder;

final class ProductCMSSizingGuideExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operationName = null, array $context = []): void
    {
        if (ProductCMSSizingGuide::class === $resourceClass) {
            $this->addWhere($queryBuilder);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operationName = null, array $context = []): void
    {
        if (ProductCMSSizingGuide::class === $resourceClass) {
            $this->addWhere($queryBuilder);
        }
    }

    private function addWhere(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->andWhere('o.enabled = true');
    }
}
