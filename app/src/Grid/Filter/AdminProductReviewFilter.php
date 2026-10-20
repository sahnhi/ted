<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class AdminProductReviewFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (!empty($data['rating'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('rating', $data['rating']));
        }

        if (!empty($data['status'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('status', $data['status']));
        }

        if (!empty($data['locale'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('locale', $data['locale']));
        }
    }
}
