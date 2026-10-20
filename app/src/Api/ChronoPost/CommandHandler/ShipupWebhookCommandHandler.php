<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\CommandHandler;

use App\Api\ChronoPost\Command\ShipupWebhookCommand;
use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ShipmentRepositoryInterface;
use App\Exception\ShipmentNotFound;
use App\Identifier\OrderNumber;
use App\Utils\Url;
use Assert\AssertionFailedException;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ShipupWebhookCommandHandler implements MessageHandlerInterface
{
    private const SUPPORTED_WEBHOOK_TYPE = 'tracker.events.new';
    private const SUPPORTED_SHIPMENT_TYPE = 'chronopost';

    private const SHIPUP_STATE_PENDING = 'pending';
    private const SHIPUP_STATE_INFO_RECEIVED = 'info_received';
    private const SHIPUP_STATE_IN_TRANSIT = 'in_transit';
    private const SHIPUP_STATE_FAILED_ATTEMPT = 'failed_attempt';
    private const SHIPUP_STATE_EXCEPTION = 'exception';
    private const SHIPUP_STATE_PICKUP = 'pickup';
    private const SHIPUP_STATE_DELIVERED = 'delivered';
    private const SHIPUP_STATE_RETURN = 'return';
    private const SHIPUP_STATE_EXPIRED = 'expired';

    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ShipmentRepositoryInterface $shipmentRepository,
        private readonly ObjectManager $shipmentManager
    ) {
    }

    /**
     * @throws \Exception
     * @throws AssertionFailedException
     */
    public function __invoke(ShipupWebhookCommand $command)
    {
        $content = json_decode($command->getContent(), true);

        if (self::SUPPORTED_WEBHOOK_TYPE !== $content['type']) {
            return;
        }

        $object = $content['data']['object'];

        if (self::SUPPORTED_SHIPMENT_TYPE !== $object['carrier_code']) {
            return;
        }

        $orderNumber = OrderNumber::completingWith0AtTheBeginning($object['fulfillment']['order']['order_number']);

        /** @var Order $order */
        $order = $this->orderRepository->findOneByNumber($orderNumber->getOrderNumber());

        if (!$order) {
            throw new ShipmentNotFound(sprintf('order not found for order number %s', $orderNumber->getOrderNumber()));
        }

        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->findOneByOrderNumber($order->getNumber());

        if (!$shipment) {
            throw new ShipmentNotFound(sprintf('shipment not found for order number %s', $orderNumber->getOrderNumber()));
        }

        $state = $this->getStatusCode($object['delivery_status_code']);

        if (!$state) {
            return;
        }

        $this->updateShipmentState(
            $shipment,
            $state,
            $object
        );
        $this->updateShippingDeliveryAddress(
            $order,
            $object['pickup_point']['address']
        );
        $this->updateShippingDeliverySlotDates($shipment, $object);
    }

    private function getStatusCode(string $status): ?string
    {
        switch ($status) {
            case self::SHIPUP_STATE_INFO_RECEIVED:
            case self::SHIPUP_STATE_PENDING:
                return OrderShippingStates::STATE_READY;
            case self::SHIPUP_STATE_RETURN:
            case self::SHIPUP_STATE_EXCEPTION:
            case self::SHIPUP_STATE_FAILED_ATTEMPT:
            case self::SHIPUP_STATE_EXPIRED:
            case self::SHIPUP_STATE_IN_TRANSIT:
                return OrderShippingStates::STATE_SHIPPED;
            case self::SHIPUP_STATE_DELIVERED:
            case self::SHIPUP_STATE_PICKUP:
                return OrderShippingStates::STATE_DELIVERED;
            default:
                return null;
        }
    }

    /**
     * @throws AssertionFailedException
     * @throws \Exception
     */
    private function updateShipmentState(
        Shipment $shipment,
        string $state,
        array $content
    ): void {
        if (OrderShippingStates::STATE_SHIPPED === $state && null !== $content['shipped_at']) {
            $date = (new \DateTime())->setTimestamp($content['shipped_at']);
            $shipment->setShippedAt($date);
        }

        if (OrderShippingStates::STATE_DELIVERED === $state && null !== $content['delivered_at']) {
            $date = (new \DateTime())->setTimestamp($content['delivered_at']);
            $shipment->setShippingDate($date);
        }

        $shipment->setState($state);
        $shipment->setTracking((string) new Url($content['carrier_tracking_url']));
        $shipment->setTrackingNumber($content['tracking_number']);

        $this->shipmentManager->persist($shipment);
        $this->shipmentManager->flush();
    }

    private function updateShippingDeliveryAddress(Order $order, array $pickupAddress)
    {
        $address = $order->getShippingAddress();
        $address->setCity($pickupAddress['city']);
        $address->setCountryCode($pickupAddress['country_code']);
        $street = $pickupAddress['address1'];
        $street .= null !== $pickupAddress['address2'] ?
            (', ' . $pickupAddress['address2']) :
            '';
        $address->setStreet($street);
        $address->setUpdatedAt(new \DateTime());

        $this->shipmentManager->persist($address);
        $this->shipmentManager->flush();
    }

    /**
     * @throws \Exception
     */
    private function updateShippingDeliverySlotDates(Shipment $shipment, array $content)
    {
        $expectedDeliveryDate = null !== $content['displayable_expected_delivery_datetime'] ?
            (new \DateTime())->setTimestamp($content['displayable_expected_delivery_datetime'])
            : null;

        if (null !== $expectedDeliveryDate && !empty($content['pickup_point']['opening_hours'])) {
            $day = strtolower($expectedDeliveryDate->format('D'));
            $openingHours = $content['pickup_point']['opening_hours'];

            /**
             * Here we are going to look fot the next day with opening hours
             * Save the diff in $i.
             */
            $i = 0;
            while (null === $openingHours[$day]) {
                $key = array_search($day, self::DAYS);
                $day = $key >= 6 ?
                    self::DAYS[0] :
                    self::DAYS[++$key];
                ++$i;
            }

            /*
             * Add $i to $expectedDeliveryDate
             */
            $expectedDeliveryDate->add(new \DateInterval('P' . $i . 'D'));

            /**
             * Look for opening hours of the day.
             */
            $dayHours = [];
            preg_match('/(.*)-.* .*-(.*)/', (string) $openingHours[$day], $dayHours);

            $startDeliverySlot = new \DateTime($expectedDeliveryDate->format('Y-m-d') . $dayHours[1]);
            $endDeliverySlot = new \DateTime($expectedDeliveryDate->format('Y-m-d') . $dayHours[2]);

            $shipment->setStartDeliverySlot($startDeliverySlot);
            $shipment->setEndDeliverySlot($endDeliverySlot);

            $this->shipmentManager->persist($shipment);
            $this->shipmentManager->flush();
        }
    }
}
