<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer\Product;

use App\Doctrine\Entity\Sylius\ProductVariant;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Bundle\ApiBundle\Serializer\ProductVariantNormalizer as BaseProductVariantNormalizer;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Exception\MissingChannelConfigurationException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductVariantNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    public function __construct(
        private readonly BaseProductVariantNormalizer $decoratedProductVariantNormalizer,
        private readonly ChannelContextInterface $channelContext,
        private readonly ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        private readonly RepositoryInterface $productVariantRepository,
    ) {
    }

    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->decoratedProductVariantNormalizer->setNormalizer($normalizer);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $this->decoratedProductVariantNormalizer->supportsNormalization($data, $format, $context);
    }

    /**
     * @param ProductVariant $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): mixed
    {
        $data = $this->decoratedProductVariantNormalizer->normalize($object, $format, $context);
        $data = $this->addPriceToBundleAssociationsVariant($data, $context);

        try {
            $data['originalPrice'] = $this->productVariantPricesCalculator->calculateOriginal($object, ['channel' => $this->channelContext->getChannel()]);
        } catch (ChannelNotFoundException|MissingChannelConfigurationException) {
            unset($data['originalPrice']);
        }

        return $data;
    }

    private function addPriceToBundleAssociationsVariant(array $data, array $context): array
    {
        if (!isset($data['bundleAssociations'], $context[ContextKeys::CHANNEL]) || !is_iterable($data['bundleAssociations'])) {
            return $data;
        }

        foreach ($data['bundleAssociations'] as &$association) {
            if (!isset($association['variant'])) {
                continue;
            }
            $variant = &$association['variant'];
            if (isset($variant['code'])) {
                $variantObj = $this->productVariantRepository->findOneBy(['code' => $variant['code']]);
                $variant['price'] = $this->productVariantPricesCalculator->calculate($variantObj, ['channel' => $context[ContextKeys::CHANNEL]]);
                $variant['originalPrice'] = $this->productVariantPricesCalculator->calculateOriginal($variantObj, ['channel' => $context[ContextKeys::CHANNEL]]);
            }
        }

        return $data;
    }
}
