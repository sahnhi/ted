<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Factory\Calendar\Request\CalendarAvailabilityRequestFactory;
use App\Processor\OrderMessage\OrderMessageProcessor;
use App\Services\Calendar\CalendarApi;
use App\Services\Sentry\ReportingInterface;
use Sentry\Severity;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class OrderDeliveryDateNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'app_order_delivery_date_normalizer_already_called';

    private const DELIVERY_SLOT_KEY = 'earliestDeliverySlot';

    public function __construct(
        private readonly CalendarAvailabilityRequestFactory $calendarAvailabilityRequestFactory,
        private readonly CalendarApi $calendarApi,
        private readonly DateTimeNormalizer $dateTimeNormalizer,
        private readonly ReportingInterface $reporting,
        private readonly OrderMessageProcessor $orderMessageProcessor,
    ) {
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Order;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        if (!$object instanceof Order) {
            throw new \InvalidArgumentException(sprintf('Expected an instance of %s, but got %s.', Order::class, $object::class));
        }

        $this->orderMessageProcessor->process($object);

        $context[self::ALREADY_CALLED] = true;

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        // If the shipments already have a delivery slot, it means we already went through the checkout process
        if (!$object->getShipments()->isEmpty()) {
            $earliestDeliverySlot = null;
            /** @var Shipment $shipment */
            foreach ($object->getShipments() as $shipment) {
                if (null !== $shipment->getStartDeliverySlot() && (null === $earliestDeliverySlot || $shipment->getStartDeliverySlot() < $earliestDeliverySlot)) {
                    $earliestDeliverySlot = $shipment->getStartDeliverySlot();
                }
            }

            if (null !== $earliestDeliverySlot) {
                $data[self::DELIVERY_SLOT_KEY] = $this->dateTimeNormalizer->normalize($earliestDeliverySlot, $format, $context);

                return $data;
            }
        }

        // If an order has no shipping address, no need to check the calendar as it needs a shipping address.
        if (null === $object->getShippingAddress()) {
            return $data;
        }

        $this->calendarApi->setRequest(
            $this->calendarAvailabilityRequestFactory->createForDeliveryDateEstimate($object)
        );

        try {
            // The slots are sorted, from the earliest to furthest
            $deliverySlots = $this->calendarApi->checkCalendar();
            if ([] !== $deliverySlots) {
                $deliverySlotDateTime = $this->formatDeliverySlotAsDateTime(array_key_first($deliverySlots), reset($deliverySlots));

                if (false !== $deliverySlotDateTime) {
                    $data[self::DELIVERY_SLOT_KEY] = $this->dateTimeNormalizer->normalize($deliverySlotDateTime, $format, $context);
                }
            }
        } catch (\Throwable $e) {
            // An exception/error thrown by the calendar should never disrupt the serialization process of an order
            $this->reporting->reportIssue($e, Severity::error(), [
                'order_id' => $object->getId(),
                'token_value' => $object->getTokenValue(),
            ]);
        }

        return $data;
    }

    private function formatDeliverySlotAsDateTime(string $date, array $slotTimes): \DateTimeInterface|false
    {
        if (empty($slotTimes['times'])) {
            return false;
        }

        $earliestTime = reset($slotTimes['times']);

        return \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s %s', $date, $earliestTime['start_time']));
    }
}
