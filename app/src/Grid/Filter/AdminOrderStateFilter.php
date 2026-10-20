<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class AdminOrderStateFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (!empty($data['state'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('state', $data['state']));
        }

        if (!empty($data['payment_state'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('paymentState', $data['payment_state']));
        }

        if (!empty($data['shipping_state'])) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('shippingState', $data['shipping_state']));
        }
    }
}
