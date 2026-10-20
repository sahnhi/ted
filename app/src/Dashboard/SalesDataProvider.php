<?php

declare(strict_types=1);

namespace App\Dashboard;

use App\Doctrine\Repository\Sylius\OrderRepository;
use Sylius\Component\Core\Dashboard\Interval;
use Sylius\Component\Core\Dashboard\SalesDataProviderInterface;
use Sylius\Component\Core\Dashboard\SalesSummary;
use Sylius\Component\Core\Dashboard\SalesSummaryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class SalesDataProvider implements SalesDataProviderInterface
{
    /**
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        private readonly RepositoryInterface $orderRepository
    ) {
    }

    public function getSalesSummary(
        ChannelInterface $channel,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        Interval $interval
    ): SalesSummaryInterface {
        $queryBuilder = $this->orderRepository->createQueryBuilder('o')
            ->select('SUM(o.total) AS total')
            ->andWhere('o.paymentState = :state')
            ->andWhere('o.channel = :channel')
            ->setParameter('state', OrderPaymentStates::STATE_PAID)
            ->setParameter('channel', $channel)
        ;

        switch ($interval->asString()) {
            case 'year':
                $queryBuilder
                    ->addSelect('YEAR(o.checkoutCompletedAt) as year')
                    ->groupBy('year')
                    ->andWhere('o.checkoutCompletedAt >= :startYear AND o.checkoutCompletedAt <= :endYear')
                    ->setParameter('startYear', $startDate->format('Y-m-d'))
                    ->setParameter('endYear', $endDate->format('Y-m-d'))
                ;
                $dateFormatter = static fn (\DateTimeInterface $date): string => $date->format('Y');
                $resultFormatter = static fn (array $data): string => $data['year'];

                break;
            case 'month':
                $queryBuilder
                    ->addSelect('YEAR(o.checkoutCompletedAt) as year')
                    ->addSelect('MONTH(o.checkoutCompletedAt) as month')
                    ->groupBy('year')
                    ->addGroupBy('month')
                    ->andWhere('o.checkoutCompletedAt >= :startYear AND o.checkoutCompletedAt <= :endYear')
                    ->setParameter('startYear', $startDate->format('Y-m-d'))
                    ->setParameter('endYear', $endDate->format('Y-m-d'))
                ;
                $dateFormatter = static fn (\DateTimeInterface $date): string => $date->format('n.Y');
                $resultFormatter = static fn (array $data): string => $data['month'] . '.' . $data['year'];

                break;
            case 'week':
                $queryBuilder
                    ->addSelect('YEAR(o.checkoutCompletedAt) as year')
                    ->addSelect('WEEK(o.checkoutCompletedAt) as week')
                    ->groupBy('year')
                    ->addGroupBy('week')
                    ->andWhere('o.checkoutCompletedAt >= :startYear AND o.checkoutCompletedAt <= :endYear')
                    ->setParameter('startYear', $startDate->format('Y-m-d'))
                    ->setParameter('endYear', $endDate->format('Y-m-d'))
                ;
                $dateFormatter = static fn (\DateTimeInterface $date): string => (ltrim($date->format('W'), '0') ?: '0') . ' ' . $date->format('Y');
                $resultFormatter = static fn (array $data): string => $data['week'] . ' ' . $data['year'];

                break;
            case 'day':
                $queryBuilder
                    ->addSelect('YEAR(o.checkoutCompletedAt) as year')
                    ->addSelect('MONTH(o.checkoutCompletedAt) as month')
                    ->addSelect('DAY(o.checkoutCompletedAt) as day')
                    ->groupBy('year')
                    ->addGroupBy('month')
                    ->addGroupBy('day')
                    ->andWhere('o.checkoutCompletedAt >= :startYear AND o.checkoutCompletedAt <= :endYear')
                    ->setParameter('startYear', $startDate->format('Y-m-d'))
                    ->setParameter('endYear', $endDate->format('Y-m-d'))
                ;
                $dateFormatter = static fn (\DateTimeInterface $date): string => $date->format('j.n.Y');
                $resultFormatter = static fn (array $data): string => $data['day'] . '.' . $data['month'] . '.' . $data['year'];

                break;
            default:
                throw new \RuntimeException(sprintf('Interval "%s" not supported.', $interval->asString()));
        }

        $ordersTotals = $queryBuilder->getQuery()->getArrayResult();

        $salesData = [];

        $period = new \DatePeriod($startDate, \DateInterval::createFromDateString(sprintf('1 %s', $interval->asString())), $endDate);
        foreach ($period as $date) {
            $salesData[$dateFormatter($date)] = 0;
        }

        foreach ($ordersTotals as $item) {
            $salesData[$resultFormatter($item)] = (int) $item['total'];
        }

        $salesData = array_map(
            static fn (int $total): string => number_format(abs($total / 100), 2, '.', ''),
            $salesData
        );

        return new SalesSummary($salesData);
    }
}
