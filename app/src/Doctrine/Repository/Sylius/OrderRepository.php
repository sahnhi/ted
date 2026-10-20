<?php

namespace App\Doctrine\Repository\Sylius;

use App\Api\Odoo\Model\OrderInterface as OdooOrderInterface;
use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\Commission;
use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\OrderReturnItem;
use App\Doctrine\Entity\Seller;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PromotionCoupon;
use App\Doctrine\Entity\Sylius\PromotionInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Exception\OrderNotFound;
use App\Grid\Filter\MultipleEntityFilter;
use App\Identifier\OrderNumber;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\EnumOrderReturnDestination;
use App\Utils\DateRange;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Setono\SyliusGiftCardPlugin\Doctrine\ORM\OrderRepositoryTrait as SetonoSyliusGiftCardPluginOrderRepositoryTrait;
use Setono\SyliusGiftCardPlugin\Repository\OrderRepositoryInterface as SetonoSyliusGiftCardPluginOrderRepositoryInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Promotion\Model\PromotionCouponInterface as PromotionCouponInterfaceModel;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order|null findOneById(int $id)
 * @method Order|null findOneByName(string $name)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends BaseOrderRepository implements OrderRepositoryInterface, SetonoSyliusGiftCardPluginOrderRepositoryInterface
{
    use SetonoSyliusGiftCardPluginOrderRepositoryTrait;

    private const ORDER_BY_ASC = 'ASC';

    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->distinct()
            ->addSelect('channel')
            ->addSelect('customer')
            ->addSelect('address')
            ->addSelect('promotionCoupon')
            ->innerJoin('o.channel', 'channel')
            ->leftJoin('o.customer', 'customer')
            ->leftJoin('o.shipments', 'shipment')
            ->leftJoin('shipment.method', 'method')
            ->leftJoin('o.shippingAddress', 'address')
            ->leftJoin('o.items', 'item')
            ->leftJoin('item.variant', 'variant')
            ->leftJoin('variant.product', 'product')
            ->leftJoin('o.promotionCoupon', 'promotionCoupon')
            ->andWhere('o.state != :notState')
            ->setParameter('notState', OrderInterface::STATE_CART);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function countValidCustomerOrders(int $idCustomer)
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->innerJoin('o.customer', 'c')
            ->andWhere('o.customer = :idCustomer')
            ->andWhere("o.paymentState = 'paid' AND o.channel != :channel")
            ->setParameter('idCustomer', $idCustomer)
            ->setParameter('channel', 3)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function customerFirstOrder(int $idCustomer)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.customer', 'c')
            ->andWhere('o.customer = :idCustomer')
            ->andWhere("o.paymentState = 'paid'")
            ->setParameter('idCustomer', $idCustomer)
            ->orderBy('o.checkoutCompletedAt')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getBisNumber(string $number): int
    {
        $bis = $this->createQueryBuilder('o')
            ->select('max(o.bis)')
            ->where('o.parentNumber = :number')
            ->setParameter('number', $number)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $bis ? 0 : (int) $bis;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getReturnNumber(string $number): int
    {
        $return = $this->createQueryBuilder('o')
            ->select('max(o.return)')
            ->where('o.parentNumber = :number')
            ->setParameter('number', $number)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $return ? 0 : (int) $return;
    }

    public function createByCustomerIdQueryBuilder($customerId): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->distinct()
            ->addSelect('channel')
            ->addSelect('customer')
            ->addSelect('address')
            ->innerJoin('o.channel', 'channel')
            ->leftJoin('o.customer', 'customer')
            ->leftJoin('o.shipments', 'shipment')
            ->leftJoin('shipment.method', 'method')
            ->leftJoin('o.shippingAddress', 'address')
            ->leftJoin('o.items', 'item')
            ->leftJoin('item.variant', 'variant')
            ->leftJoin('variant.product', 'product')
            ->andWhere('o.customer = :customerId')
            ->andWhere('o.state != :notState')
            ->setParameter('customerId', $customerId)
            ->setParameter('notState', OrderInterface::STATE_CART);
    }

    public function getListQueryShipped($shippingMethodCodes): array
    {
        $tomorrow = new \DateTime('tomorrow');

        $qb = $this->createQueryBuilder('o')
            ->join('o.shipments', 'shipment')
            ->join('shipment.method', 'method')
            ->andWhere('o.state = :state')
            ->andWhere('o.shippingState = :shippingState')
            ->andWhere('o.paymentState = :paymentState')
            ->andWhere('shipment.startDeliverySlot <= :shipmentStartDeliverySlot')
            ->andWhere('method.code in (:codes)')
            ->setParameter('state', OrderInterfaceAlias::STATE_NEW)
            ->setParameter('shippingState', OrderShippingStates::STATE_READY)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('shipmentStartDeliverySlot', $tomorrow->format('Y-m-d H:i:s'))
            ->setParameter('codes', $shippingMethodCodes)
            ->setMaxResults(50);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getCompletedOrderByCartToken($cartToken): ?Order
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.tokenValue = :cartToken')
            ->andWhere('o.state = :state')
            ->setParameter('cartToken', $cartToken)
            ->setParameter('state', OrderInterfaceAlias::STATE_FULFILLED);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getOrderByCustomerEmailOrProducts(Customer $customer, $products = [], $version = false): ?Order
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.customer', 'customer')
            ->setParameter('customerEmail', $customer->getEmail())
            ->setParameter('state', OrderInterfaceAlias::STATE_FULFILLED);

        if (!empty($products)) {
            /* TODO: Temporary code to remove and keep only the first if */
            if (true === $version) {
                $productsCode = array_keys($products);
                $qb
                    ->innerJoin('o.items', 'item')
                    ->innerJoin('item.variant', 'variant')
                    ->innerJoin('variant.product', 'product')
                    ->where('customer.email = :customerEmail and o.state = :state')
                    ->andWhere('product.code IN (:productsCode)')
                    ->setParameter('productsCode', $productsCode);
            } else {
                $i = 1;
                foreach ($products as $product) {
                    $qb
                        ->innerJoin('o.items', 'item' . $i)
                        ->innerJoin('item' . $i . '.variant', 'variant' . $i)
                        ->innerJoin('variant' . $i . '.product', 'product' . $i)
                        ->orWhere('product' . $i . '.code = :product' . $i . ' and customer.email = :customerEmail and o.state = :state')
                        ->setParameter('product' . $i, $product['code']);
                    ++$i;
                }
            }
        } else {
            $qb->where('customer.email = :customerEmail and o.state = :state');
        }

        $qb->orderBy('o.id', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function getQueryBuilderReadyForReviews(DateRange $dateRange): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.shipments', 's')
            ->innerJoin('o.channel', 'c')
            ->andWhere('o.shippingState IN (:states)')
            ->andWhere('s.endDeliverySlot >= :minDate')
            ->andWhere('s.endDeliverySlot <= :maxDate')
            ->andWhere('o.reviewEmailSent = :reviewEmailSent')
            ->setParameter('states', [
                OrderShippingStates::STATE_DELIVERED,
                OrderShippingStates::STATE_SHIPPED,
            ])
            ->setParameter('minDate', $dateRange->getFrom())
            ->setParameter('maxDate', $dateRange->getTo())
            ->setParameter('reviewEmailSent', false);
    }

    private function getQueryBuilderForReviewSeekers(DateRange $dateRange): QueryBuilder
    {
        return $this->getQueryBuilderReadyForReviews($dateRange)
            ->innerJoin('o.customer', 'cu')
            ->andWhere('c.code IN (:channels)')
            ->setParameter('channels', [ChannelCode::CHANNEL_WEB, ChannelCode::CHANNEL_SAV])
            ->andWhere('cu.optin = :optin')
            ->setParameter('optin', true);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countReadyForReviewSeekers(DateRange $dateRange): int
    {
        return $this->getQueryBuilderForReviewSeekers($dateRange)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Order[]|float|int|mixed|string
     */
    public function findAllForMailReviews(DateRange $dateRange)
    {
        $queryBuilder = $this->getQueryBuilderReadyForReviews($dateRange)
            ->andWhere('c.code NOT IN (:channels)')
            ->setParameter('channels', [ChannelCode::CHANNEL_RETURN]);

        $orders = $queryBuilder->getQuery()->getResult();

        $this->associationHydrator->hydrateAssociations($orders, [
            'items',
            'items.variant',
            'items.variant.translations',
            'items.variant.product',
            'channel',
        ]);

        return $orders;
    }

    /**
     * @return Order[]|float|int|mixed|string
     */
    public function findAllForThirdPartyReviews(DateRange $dateRange): mixed
    {
        $queryBuilder = $this->getQueryBuilderForReviewSeekers($dateRange);

        $orders = $queryBuilder->getQuery()->getResult();

        $this->associationHydrator->hydrateAssociations($orders, ['customer']);

        return $orders;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getTotalSalesForChannel(ChannelInterface $channel): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.paymentState = :paymentState')
            ->setParameter('channel', $channel)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllByCurrentYear()
    {
        $currentYear = new \DateTime('now');
        $currentYear->setDate(date('Y'), '1', '1');
        $currentYear->format('Y-m-d H:i:s');

        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.channel', 'channel')
            ->andWhere('channel.code not in (:channels)')
            ->andWhere('o.paymentState = :payment_state')
            ->andWhere('o.checkoutCompletedAt >= :date')
            ->setParameter('channels', ['bis', 'b2b', 'return', 'operation'])
            ->setParameter('payment_state', OrderPaymentStates::STATE_PAID)
            ->setParameter('date', $currentYear)
            ->orderBy('o.checkoutCompletedAt', self::ORDER_BY_ASC);

        return $qb->getQuery()->getResult();
    }

    public function findAllWithItemsAndCustomer(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->leftJoin('o.shippingAddress', 'sa')
            ->leftJoin('o.billingAddress', 'ba')
            ->leftJoin('o.promotionCoupon', 'pc')
            ->leftJoin('o.channel', 'ch')
            ->leftJoin('o.payments', 'pa')
            ->leftJoin('o.shipments', 'sh')
            ->leftJoin('o.items', 'i')
            ->orderBy('o.createdAt', self::ORDER_BY_ASC)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function createQueryBuilderByNumber(string $number): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.number LIKE :number')
            ->setParameter('number', '%' . $number . '%');
    }

    public function getReportingData($channel, $type = null)
    {
        $productCommissions = $this->getEntityManager()->getRepository(Commission::class)->findAll();

        $sellerCommissions = ['mattress' => 0, 'bed-frame' => 0, 'slim-mattress' => 0];
        foreach ($productCommissions as $productCommission) {
            $sellerCommissions[$productCommission->getProduct()->getCode()] = $productCommission->getCommission();
        }

        $conn = $this->getEntityManager()->getConnection();

        $storeCommission = 0;
        if ('carrefour' === $channel->getCode()) {
            $storeCommission = Commission::CARREFOUR_COMMISSION;
        } elseif ('printemps' === $channel->getCode()) {
            $storeCommission = Commission::PRINTEMPS_COMMISSION;
        }

        if ('reporting_partners_total' === $type) {
            $sqlSelect = 'SELECT
                        SUM(round(sylius_order.total/120,2)) as caHT,
                        COUNT(sylius_order.id) as nbrOrder,
                        SUM(round(sylius_order.total*' . $storeCommission . '/100/100/1.2,2)) as commissionPartner,
                        MONTH(sylius_order.checkout_completed_at) as month,
                        YEAR(sylius_order.checkout_completed_at) as year';
        } elseif ('reporting_partners' === $type) {
            $sqlSelect = 'SELECT
                        sylius_store.name as storeName,
                        SUM(round(sylius_order.total/120,2)) as caHT,
                        COUNT(sylius_order.id) as nbrOrder,
                        SUM(round(sylius_order.total*' . $storeCommission . '/100/100/1.2,2)) as commissionPartner,
                        MONTH(sylius_order.checkout_completed_at) as month,
                        YEAR(sylius_order.checkout_completed_at) as year';
        } elseif ('reporting_sellers' === $type) {
            $sqlSelect = 'SELECT
                        sylius_seller.name as sellerName,
                        ROUND((' . $sellerCommissions['mattress'] . '/100)*SUM(CASE WHEN sylius_product_variant.product_id = 13 THEN sylius_order_item.quantity ELSE 0 END)+(' . $sellerCommissions['slim-mattress'] . '/100)*SUM(CASE WHEN sylius_product_variant.product_id = 20 THEN sylius_order_item.quantity ELSE 0 END)+(' . $sellerCommissions['bed-frame'] . '/100)*SUM(CASE WHEN sylius_product_variant.product_id = 18 THEN sylius_order_item.quantity ELSE 0 END)+(' . Seller::OTHERS_COMMISSION . '/100)*(SUM(CASE WHEN sylius_product_variant.product_id not in (13,18,20) THEN sylius_order_item.quantity ELSE 0 END)-SUM(CASE WHEN sylius_product_variant.product_id = 14 OR (sylius_product_variant.product_id not in (13,18,20) and sylius_order_item.total=0) THEN sylius_order_item.quantity - sylius_order_item.total/8500 ELSE 0 END)), 2) as commissionSeller,
                        MONTH(sylius_order.checkout_completed_at) as month,
                        YEAR(sylius_order.checkout_completed_at) as year';
        } else {
            $sqlSelect = "SELECT sylius_order.number,
                        DATE_ADD(sylius_order.checkout_completed_at, INTERVAL 2 HOUR) as date,
                        GROUP_CONCAT(distinct sylius_store.name) as storeName,
                        sylius_address.first_name as firstName,
                        sylius_address.last_name as lastName,
                        sylius_customer.email,
                        CASE WHEN sylius_order.channel_id = 1 THEN 'Sur devis' ELSE 'Sur place' END as quoteSpot,
                        round(sylius_order.total/100) as caTTC,
                        round(sylius_order.total/120,2) as caHT,
                        GROUP_CONCAT(distinct sylius_seller.name) as sellerName,
                        SUM(CASE WHEN sylius_product_variant.product_id = 13 THEN sylius_order_item.quantity ELSE 0 END) as matelas,
                        SUM(CASE WHEN sylius_product_variant.product_id = 18 THEN sylius_order_item.quantity ELSE 0 END) as sommier,
                        SUM(CASE WHEN sylius_product_variant.product_id = 20 THEN sylius_order_item.quantity ELSE 0 END) as TED,
                        SUM(CASE WHEN sylius_product_variant.product_id not in (13,18,20) THEN sylius_order_item.quantity ELSE 0 END) as autres,
                        SUM(CASE WHEN sylius_product_variant.product_id = 14 OR (sylius_product_variant.product_id not in (13,18,20) and sylius_order_item.total=0) THEN ROUND(sylius_order_item.quantity - sylius_order_item.total/8500) ELSE 0 END) as autresOfferts,
                        SUM(CASE WHEN sylius_product_variant.product_id = 14 THEN ROUND(sylius_order_item.quantity - sylius_order_item.total/8500) ELSE 0 END) as lingot,
                        GROUP_CONCAT(distinct sylius_payment_method.code) as paymentMethod,
                        CASE WHEN MAX(sylius_shipping_method.code)='MAGASIN' THEN 'Emporté' ELSE 'Livraison' END as shippingMethod,
                        round(sylius_order.total*" . $storeCommission . '/100/100/1.2,2) as commissionPartner,
                        ROUND((' . $sellerCommissions['mattress'] . '/100)*SUM(CASE WHEN sylius_product_variant.product_id = 13 THEN sylius_order_item.quantity ELSE 0 END)+(' . $sellerCommissions['slim-mattress'] . '/100)*SUM(CASE WHEN sylius_product_variant.product_id = 20 THEN sylius_order_item.quantity ELSE 0 END)+(' . $sellerCommissions['bed-frame'] . '/100)*SUM(CASE WHEN sylius_product_variant.product_id = 18 THEN sylius_order_item.quantity ELSE 0 END)+(' . Seller::OTHERS_COMMISSION . '/100)*(SUM(CASE WHEN sylius_product_variant.product_id not in (13,18,20) THEN sylius_order_item.quantity ELSE 0 END)-SUM(CASE WHEN sylius_product_variant.product_id = 14 OR (sylius_product_variant.product_id not in (13,18,20) and sylius_order_item.total=0) THEN sylius_order_item.quantity - sylius_order_item.total/8500 ELSE 0 END)), 2) as commissionSeller,
                        MONTH(sylius_order.checkout_completed_at) as monthDate,
                        YEAR(sylius_order.checkout_completed_at) as yearDate';
        }

        $sqlFrom = '
                FROM sylius_order
                      INNER JOIN sylius_order_store on sylius_order_store.order_id=sylius_order.id
                      INNER JOIN sylius_store on sylius_order_store.store_id=sylius_store.id and sylius_store.channel_id = :channel';

        if ('reporting_sellers' === $type) {
            $sqlFrom .= '
                      INNER JOIN sylius_seller on sylius_order_store.seller_id=sylius_seller.id
                      INNER JOIN sylius_order_item on sylius_order.id=sylius_order_item.order_id
                      INNER JOIN sylius_product_variant on sylius_product_variant.id=sylius_order_item.variant_id';
        } elseif (null === $type) {
            $sqlFrom .= "
                      INNER JOIN sylius_seller on sylius_order_store.seller_id=sylius_seller.id
                      INNER JOIN sylius_payment on sylius_payment.order_id=sylius_order.id and sylius_payment.state='completed'
                      INNER JOIN sylius_payment_method on sylius_payment_method.id=sylius_payment.method_id
                      INNER JOIN sylius_order_item on sylius_order.id=sylius_order_item.order_id
                      INNER JOIN sylius_product_variant on sylius_product_variant.id=sylius_order_item.variant_id
                      INNER JOIN sylius_shipment on sylius_shipment.order_id=sylius_order.id
                      INNER JOIN sylius_shipping_method on sylius_shipping_method.id = sylius_shipment.method_id
                      INNER JOIN sylius_address on sylius_order.shipping_address_id=sylius_address.id
                      INNER JOIN sylius_customer on sylius_order.customer_id=sylius_customer.id";
        }

        $sqlWhere = '
                WHERE (sylius_order.channel_id = :channel or (sylius_order.channel_id = 1 and sylius_store.name is not null)) and payment_state IN (:paymentStates)';

        $sqlGroupBy = '';
        if ('reporting_partners_total' === $type) {
            $sqlGroupBy = '
                GROUP BY MONTH(sylius_order.checkout_completed_at), YEAR(sylius_order.checkout_completed_at)';
        } elseif ('reporting_partners' === $type) {
            $sqlGroupBy = '
                GROUP BY MONTH(sylius_order.checkout_completed_at), YEAR(sylius_order.checkout_completed_at), sylius_store.name';
        } elseif ('reporting_sellers' === $type) {
            $sqlGroupBy = '
                GROUP BY MONTH(sylius_order.checkout_completed_at), YEAR(sylius_order.checkout_completed_at), sylius_seller.name';
        } else {
            $sqlGroupBy = '
                GROUP BY sylius_order.id';
        }

        $sql = $sqlSelect . '  ' . $sqlFrom . ' ' . $sqlWhere . ' ' . $sqlGroupBy;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['channel' => $channel->getId(), 'paymentStates' => OrderPaymentStates::STATE_PAID . ', ' . OrderPaymentStates::STATE_REFUNDED]);

        return $stmt->fetchAll();
    }

    /**
     * @return Order|null
     */
    public function getOrdersForLokad($yesterday): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.updatedAt >= :yesterday')
            ->andWhere('o.paymentState = :state')
            ->setParameter('yesterday', $yesterday)
            ->setParameter('state', OrderPaymentStates::STATE_PAID)
            ->getQuery()->getResult();
    }

    public function getTodayTotalSalesForChannel(ChannelInterface $channel): int
    {
        $today = new \DateTime('today');
        $today->setTime(0, 0, 0);

        return (int) $this->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.paymentState = :paymentState')
            ->andWhere('o.checkoutCompletedAt >= :checkoutCompletedAt')
            ->setParameter('channel', $channel)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('checkoutCompletedAt', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getOneByTokenValueHydrated(string $tokenValue)
    {
        $order = $this->createQueryBuilder('o')
            ->andWhere('o.tokenValue = :tokenValue')
            ->andWhere('o.state != :stateCancelled')
            ->setParameter('tokenValue', $tokenValue)
            ->setParameter('stateCancelled', OrderInterfaceAlias::STATE_CANCELLED)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($order, [
            'items',
            'items.units',
            'items.units.adjustments',
            'items.variant',
            'items.variant.channelPricings',
            'items.variant.optionValues',
            'items.variant.optionValues.translations',
            'items.variant.packs',
            'items.variant.translations',
            'adjustments',
            'payments',
            'shipments',
            'promotions',
            'orderReason',
        ]);

        return $order;
    }

    public function isNewCustomer($customerId, $orderToken): bool
    {
        $result = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->where('o.customer = :customerId')
            ->andWhere('o.tokenValue != :orderToken')
            ->andWhere('o.paymentState = :paid')
            ->setParameter('customerId', $customerId)
            ->setParameter('orderToken', $orderToken)
            ->setParameter('paid', OrderPaymentStates::STATE_PAID)
            ->getQuery()
            ->getSingleScalarResult();

        return ($result > 0) ? false : true;
    }

    public function createOrderList(array $criteria): QueryBuilder
    {
        $req = $this->createQueryBuilder('o');
        $req->innerJoin('o.channel', 'channel');
        if (!empty($criteria)) {
            $req->distinct();
            if (!empty($criteria['channel'])) {
                $req->addSelect('channel');
            }
            if (!empty($criteria['customer']['type'])) {
                $req->addSelect('customer')
                    ->leftJoin('o.customer', 'customer');
            }
            if (!empty($criteria['shipping_department']['value'])) {
                $req->addSelect('address')
                    ->leftJoin('o.shippingAddress', 'address');
            }
            if (!empty($criteria['promotion_coupon']['type']) || !empty($criteria['promotion']['type'])) {
                $req->addSelect('promotionCoupon')
                    ->leftJoin('o.promotionCoupon', 'promotionCoupon');
                $req->addSelect('promotion')
                    ->leftJoin('promotionCoupon.promotion', 'promotion');
            }
            if (!empty($criteria['shipping_method']) || !empty($criteria['shipping_date'])) {
                $req->leftJoin('o.shipments', 'shipment')
                    ->leftJoin('shipment.method', 'method');
            }
            if (!empty($criteria['contains_product']) || !empty($criteria['contains_variant'])) {
                $req->leftJoin('o.items', 'item')
                    ->leftJoin('item.variant', 'variant')
                    ->leftJoin('variant.product', 'product');
            }

            if (!empty($criteria['checkout_state'])) {
                $req->andWhere('o.state = :cartState AND o.checkoutState IN ( :paymentSelected, :paymentSkipped)')
                    ->setParameter('cartState', OrderInterfaceAlias::STATE_CART)
                    ->setParameter('paymentSelected', OrderCheckoutStates::STATE_PAYMENT_SELECTED)
                    ->setParameter('paymentSkipped', OrderCheckoutStates::STATE_PAYMENT_SKIPPED)
                    ->orderBy('o.id', 'DESC');
            }
        }

        return $req;
    }

    public function countByCustomerAndCoupon(
        CustomerInterface $customer,
        PromotionCouponInterface $coupon,
        bool $includeCancelled = false
    ): int {
        $orderStates = [OrderInterface::STATE_CART, OrderInterface::STATE_NEW];

        // TODO : Disable coupons reusable from cancelled orders, to do clean
        // if ($coupon->isReusableFromCancelledOrders()) {
        //    $orderStates[] = OrderInterface::STATE_CANCELLED;
        // }

        $orderPaymentStates = [OrderPaymentStates::STATE_PAID];

        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.promotionCoupon = :coupon')
            ->andWhere('o.state NOT IN (:orderStates)')
            ->andWhere('o.paymentState NOT IN (:orderPaymentStates)')
            ->setParameter('customer', $customer)
            ->setParameter('coupon', $coupon)
            ->setParameter('orderStates', $orderStates)
            ->setParameter('orderPaymentStates', $orderPaymentStates)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getOrderForTrackingEmailByToken($token)
    {
        $order = $this->createQueryBuilder('o')
            ->join('o.shipments', 's')
            ->join('s.method', 'sm')
            ->join('o.customer', 'c')
            ->join('o.items', 'i')
            ->join('i.variant', 'v')
            ->join('v.product', 'p')
            ->leftJoin('o.giftCards', 'gc')
            ->where('o.tokenValue = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($order, [
            'shipments',
            'shipments.method',
            'items',
            'items.variant',
            'items.variant.product',
            'customer',
            'giftCards',
        ]);

        return $order;
    }

    public function findOrderReturnListQueryBuilder(array $criteria, $export = true): QueryBuilder
    {
        $oneYearAgo = (new \DateTime('now'))->modify('-1 year');

        $req = $this->createQueryBuilder('o')
            ->distinct();

        if (!empty($criteria)) {
            $req->distinct();
            if (!empty($criteria['customer']['type'])) {
                $req->addSelect('customer')
                    ->leftJoin('o.customer', 'customer');
            }
            if (!empty($criteria['contains_product']) || !empty($criteria['contains_variant'])) {
                $req->leftJoin('o.items', 'item')
                    ->leftJoin('item.variant', 'variant')
                    ->leftJoin('variant.product', 'product');
            }
        }

        $req->addSelect('channel')
            ->addSelect('address')
            ->addSelect('shipment')
            ->innerJoin('o.channel', 'channel')
            ->leftJoin('o.shipments', 'shipment')
            ->leftJoin('shipment.method', 'method')
            ->leftJoin('o.shippingAddress', 'address')
            ->leftJoin('o.orderReturn', 'orderReturn')
            ->leftJoin(OrderReturnItem::class, 'ori', 'WITH', 'ori.orderReturn = orderReturn.id')
            ->where('channel.code = :returnChannel')
            ->andWhere('o.state != :cancelled')
            ->andWhere('o.paymentState != :refunded')
            ->andWhere('o.checkoutCompletedAt >= :oneYear')
            ->andWhere('o.number NOT LIKE :return_type_refund')
            ->andWhere('o.number NOT LIKE :return_type_sender')
            ->orderBy('shipment.endDeliverySlot', self::ORDER_BY_ASC)
            ->setParameters(
                [
                    'returnChannel' => ChannelCode::CHANNEL_RETURN,
                    'refunded' => OrderPaymentStates::STATE_REFUNDED,
                    'cancelled' => OrderInterfaceAlias::STATE_CANCELLED,
                    'oneYear' => $oneYearAgo->format('Y-m-d H:i:s'),
                    'return_type_refund' => '%RB%',
                    'return_type_sender' => '%RX%',
                ]
            );

        if (isset($criteria['destination']) && in_array($criteria['destination']['destination'], EnumOrderReturnDestination::AVAILABLE_DESTINATION_TYPE, true)) {
            $req->andWhere('ori.destination = :destination')
                ->setParameter('destination', $criteria['destination']['destination']);
        } elseif (isset($criteria['destination']) && EnumOrderReturnDestination::DESTINATION_WAITING === $criteria['destination']['destination']) {
            $req->andWhere('orderReturn.id IS NULL');
        }
        if (!empty($criteria)) {
            $this->applyMultipleEntityFilters($req, $criteria);
        }

        return $req;
    }

    public function findOrderReturnForLogisticListQueryBuilder(array $criteria, $export = true): QueryBuilder
    {
        $oneYearAgo = (new \DateTime('now'))->modify('-1 year');

        $req = $this->createQueryBuilder('o')
            ->distinct();

        if (!empty($criteria)) {
            $req->distinct();
            if (!empty($criteria['customer']['type'])) {
                $req->addSelect('customer')
                    ->leftJoin('o.customer', 'customer');
            }
            if (!empty($criteria['contains_product']) || !empty($criteria['contains_variant'])) {
                $req->leftJoin('o.items', 'item')
                    ->leftJoin('item.variant', 'variant')
                    ->leftJoin('variant.product', 'product');
            }
        }

        $req->addSelect('channel')
            ->addSelect('address')
            ->addSelect('shipment')
            ->innerJoin('o.channel', 'channel')
            ->leftJoin('o.shipments', 'shipment')
            ->leftJoin('shipment.method', 'method')
            ->leftJoin('o.shippingAddress', 'address')
            ->leftJoin('o.orderReturn', 'orderReturn')
            ->leftJoin(OrderReturnItem::class, 'ori', 'WITH', 'ori.orderReturn = orderReturn.id')
            ->where('channel.code = :returnChannel')
            ->andWhere('o.state != :cancelled')
            ->andWhere('o.checkoutCompletedAt >= :oneYear')
            ->andWhere('o.number NOT LIKE :return_type_sender')
            ->orderBy('shipment.endDeliverySlot', self::ORDER_BY_ASC)
            ->setParameters(
                [
                    'returnChannel' => ChannelCode::CHANNEL_RETURN,
                    'cancelled' => OrderInterfaceAlias::STATE_CANCELLED,
                    'oneYear' => $oneYearAgo->format('Y-m-d H:i:s'),
                    'return_type_sender' => '%RX%',
                ]
            );

        if (isset($criteria['destination']) && in_array($criteria['destination']['destination'], EnumOrderReturnDestination::AVAILABLE_DESTINATION_TYPE, true)) {
            $req->andWhere('ori.destination = :destination')
                ->setParameter('destination', $criteria['destination']['destination']);
        } elseif (isset($criteria['destination']) && EnumOrderReturnDestination::DESTINATION_WAITING === $criteria['destination']['destination']) {
            $req->andWhere('orderReturn.id IS NULL');
        }
        if (!empty($criteria)) {
            $this->applyMultipleEntityFilters($req, $criteria);
        }

        return $req;
    }

    public function createOutOfStockListQueryBuilder(array $criteria = []): QueryBuilder
    {
        $req = $this->createQueryBuilder('o');
        if (!empty($criteria)) {
            $req->distinct();
            if (!empty($criteria['customer']['type'])) {
                $req->addSelect('customer')
                    ->leftJoin('o.customer', 'customer');
            }
            if (!empty($criteria['shipping_method']) || !empty($criteria['shipping_date']) || !empty($criteria['delivery_date'])) {
                $req->leftJoin('o.shipments', 'shipment')
                ->leftJoin('shipment.method', 'method');
            }
            if (!empty($criteria['contains_product']) || !empty($criteria['contains_variant']) || !empty($criteria['isBundle'])) {
                $req->leftJoin('o.items', 'item')
                    ->leftJoin('item.variant', 'variant')
                    ->leftJoin('variant.product', 'product');
            }
        }
        $req->addSelect('channel')
            ->innerJoin('o.channel', 'channel')
            ->leftJoin('o.shippingAddress', 'address');

        $where = '(outOfStock.id IS NULL '
            . 'AND o.state = :newState '
            . 'AND o.paymentState = :paidState '
            . 'AND channel.code != :returnChannel) '
            . 'OR (outOfStock.id IS NOT NULL '
            . 'AND outOfStock.status IN (:outOfStockStates) '
            . 'AND o.state != :cancelled '
            . 'AND o.shippingState NOT IN (:shippedAndDelivered)';
        if (!empty($criteria['isBundle']) && !empty($criteria['isBundle']['value'])) {
            $isBundle = $criteria['isBundle']['value'];
            if ('1' === $isBundle) {
                $req->innerJoin('variant.bundleAssociations', 'bundle');
            } else {
                $req->leftJoin('variant.bundleAssociations', 'bundle', false);
                $where .= ' AND bundle IS NULL';
            }
        }
        $where .= ')';
        $req->leftJoin('o.orderOutOfStock', 'outOfStock')
            ->andWhere($where)
            ->setParameter('newState', OrderInterfaceAlias::STATE_NEW)
            ->setParameter('paidState', OrderPaymentStates::STATE_PAID)
            ->setParameter('returnChannel', 'return')
            ->setParameter('outOfStockStates', [OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED, OrderOutOfStockInterface::STATE_OUT_OF_STOCK, OrderOutOfStockInterface::STATE_TO_SEND])
            ->setParameter('shippedAndDelivered', [OrderShippingStates::STATE_DELIVERED, OrderShippingStates::STATE_SHIPPED])
            ->setParameter('cancelled', OrderInterfaceAlias::STATE_CANCELLED)
            ->orderBy('o.stockPriority', 'DESC')
            ->addOrderBy('o.checkoutCompletedAt', self::ORDER_BY_ASC)
            ->distinct();
        if (!empty($criteria)) {
            $this->applyMultipleEntityFilters($req, $criteria);
        }

        return $req;
    }

    private function applyMultipleEntityFilters(QueryBuilder $req, array $criteria)
    {
        $multipleEntityFilters = [
            MultipleEntityFilter::FILTER_NOT_CONTAINS_VARIANT => !empty($criteria[MultipleEntityFilter::FILTER_NOT_CONTAINS_VARIANT]),
            MultipleEntityFilter::FILTER_CONTAINS_VARIANT => !empty($criteria[MultipleEntityFilter::FILTER_CONTAINS_VARIANT]),
        ];

        $i = 0;

        foreach ($multipleEntityFilters as $key => $value) {
            if ($value) {
                $function = MultipleEntityFilter::FILTER_NOT_CONTAINS_VARIANT === $key ? 'notIn' : 'in';

                $req->andWhere(
                    $this->getEntityManager()->getExpressionBuilder()->$function(
                        'o',
                        $this->createQueryBuilder('o' . $i)
                            ->select('o' . $i)
                            ->leftJoin('o' . $i . '.items', 'i' . $i)
                            ->leftJoin('i' . $i . '.variant', 'v' . $i)
                            ->leftJoin('i' . $i . '.orderItemProductCombo', 'orderItemProductCombo' . $i)
                            ->leftJoin('orderItemProductCombo' . $i . '.productCombo', 'productCombo' . $i)
                            ->leftJoin('productCombo' . $i . '.productVariant', 'productVariant' . $i)
                            ->leftJoin('productCombo' . $i . '.productVariantLogistic', 'productVariantLogistic' . $i)
                            ->leftJoin('v' . $i . '.bundleAssociations', 'bundles' . $i)
                            ->where('v' . $i . '.id IN (:' . $key . ')')
                            ->orWhere('productVariant' . $i . '.id IN (:' . $key . ')')
                            ->orWhere('productVariantLogistic' . $i . '.id IN (:' . $key . ')')
                            ->orWhere('bundles' . $i . '.id IN (:' . $key . ')')
                            ->orWhere('bundles' . $i . '.variant IN (:' . $key . ')')
                            ->getDQL()
                    )
                );
            }

            ++$i;
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getOrderByCustomerEmail(string $email): ?Order
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.customer', 'customer')
            ->where('customer.email = :customerEmail and o.paymentState = :paymentState')
            ->setParameter('customerEmail', $email)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getParrainOrderAmountTotal(PromotionCoupon $promotionCoupon)
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.total)')
            ->where('o.promotionCoupon = :promotionCoupon')
            ->setParameter('promotionCoupon', $promotionCoupon)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPaidByCustomer(CustomerInterface $customer): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.paymentState = :paymentState')
            ->setParameter('customer', $customer)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCurrentByEmailAndNumberValueHydrated(string $email, string $number)
    {
        $order = $this->createQueryBuilder('o')
            ->join('o.customer', 'c')
            ->join('o.shipments', 's')
            ->andWhere(
                'c.email = :email ' .
                'AND o.return = 0 ' .
                'AND o.paymentState = :paid ' .
                'AND o.tokenValue IS NOT NULL ' .
                'AND (o.number = :number ' .
                'OR o.parentNumber = :number)'
            )
            ->addOrderBy('o.id', 'DESC')
            ->setMaxResults(1)
            ->setParameter('email', $email)
            ->setParameter('number', $number)
            ->setParameter('paid', OrderPaymentStates::STATE_PAID)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($order, [
            'items',
            'items.units',
            'items.units.adjustments',
            'items.variant',
            'items.variant.channelPricings',
            'items.variant.optionValues',
            'items.variant.optionValues.translations',
            'items.variant.packs',
            'items.variant.translations',
            'adjustments',
            'payments',
            'shipments',
            'promotions',
            'orderReason',
        ]);

        return $order;
    }

    /**
     * @todo : find a better way to handle child links between orders
     */
    public function findAssociatedOrders($number): array
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('o.parentNumber')
            ->where('o.number LIKE :number')
            ->setParameter('number', $number . '%');

        $finalArray = [];
        $results = $qb->getQuery()->getResult();

        foreach ($results as $result) {
            $finalArray[$result['parentNumber']][] = $result[0];
        }

        return $finalArray;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findInitialOrder(?string $number): ?OrderInterface
    {
        $order = $this->findOneLikeNumber($number);

        if ($order instanceof Order && null !== $order->getParentNumber()) {
            return $this->findInitialOrder($order->getParentNumber());
        }

        return $order;
    }

    /**
     * @return OrderInterfaceAlias|null
     */
    public function getOrderParent(Order $order): ?OrderInterface
    {
        if (null !== $order->getParentNumber()) {
            return $this->findOneBy(['number' => $order->getParentNumber()]);
        }

        return null;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findOneLikeNumber($number, $state = null)
    {
        // Get Base Order Number (EX TED25002RB1 => 25002)
        $matches = [];
        preg_match("/([0-9]+)(\d{3})/", (string) $number, $matches);
        $base = !empty($matches) ? $matches[0] : $number;

        // Get Everything after Base Order Number to get full number (EX TED25002RB1 => RB1 then TED25002RB1 => 00025002RB1)
        $suffix = substr((string) $number, strpos((string) $number, (string) $base) + strlen((string) $base));
        $number = str_pad((string) $base, 9, '0', STR_PAD_LEFT) . $suffix;

        $request = $this->createQueryBuilder('o')
            ->where('o.number = :number')
            ->setParameter('number', $number);
        if (null !== $state) {
            $request->andWhere('o.state != :state')
                ->setParameter('state', $state);
        }
        $result = $request->getQuery()->getOneOrNullResult();

        // Case it's an old command (starting with 0)
        if (null === $result) {
            $number = $base . $suffix;
            $request = $this->createQueryBuilder('o')
                ->where('o.number = :number')
                ->setParameter('number', $number);
            if (null !== $state) {
                $request->andWhere('o.state != :state')
                    ->setParameter('state', $state);
            }
            $result = $request->getQuery()->getOneOrNullResult();
        }

        return $result;
    }

    public function findYounitedContractsToActivate()
    {
        $parameters = [
            'delivered' => OrderShippingStates::STATE_DELIVERED,
            'paid' => OrderPaymentStates::STATE_PAID,
            'younited' => 'younited',
            'completed' => PaymentInterface::STATE_COMPLETED,
        ];

        $qb = $this->createQueryBuilder('o')
            ->join('o.payments', 'p')
            ->join('p.method', 'm')
            ->where('o.shippingState = :delivered')
            ->andWhere('o.paymentState = :paid')
            ->andWhere('m.code = :younited')
            ->andWhere('p.state = :completed')
            ->andWhere('o.younitedActivated = 0')
            ->setMaxResults(50)
            ->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }

    public function findYounitedBisContractsToActivate()
    {
        $parameters = [
            'delivered' => OrderShippingStates::STATE_DELIVERED,
            'paid' => OrderPaymentStates::STATE_PAID,
            'younited' => 'younited',
            'bis' => 'bis',
            'cancelled' => OrderShippingStates::STATE_CANCELLED,
            'completed' => PaymentInterface::STATE_COMPLETED,
        ];

        $qb = $this->createQueryBuilder('o')
            ->join(Order::class, 'i', Join::WITH, 'o.parentNumber = i.number')
            ->join('i.payments', 'p')
            ->join('p.method', 'm')
            ->join('o.channel', 'c')
            ->where('o.shippingState = :delivered')
            ->andWhere('c.code = :bis')
            ->andWhere('i.shippingState = :cancelled')
            ->andWhere('i.paymentState = :paid')
            ->andWhere('m.code = :younited')
            ->andWhere('i.younitedActivated = 0')
            ->andWhere('p.state = :completed')
            ->setMaxResults(50)
            ->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneLastOrderForGetCouponSponsor(Customer $customer): PromotionCouponInterfaceModel|null
    {
        /** @var Order|null $order */
        $order = $this->createQueryBuilder('o')
            ->innerJoin('o.promotionCoupon', 'pc')
            ->innerJoin('pc.promotion', 'p')
            ->andWhere('o.paymentState != :paymentState')
            ->andWhere('o.customer = :customer')
            ->andWhere('p.code = :promotionCode')
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('customer', $customer)
            ->setParameter('promotionCode', PromotionInterface::PROMOTION_SPONSOR_CODE)
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $order?->getPromotionCoupon();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findAllLikeNumber($numbers, $state = null)
    {
        $ordersCode = $ordersOldCode = [];
        foreach ($numbers as $number) {
            // Get Base Order Number (EX TED25002RB1 => 25002)
            $matches = [];
            preg_match("/([0-9]+)(\d{3})/", (string) $number, $matches);
            $base = !empty($matches) ? $matches[0] : $number;

            // Get Everything after Base Order Number to get full number (EX TED25002RB1 => RB1 then TED25002RB1 => 00025002RB1)
            $suffix = substr((string) $number, strpos((string) $number, (string) $base) + strlen((string) $base));
            $number = str_pad((string) $base, 9, '0', STR_PAD_LEFT) . $suffix;
            $ordersCode[] = $number;
            $ordersOldCode[] = $base . $suffix;
        }

        $request = $this->createQueryBuilder('o')
            ->where('o.number IN (:number)')
            ->setParameter('number', $ordersCode);
        if (null !== $state) {
            $request->andWhere('o.state != :state')
                ->setParameter('state', $state);
        }
        $result = $request->getQuery()->getResult();

        // Case it's an old command (starting with 0)
        if (null === $result) {
            $request = $this->createQueryBuilder('o')
                ->where('o.number IN (:number)')
                ->setParameter('number', $ordersOldCode);
            if (null !== $state) {
                $request->andWhere('o.state != :state')
                    ->setParameter('state', $state);
            }
            $result = $request->getQuery()->getResult();
        }

        return $result;
    }

    public function getLastUpdatedDate($number)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o.updatedAt')
            ->where('o.parentNumber = :number')
            ->orWhere('o.number = :number')
            ->setParameter('number', $number)
            ->orderBy('o.updatedAt')
            ->setMaxResults(1);

        return $qb->getQuery()->getSingleResult()['updatedAt'];
    }

    public function findAllNonAnonymousPaidInitialOrders($offset, $limit)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.customer is not null')
            ->andWhere('o.number is not null')
            ->andWhere('o.parentNumber is null')
            ->andWhere('o.paymentState IN (:states)')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->setParameter('states', [OrderPaymentStates::STATE_PAID, OrderPaymentStates::STATE_REFUNDED])
            ->orderBy('o.id', self::ORDER_BY_ASC);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int|mixed|string
     */
    public function findAllShippedByShippingMethod(string $shippingMethod): mixed
    {
        $date = new \DateTime();
        $qb = $this->createQueryBuilder('o')
            ->join('o.shipments', 'sh')
            ->join('sh.method', 'm')
            ->join('o.channel', 'ch')
            ->where('o.shippingState = :shippingState')
            ->andWhere('m.code LIKE :shippingMethod')
            ->andWhere("sh.tracking IS NULL OR sh.tracking = ' '")
            ->andWhere('sh.startDeliverySlot > :date')
            ->setParameter('shippingMethod', '%' . $shippingMethod . '%')
            ->setParameter('shippingState', OrderShippingStates::STATE_SHIPPED)
            ->setParameter('date', $date->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    public function getSimpleData(ChannelInterface $channel)
    {
        $date = new \DateTime();
        $interval = new \DateInterval('P1Y');
        $date->sub($interval);

        $qb = $this->createQueryBuilder('o')
            ->join('o.orderStore', 'os')
            ->join('o.channel', 'c')
            ->join('os.store', 's')
            ->andWhere('(o.channel = :channel OR c.id = 1)')
            ->andWhere('os.store is not null')
            ->andWhere('o.paymentState = :paid')
            ->andWhere('s.channel = :channel')
            ->andWhere('os.seller is not null')
            ->andWhere('o.checkoutCompletedAt >= :date')
            ->setParameter('paid', OrderPaymentStates::STATE_PAID)
            ->setParameter('channel', $channel->getId())
            ->setParameter('date', $date->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Override from setono GiftCardPlugin.
     */
    public function createCompletedQueryBuilderByGiftCard($giftCardId): QueryBuilder
    {
        return $this->createQueryBuilderByGiftCard($giftCardId->get('id'))
            ->andWhere('o.checkoutState = :checkoutState')
            ->setParameter('checkoutState', OrderCheckoutStates::STATE_COMPLETED);
    }

    /**
     * @return float|int|mixed|string|null
     *
     * @throws NonUniqueResultException
     */
    public function findForQualityId($id)
    {
        $qb = $this->createQueryBuilder('o')
            ->join('o.items', 'oi')
            ->join('oi.orderItemLogistics', 'oil')
            ->join('oil.quality', 'q')
            ->andWhere('q.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array{id: int, checkoutCompletedAt: \DateTime, total: int}
     */
    public function findInvoiceIdForPaidOrderByOrderNumber(OrderNumber $orderNumber): array
    {
        $result = $this->createQueryBuilder('o')
            ->select('o.id, o.checkoutCompletedAt, o.total')
            ->andWhere('o.state != :state')
            ->andWhere('o.number in (:number)')
            ->setParameters(
                [
                    'state' => OrderPaymentStates::STATE_PAID,
                    'number' => [
                        $orderNumber->getOrderNumber(),
                        $orderNumber->getDeprecatedOrderNumber(),
                    ],
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $result) {
            throw new OrderNotFound(sprintf('order %s not found', $orderNumber->getOrderNumber()));
        }

        return $result;
    }

    /**
     * @return array{array{variantName: string, name: string, code: string, unit_price: string, total: string, quantity: int, variantQuantity: int, weight: string}}
     */
    public function findLogisticCodeAndNameForInvoiceByOrderItem(int $orderItemId, string $locale): array
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('pvt.name as variantName, pvt.name, pv.code, oi.unit_price, oi.total, count(pv.code) as quantity, pv.weight')
            ->addSelect(
                '(SELECT count(pv.id)
		FROM tediber.sylius_order_item oi
		INNER JOIN tediber.sylius_order_item_logistic oil
			ON oil.order_item_id = oi.id
		INNER JOIN tediber.sylius_product_variant pv
			ON pv.id = oil.productVariantLogistic_id
		WHERE oi.id = :orderItemId ) AS variantQuantity'
            )
            ->from('sylius_order_item', 'oi')
            ->innerJoin('oi', 'sylius_order_item_logistic', 'oil', 'oil.order_item_id = oi.id')
            ->innerJoin('oil', 'sylius_product_variant', 'pv', 'pv.id = oil.productVariantLogistic_id')
            ->innerJoin('pv', 'sylius_product_variant_translation', 'pvt', 'pvt.translatable_id = pv.id')
            ->where('oi.id = :orderItemId')
            ->andWhere('pvt.locale = :locale')
            ->groupBy('pv.code')
            ->addGroupBy('oi.id')
            ->setParameters(
                [
                    'orderItemId' => $orderItemId,
                    'locale' => $locale,
                ]
            );

        return $query->execute()->fetchAllAssociative();
    }

    public function findTotalWeightInKilograms(string $tokenOrder): ?string
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('SUM(pv.weight) as totalWeight')
            ->from('sylius_order', 'o')
            ->innerJoin('o', 'sylius_order_item', 'oi', 'oi.order_id = o.id')
            ->innerJoin('oi', 'sylius_product_variant', 'pv', 'pv.id = oi.variant_id')
            ->where('o.token_value = :token')
            ->setParameter('token', $tokenOrder);

        $response = $query->execute()->fetchAllAssociative();

        return $response[0]['totalWeight'] ?? null;
    }

    public function getOrdersToCheckEdi(): array
    {
        $date = new \DateTime();
        $interval = new \DateInterval('P1D');
        $date->sub($interval);

        return $this->createQueryBuilder('o')
            ->leftJoin('o.shipments', 's')
            ->leftJoin('s.method', 'm')
            ->leftJoin('o.payments', 'p')
            ->andWhere('o.paymentState = :state')
            ->andWhere('m.code NOT IN (:methods)')
            ->andWhere('p.state = :paymentState')
            ->andWhere('p.createdAt >= :createdAt')
            ->setParameter('state', OrderPaymentStates::STATE_PAID)
            ->setParameter('paymentState', PaymentInterface::STATE_COMPLETED)
            ->setParameter('methods', ShippingMethodInterface::SPIDY_DELIVERIES)
            ->setParameter('createdAt', $date)
            ->getQuery()
            ->getResult();
    }

    public function getErpNotSent()
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->join('o.channel', 'c')
            ->andWhere($queryBuilder->expr()->neq('c.code', ':return'))
            ->andWhere($queryBuilder->expr()->eq('o.odooState', ':ready'))
            ->andWhere($queryBuilder->expr()->eq('o.paymentState', ':paymentState'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->setParameter('return', ChannelCode::CHANNEL_RETURN)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('ready', OdooOrderInterface::ODOO_STATE_READY)
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery();
    }

    public function getErpNotShipped()
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->join('o.channel', 'c')
            ->andWhere($queryBuilder->expr()->neq('c.code', ':return'))
            ->andWhere($queryBuilder->expr()->eq('o.odooState', ':created'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->setParameter('return', ChannelCode::CHANNEL_RETURN)
            ->setParameter('created', OdooOrderInterface::ODOO_STATE_CREATED)
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery();
    }

    public function getErpReturnNotCreated(): \Doctrine\ORM\Query
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->join('o.channel', 'c')
            ->leftJoin('o.orderReason', 'sor') // TODO - Remove when odoo has a solution for this
            ->leftJoin('sor.reason', 'r') // TODO - Remove when odoo has a solution for this
            ->andWhere($queryBuilder->expr()->in('r.id', ':commercial_offer')) // TODO - Remove when odoo has a solution for this
            ->andWhere($queryBuilder->expr()->eq('c.code', ':return'))
            ->andWhere($queryBuilder->expr()->eq('o.odooState', ':ready'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->setParameter('return', ChannelCode::CHANNEL_RETURN)
            ->setParameter('ready', OdooOrderInterface::ODOO_STATE_READY)
            ->setParameter('commercial_offer', OrderReason::COMMERCIAL_OFFER_ID) // TODO - Remove when odoo has a solution for this
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery();
    }

    public function getErpReturnNotConfirmed(): \Doctrine\ORM\Query
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->leftJoin('o.channel', 'c')
            ->andWhere($queryBuilder->expr()->eq('o.odooState', ':odooState'))
            ->andWhere($queryBuilder->expr()->eq('o.paymentState', ':paymentState'))
            ->andWhere($queryBuilder->expr()->eq('c.code', ':channelCode'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->andWhere($queryBuilder->expr()->exists(
                $this->getEntityManager()->createQueryBuilder()
                    ->select('1')
                    ->from(PaymentInterface::class, 'p')
                    ->where('p.order = o.id')
                    ->andWhere('p.state = :paymentState')
            ))
            ->setParameter('odooState', \App\Api\Odoo\Model\Order::ODOO_STATE_CREATED)
            ->setParameter('paymentState', OrderPaymentStates::STATE_REFUNDED)
            ->setParameter('channelCode', ChannelCode::CHANNEL_RETURN)
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery();
    }

    public function getErpShipped()
    {
        $queryBuilder = $this->createQueryBuilder('o');

        // TODO - Need to be more specific when Lot Number is added to Sylius
        return $queryBuilder
            ->join('o.channel', 'c')
            ->innerJoin('o.shipments', 's')
            ->andWhere($queryBuilder->expr()->neq('c.code', ':return'))
            ->andWhere($queryBuilder->expr()->eq('o.odooState', ':shipping'))
            ->andWhere($queryBuilder->expr()->eq('o.shippingState', ':shipped'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->setParameter('return', ChannelCode::CHANNEL_RETURN)
            ->setParameter('shipping', OdooOrderInterface::ODOO_STATE_DESTOCKED)
            ->setParameter('shipped', OrderShippingStates::STATE_SHIPPED)
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery();
    }

    public function getErpToCancel()
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->join('o.channel', 'c')
            ->andWhere($queryBuilder->expr()->neq('o.odooState', ':odooState'))
            ->andWhere($queryBuilder->expr()->eq('o.state', ':cancelled'))
            ->andWhere($queryBuilder->expr()->eq('o.paymentState', ':paymentState'))
            ->andWhere($queryBuilder->expr()->lt('o.updatedAt', ':nowMinusTenMinutes'))
            ->setParameter('odooState', OdooOrderInterface::ODOO_STATE_CANCELLED)
            ->setParameter('cancelled', OrderInterface::STATE_CANCELLED)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('nowMinusTenMinutes', new \DateTime('-10 minutes'))
            ->getQuery()
        ;
    }

    public function getOrdersOrderItems()
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->join('o.items', 'oi')
            ->join('o.payments', 'p')
            ->andWhere($queryBuilder->expr()->in('p.state', ':state'))
            ->andWhere($queryBuilder->expr()->eq('o.paymentState', ':paymentState'))
            ->andWhere('o.total != p.amount')
            ->andWhere('o.checkoutCompletedAt >= :dateStart')
            ->andWhere('o.checkoutCompletedAt <= :dateEnd')
            ->setParameter('state', PaymentInterface::STATE_COMPLETED)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->setParameter('dateStart', '2020-12-31 23:59:59')
            ->setParameter('dateEnd', '2024-12-31 23:59:59')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findIdsByDateRangeByShippedOrders(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->select('DISTINCT o.id')
            ->leftjoin('o.shipments', 's')
            ->leftjoin('o.channel', 'c')
            ->andWhere('s.shippingDate >= :startDate')
            ->andWhere('s.shippingDate <= :endDate')
            ->andWhere('s.state IN (:shippingState)')
            ->andWhere('o.shippingState IN (:shippingState)')
            ->andWhere('c.code != :channelCode')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('channelCode', ChannelCode::CHANNEL_RETURN)
            ->setParameter('shippingState', [OrderShippingStates::STATE_SHIPPED, OrderShippingStates::STATE_DELIVERED])
            ->getQuery()
            ->getResult();
    }

    public function findIdsByDateRangeByCheckoutOrders(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->select('distinct o.id')
            ->leftjoin('o.channel', 'c')
            ->andWhere('o.checkoutCompletedAt >= :startDate')
            ->andWhere('o.checkoutCompletedAt <= :endDate')
            ->andWhere('o.paymentState IN (:state)')
            ->andWhere('c.code != :channelCode')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('channelCode', ChannelCode::CHANNEL_RETURN)
            ->setParameter('state', [OrderPaymentStates::STATE_PAID])
            ->getQuery()
            ->getResult();
    }

    public function getLastCreatedBisByParentNumber(string $parentNumber): ?Order
    {
        $queryBuilder = $this->createQueryBuilder('o');

        return $queryBuilder
            ->join('o.channel', 'channel')
            ->andWhere($queryBuilder->expr()->eq('o.state', ':state'))
            ->andWhere($queryBuilder->expr()->eq('channel.code', ':channelCode'))
            ->andWhere($queryBuilder->expr()->eq('o.parentNumber', ':parentNumber'))
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('channelCode', ChannelCode::CHANNEL_BIS)
            ->setParameter('parentNumber', $parentNumber)
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return iterable<Order>
     */
    public function findOldBefore(\DateTimeInterface $date): iterable
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->andWhere('o.state = :state')
            ->setParameter('state', OrderInterfaceAlias::STATE_CART)
            ->andWhere('o.updatedAt < :maxDate')
            ->setParameter('maxDate', $date);

        return $qb->getQuery()->toIterable();
    }

    public function countOldBefore(\DateTimeInterface $date): int
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->select('COUNT(o.id)')
            ->andWhere('o.state = :state')
            ->setParameter('state', OrderInterfaceAlias::STATE_CART)
            ->andWhere('o.updatedAt < :maxDate')
            ->setParameter('maxDate', $date);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
