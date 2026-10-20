<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Repository\Sylius\CustomerRepositoryInterface as TediberCustomerRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Setono\SyliusGiftCardPlugin\Doctrine\ORM\CustomerRepositoryTrait as SetonoSyliusGiftCardPluginCustomerRepositoryTrait;
use Setono\SyliusGiftCardPlugin\Repository\CustomerRepositoryInterface as SetonoSyliusGiftCardPluginCustomerRepositoryInterface;
use Sylius\AdminOrderCreationPlugin\Doctrine\ORM\CustomerRepositoryInterface;
use Sylius\AdminOrderCreationPlugin\Doctrine\ORM\CustomerRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository as SyliusCustomerRepository;
use Sylius\Component\Core\OrderPaymentStates;

/**
 * @method Customer|null findOneByEmail(string $email)
 */
class CustomerRepository extends SyliusCustomerRepository implements SetonoSyliusGiftCardPluginCustomerRepositoryInterface, CustomerRepositoryInterface, TediberCustomerRepositoryInterface
{
    use SetonoSyliusGiftCardPluginCustomerRepositoryTrait;
    use CustomerRepositoryTrait;

    public function findByEmailPart(string $email): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.email LIKE :email')
            ->setParameter('email', '%' . $email . '%')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithAddress(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.defaultAddress', 'a')
            ->orderBy('c.createdAt', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByPromotionCoupon($coupon): Customer|null
    {
        return $this->createQueryBuilder('c')
            ->join('c.promotionCoupons', 'pc')
            ->where('pc.code = :coupon')
            ->setParameter('coupon', $coupon)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countNbFilleul(?Customer $sponsor): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.sponsor = :sponsor')
            ->setParameter('sponsor', $sponsor)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllMails($mode = 'iterate')
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.email');

        if ('iterate' === $mode) {
            return $qb->getQuery()->iterate();
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    public function findCustomerIds(int $offset, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function createCustomerWithMultipleOrdersOnSameDayQueryBuilder(): QueryBuilder
    {
        $subQuery = $this->createQueryBuilder('c')
            ->select('c.id')
            ->innerJoin('c.orders', 'o')
            ->andWhere('o.paymentState NOT IN (:states)')
            ->andHaving('COUNT(DISTINCT o.id) >= 2')
            ->groupBy('c.id, o.checkoutCompletedAt')
            ->getDQL();

        $queryBuilder = $this->createQueryBuilder('cm');

        return $queryBuilder
            ->andWhere($queryBuilder->expr()->in('cm.id', $subQuery))
            ->setParameter('states', [OrderPaymentStates::STATE_CANCELLED, OrderPaymentStates::STATE_REFUNDED]);
    }
}
