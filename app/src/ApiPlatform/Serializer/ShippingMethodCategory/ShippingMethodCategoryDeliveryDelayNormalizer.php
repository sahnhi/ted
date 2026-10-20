<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer\ShippingMethodCategory;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ShippingMethodCategoryDeliveryDelayNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const NORMALIZER_ALREADY_CALLED = 'app_shipping_method_category_delivery_delay_normalizer_already_called';

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::NORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ShippingMethodCategory;
    }

    /**
     * @param ShippingMethodCategory $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $context[self::NORMALIZER_ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);
        $data['lowestTransportTime'] = $this->getLowestTransportTime($object);

        return $data;
    }

    private function getLowestTransportTime(ShippingMethodCategory $shippingMethodCategory): float
    {
        $lowestTransportTime = null;

        /** @var ShippingMethod $shippingMethod */
        foreach ($shippingMethodCategory->getShippingMethods() as $shippingMethod) {
            if (null === $lowestTransportTime || $shippingMethod->getTransportTime() < $lowestTransportTime) {
                $lowestTransportTime = $shippingMethod->getTransportTime();
            }
        }

        return $lowestTransportTime;
    }
}
