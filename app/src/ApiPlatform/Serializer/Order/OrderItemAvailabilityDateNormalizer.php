<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer\Order;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\Services\Calendar\VariantService;
use App\Services\Sentry\ReportingInterface;
use Lcobucci\Clock\Clock;
use Sentry\Severity;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class OrderItemAvailabilityDateNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'app_order_item_availability_date_normalizer_already_called';

    private const AVAILABILITY_DATE_KEY = 'availableFrom';

    public function __construct(
        private readonly VariantService $variantService,
        private readonly Clock $clock,
        private readonly DateTimeNormalizer $dateTimeNormalizer,
        private readonly ReportingInterface $reporting,
    ) {
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof OrderItem;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        if (!$object instanceof OrderItem) {
            throw new \InvalidArgumentException(sprintf('Expected an instance of %s, but got %s.', OrderItem::class, $object::class));
        }

        $context[self::ALREADY_CALLED] = true;

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $variant = $object->getVariant();
        if (null === $variant) {
            return $data;
        }

        $shippingDate = null;
        foreach ($variant->getShippingStock() as $stock) {
            if (null === $shippingDate || $shippingDate > $stock->getShippingDate()) {
                $shippingDate = $stock->getShippingDate();
            }
        }

        $now = $this->clock->now();

        $shippingDate ??= $now;

        try {
            $availabilityDate = $this->variantService->checkLogisticVariants($variant->getCode(), $shippingDate, $now);

            $data[self::AVAILABILITY_DATE_KEY] = $this->dateTimeNormalizer->normalize($availabilityDate, $format, $context);
        } catch (\Throwable $e) {
            // An exception/error thrown by the variant service should never disrupt the serialization process of an OrderItem
            $this->reporting->reportIssue($e, Severity::error(), [
                'order_item_id' => $object->getId(), 'variant_code' => $variant->getCode(),
            ]);
        }

        return $data;
    }
}
