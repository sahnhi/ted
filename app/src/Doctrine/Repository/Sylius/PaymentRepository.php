<?php

namespace App\Doctrine\Repository\Sylius;

use App\Api\Odoo\Model\OrderInterface;
use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\Fastmag;
use App\Services\Channel\ChannelCode;
use App\Utils\DateRange;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentRepository as BasePaymentRepository;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;

/**
 * @method PaymentInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentInterface|null findOneById(int $id)
 * @method PaymentInterface|null findOneByName(string $name)
 * @method PaymentInterface[]    findAll()
 * @method PaymentInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends BasePaymentRepository implements PaymentRepositoryInterface
{
    public function findByLikeRequestIdAndAmountInDetails($requestId, $amount): ?PaymentInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.details LIKE :requestId')
            ->andWhere('o.details LIKE :amount')
            ->setParameter('requestId', '%\"REQUESTID\":\"' . $requestId . '\"%')
            ->setParameter('amount', '%\"AMOUNT\":\"' . $amount . '\"%')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOrderNumberAndCompletedState($orderNumber): ?PaymentInterface
    {
        return $this->createQueryBuilder('p')
            ->join('p.order', 'o')
            ->andWhere('o.number = :orderNumber')
            ->andWhere('p.state = :paymentState')
            ->setParameter('paymentState', OrderCheckoutStates::STATE_COMPLETED)
            ->setParameter('orderNumber', $orderNumber)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByRefundReferenceIdIsNull($orderNumber, $paymentId, $amount)
    {
        return $this->createQueryBuilder('p')
            ->join('p.order', 'o')
            ->andWhere('o.shippingState IN (:shippingStates)')
            ->andWhere('p.amount = :amount')
            ->andWhere('p.details LIKE :orderNumber')
            ->andWhere('p.details LIKE :paymentId')
            ->andWhere('p.details LIKE :refundIsNull')
            ->setParameter('shippingStates', [
                OrderShippingStates::STATE_SHIPPED,
                OrderShippingStates::STATE_DELIVERED,
            ])
            ->setParameter('amount', $amount)
            ->setParameter('orderNumber', '%\"ORDER_NUMBER\":\"' . $orderNumber . '\"%')
            ->setParameter('paymentId', '%\"PAYMENT_ID\":\"' . $paymentId . '\"%')
            ->setParameter('refundIsNull', '%\"REFUND\":null%')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOrderToken($token)
    {
        return $this->createQueryBuilder('p')
            ->join('p.order', 'o')
            ->andWhere('o.tokenValue = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getResult();
    }

    public function findStorePaymentsOfDay($store)
    {
        $date = new \DateTime();

        return $this->createQueryBuilder('p')
            ->join('p.order', 'o')
            ->join('o.channel', 'c')
            ->join('o.orderStore', 'os')
            ->join('os.store', 's')
            ->andWhere('s.id = :storeId')
            ->andWhere('c.code = :store')
            ->andWhere('o.checkoutCompletedAt LIKE :date')
            ->andWhere('p.state = :completed')
            ->setParameter('date', '%' . $date->format('Y-m-d') . '%')
            ->setParameter('store', 'boutique')
            ->setParameter('completed', 'completed')
            ->setParameter('storeId', $store)
            ->getQuery()->getResult();
    }

    public function getOdooRefund(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.order', 'o')
            ->join('o.channel', 'c')
            ->where('o.odooState = :created')
            ->andWhere('c.code = :return')
            ->andWhere('p.state = :refunded')
            ->setParameter('created', OrderInterface::ODOO_STATE_CREATED)
            ->setParameter('return', ChannelCode::CHANNEL_RETURN)
            ->setParameter('refunded', OrderPaymentStates::STATE_REFUNDED)
            ->getQuery()->getResult();
    }

    public function findByDateRangeAmountAndSellerName(DateRange $dateRange, int $amount, string $lastName, ?string $firstName): ?PaymentInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.order', 'o')
            ->innerJoin(Fastmag::class, 'f', 'WITH', 'o.id = f.order')
            ->innerJoin('o.customer', 'c')
            ->innerJoin('o.channel', 'ch')
            ->where('p.amount = :amount')
            ->andWhere('ch.code = :channel')
            ->andWhere('p.createdAt >= :dateFrom')
            ->andWhere('p.createdAt <= :dateTo')
            ->setParameters(
                [
                    'amount' => $amount,
                    'channel' => ChannelCode::CHANNEL_RETAIL,
                    'dateFrom' => $dateRange->getFrom(),
                    'dateTo' => $dateRange->getTo(),
                ]
            );

        if (null === $firstName) {
            $qb->andWhere('LOWER(TRIM(c.lastName)) = :lastName')
                ->setParameter('lastName', $lastName);
        } else {
            $qb->andWhere('(LOWER(TRIM(c.firstName)) = :firstName AND LOWER(TRIM(c.lastName)) = :lastName) OR (LOWER(TRIM(c.firstName)) = :lastName AND LOWER(TRIM(c.lastName)) = :firstName)')
                ->setParameter('firstName', $firstName)
                ->setParameter('lastName', $lastName);
        }

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
