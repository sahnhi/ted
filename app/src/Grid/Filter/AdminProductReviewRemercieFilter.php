<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class AdminProductReviewRemercieFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (isset($data['remercie'])) {
            if ('1' === $data['remercie']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->equals('remercie', true));
            } elseif ('0' === $data['remercie']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->isNull('remercie'));
            }
        }
    }
}
