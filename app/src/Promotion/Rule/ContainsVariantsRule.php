<?php

declare(strict_types=1);

namespace App\Promotion\Rule;

use Doctrine\ORM\QueryBuilder;
use Setono\SyliusCatalogPromotionPlugin\Rule\Rule;
use Webmozart\Assert\Assert;

class ContainsVariantsRule extends Rule
{
    public const TYPE = 'contains_variant';

    public function filter(QueryBuilder $queryBuilder, array $configuration): void
    {
        $value = self::getConfigurationValue('variants', $configuration);
        Assert::isArray($value);

        $rootAlias = $this->getRootAlias($queryBuilder);
        $parameter = self::generateParameter('variant_codes');

        $queryBuilder
            ->andWhere(sprintf('%s.code IN (:%s)', $rootAlias, $parameter))
            ->setParameter($parameter, $value);
    }
}
