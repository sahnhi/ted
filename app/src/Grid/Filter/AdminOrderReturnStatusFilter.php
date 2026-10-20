<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class AdminOrderReturnStatusFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (!empty($data['savStatus'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('orderReturn.savStatus', $data['savStatus']));
        }
        if (!empty($data['warehouseStatus'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('orderReturn.warehouseStatus', $data['warehouseStatus']));
        }
    }
}
