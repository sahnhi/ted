<?php

namespace App\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class AdminProductStockFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (!empty($data['onHand']) && !empty($data['stockValue'])) {
            if ('=' === $data['onHand']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->equals('onHand', $data['stockValue']));
            } elseif ('>' === $data['onHand']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->greaterThan('onHand', $data['stockValue']));
            } elseif ('>=' === $data['onHand']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->greaterThanOrEqual('onHand', $data['stockValue']));
            } elseif ('<' === $data['onHand']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->lessThan('onHand', $data['stockValue']));
            } elseif ('<=' === $data['onHand']) {
                $dataSource->restrict($dataSource->getExpressionBuilder()->lessThanOrEqual('onHand', $data['stockValue']));
            }
        }
    }
}
