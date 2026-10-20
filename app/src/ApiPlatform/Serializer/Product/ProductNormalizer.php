<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer\Product;

use App\ApiPlatform\Serializer\SerializerContextBuilder;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\Sylius\ChannelPricingRepository;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Product\Resolver\DefaultProductVariantResolver;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Webmozart\Assert\Assert;

class ProductNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'app_product_normalizer_already_called';

    public function __construct(
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ChannelPricingRepository $channelPricingRepository,
    ) {
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Product && isset($context[ContextKeys::CHANNEL]);
    }

    /**
     * @param Product $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        Assert::isInstanceOf($object, ProductInterface::class);
        Assert::keyNotExists($context, self::ALREADY_CALLED);
        Assert::isInstanceOf($context[ContextKeys::CHANNEL], Channel::class);

        $context[self::ALREADY_CALLED] = true;

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['lowestPrice'] = $this->getProductLowestPriceForChannel($object, $context[ContextKeys::CHANNEL]);

        // this will return the number of reviews for the product
        $this->getReviewInfos($object, $data);

        if (array_key_exists('productTaxons_taxon_code', $context[SerializerContextBuilder::QUERY_PARAMS_KEY])) {
            $queryTaxonParam = $context[SerializerContextBuilder::QUERY_PARAMS_KEY]['productTaxons_taxon_code'];
            if (!is_array($queryTaxonParam)) {
                $queryTaxonParam = [$queryTaxonParam];
            }

            foreach ($queryTaxonParam as $taxonConde) {
                $filteredProductTaxons = $object->getProductTaxons()->filter(fn (ProductTaxonInterface $productTaxon): bool => $productTaxon->getTaxon()?->getCode() === $taxonConde);

                if (1 === $filteredProductTaxons->count()) {
                    /** @var ProductTaxonInterface $productTaxon */
                    $productTaxon = $filteredProductTaxons->first();

                    $data['taxonPosition'][$taxonConde] = $productTaxon->getPosition();
                }
            }
        }

        if (isset($context['collection_operation_name']) && 'shop_get_extended' === $context['collection_operation_name']) {
            $defaultProductVariantResolver = new DefaultProductVariantResolver();
            $variant = $defaultProductVariantResolver->getVariant($object);
            $data['defaultVariantPopulated'] = null === $variant ? null : $this->normalizer->normalize($variant, $format, $context);
        }

        return $data;
    }

    private function getProductLowestPriceForChannel(Product $product, Channel $channel): int|null
    {
        $channelPricing = $this->channelPricingRepository->getLowestPriceForProduct($product, $channel);

        return $channelPricing?->getPrice();
    }

    private function getReviewInfos(Product $product, array &$data): void
    {
        $data['reviewsCount'] = $this->productReviewRepository->countRatingForProduct($product);
    }
}
