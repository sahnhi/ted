<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class MultipleEntityFilter implements FilterInterface
{
    public const FILTER_CONTAINS_VARIANT = 'contains_variant';
    public const FILTER_NOT_CONTAINS_VARIANT = 'not_contains_variant';

    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        $expressionBuilder = $dataSource->getExpressionBuilder();

        $expressions = [];

        $dataSource->getQueryBuilder()->setParameter($name, $data);

        $dataSource->restrict($expressionBuilder->orX(...$expressions));
    }
}
