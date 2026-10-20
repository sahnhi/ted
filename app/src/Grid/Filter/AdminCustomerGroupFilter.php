<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class AdminCustomerGroupFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (!empty($data['customer_group'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('group.code', $data['customer_group']));
        }
    }
}
