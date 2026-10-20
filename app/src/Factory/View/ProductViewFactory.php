<?php

namespace App\Factory\View;

use App\Doctrine\Entity\ProductGoogleRssInterface;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use App\Doctrine\Repository\Sylius\ProductTaxonRepository;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Provider\CrossSelling\RelatedProductsProvider;
use App\View\ProductTaxonView;
use App\View\ProductTransmissionView;
use App\View\ProductView;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final class ProductViewFactory implements ProductViewFactoryInterface
{
    use BundlePricingTrait;

    public function __construct(
        private readonly ImageViewFactoryInterface $imageViewFactory,
        private readonly ProductAttributeValuesViewFactoryInterface $attributeValuesViewFactory,
        private readonly ProductOptionViewFactoryInterface $productOptionViewFactory,
        private readonly ProductVariantViewFactoryInterface $productVariantViewFactory,
        private readonly string $fallbackLocale,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly ProductTaxonRepository $productTaxonRepository,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly RelatedProductsProvider $relatedProductsProvider,
        private readonly RelatedProductViewFactoryInterface $relatedProductViewFactory,
    ) {
    }

    public function checkProductBounds(bool $bounds, ProductInterface $product, ProductView $productView, ChannelInterface $channel): ProductView
    {
        if ($bounds) {
            /** @var ProductVariantInterface $variant */
            foreach ($product->getVariants() as $variant) {
                if ($variant->isEnabled()) {
                    if (is_null($productView->minBound) || $variant->getChannelPricingForChannel($channel)->getPrice() < $productView->minBound) {
                        $productView->minBound = $variant->getChannelPricingForChannel($channel)->getPrice();
                    }
                    if (is_null($productView->maxBound) || $variant->getChannelPricingForChannel($channel)->getPrice() > $productView->maxBound) {
                        $productView->maxBound = $variant->getChannelPricingForChannel($channel)->getPrice();
                    }
                }
            }
        }

        return $productView;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function listForTaxon(ProductInterface $product, array $productTaxons, ChannelInterface $channel, string $locale): ProductView
    {
        $productView = new ProductView();
        $productView->code = $product->getCode();

        $options = $product->getOptions();

        $productView->averageRating = (string) $product->getAverageRating();

        $productView->numberOfRating = (string) $this->productReviewRepository->countRatingForProduct($product);

        /** @var ProductTranslationInterface $translation */
        $translation = $product->getTranslations()->get($locale);
        $productView->name = $translation->getName();
        $productView->slug = $translation->getSlug();

        if (!$options->isEmpty()) {
            foreach ($options as $option) {
                $productView->options[] = $this->productOptionViewFactory->createForTaxon($option, $locale, $product);
            }
        }

        $cheapestProductVariant = $this->productVariantRepository->findCheapestPrice($product, $channel);

        if ($cheapestProductVariant instanceof ProductVariantInterface) {
            $productTaxonWithVariant = $this->productTaxonRepository->getOneByProductId($cheapestProductVariant->getProduct()->getId(), $productTaxons);

            $channelPricing = $cheapestProductVariant->getChannelPricings()->get($channel->getCode());
            $prd = $productTaxonWithVariant->getProduct();
            $prd->setCurrentLocale($locale);

            $productView->variants[] = $this->productVariantViewFactory->createForTaxon($cheapestProductVariant, $prd, $channel, $locale);
            $productView->minBound = $channelPricing instanceof ChannelPricingInterface ? $channelPricing->getPrice() : null;
            if (null !== $productView->minBound && $cheapestProductVariant->isBundle()) {
                $productView->minBound = self::getBundlePrice($channelPricing);
            }
        }

        unset($options, $translation, $channelPricing, $cheapestProductVariant);

        return $productView;
    }

    public function list(ProductInterface $product, ChannelInterface $channel, string $locale, bool $bounds = false): ProductView
    {
        $productView = new ProductView();
        $productView->code = $product->getCode();

        $productView->averageRating = (string) $product->getAverageRating();
        $productView->numberOfRating = (string) count($product->getAcceptedReviews());

        /** @var ProductTranslationInterface $translation */
        $translation = $product->getTranslation($locale);
        $productView->name = $translation->getName();
        $productView->slug = $translation->getSlug();

        foreach ($product->getOptions() as $option) {
            $productView->options[] = $this->productOptionViewFactory->create($option, $locale, $product);
        }

        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            if ($variant->isEnabled()) {
                $productView->variants[] = $this->productVariantViewFactory->create($variant, $channel, $locale);
            }
        }

        return $this->checkProductBounds($bounds, $product, $productView, $channel);
    }

    public function listShopApi(ProductInterface $product, ChannelInterface $channel, string $locale, bool $bounds = false): ProductView
    {
        $productView = new ProductView();
        $productView->code = $product->getCode();

        $productView->averageRating = (string) $product->getAverageRating();
        $productView->numberOfRating = (string) count($product->getAcceptedReviews());
        $productView->joinStoryLink = $product->getJoinStoryLink();

        /** @var ProductTranslationInterface $translation */
        $translation = $product->getTranslation($locale);
        $productView->name = $translation->getName();
        $productView->slug = $translation->getSlug();

        return $this->checkProductBounds($bounds, $product, $productView, $channel);
    }

    public function create(ProductInterface $product, ChannelInterface $channel, string $locale, $removeCartField = false, bool $withVariants = false): ProductView
    {
        $productView = new ProductView();
        $productView->code = $product->getCode();
        $productView->warranty = $product->getWarranty();
        $productView->bundleType = $product->getBundleType()?->value;
        $cheapestProductVariant = $this->productVariantRepository->getCheapestByProduct($product, $channel);
        if ($cheapestProductVariant instanceof ProductVariantInterface) {
            $channelPricing = $cheapestProductVariant->getChannelPricingForChannel($channel);
            $productView->lowerPrice = $channelPricing instanceof ChannelPricingInterface ? self::getBundlePrice($channelPricing) : null;
            $productView->originalLowerPrice = $channelPricing instanceof ChannelPricingInterface ? $channelPricing->getOriginalPrice() : null;
        }

        $productRssGoogle = $product->getProductGoogleRss();
        $productView->gtin = $productRssGoogle instanceof ProductGoogleRssInterface ? $productRssGoogle->getGtin() : null;

        if (!$removeCartField) {
            $productView->averageRating = (string) $product->getAverageRating();
            $productView->numberOfRating = (string) count($product->getAcceptedReviews());
        }

        /** @var ProductTranslationInterface $translation */
        $translation = $product->getTranslation($locale);
        $productView->name = $translation->getName();
        $productView->slug = $translation->getSlug();
        $productView->description = $translation->getDescription();
        $productView->shortDescription = $translation->getShortDescription();
        $productView->metaKeywords = $translation->getMetaKeywords();
        $productView->metaDescription = $translation->getMetaDescription();

        foreach ($product->getImages() as $image) {
            $productView->images[] = $this->imageViewFactory->create($image);
        }

        if (!$removeCartField) {
            foreach ($product->getOptions() as $option) {
                if ($product->hasBundle()) {
                    $productView->options[] = $this->productOptionViewFactory->createForBundle($option, $locale, $product, $channel);
                } else {
                    $productView->options[] = $this->productOptionViewFactory->create($option, $locale, $product);
                }
            }
        }

        if (true === $withVariants) {
            /** @var ProductVariantInterface $variant */
            foreach ($product->getEnabledVariants() as $variant) {
                $productView->variants[] = $this->productVariantViewFactory->create($variant, $channel, $locale);
            }
        }

        /** @var ProductTaxonView $taxons */
        $taxons = new ProductTaxonView();
        if (null !== $product->getMainTaxon()) {
            $taxons->main = $product->getMainTaxon()->getCode();
        }

        /** @var TaxonInterface $taxon */
        foreach ($product->getTaxons() as $taxon) {
            $taxons->others[] = $taxon->getCode();

            if (str_starts_with((string) $taxon->getCode(), 'review_')) {
                $taxons->review = str_replace('review_', '', (string) $taxon->getCode());
            }
        }
        $productView->taxons = $taxons;

        $productView->attributes = $this->attributeValuesViewFactory->create(
            $product->getAttributesByLocale($locale, $this->fallbackLocale)->toArray(),
            $locale
        );

        $productView->joinStoryLink = $product->getJoinStoryLink();

        $productView->relatedProducts = array_map(
            fn (Product $relatedProduct): \App\View\RelatedProductView => $this->relatedProductViewFactory->create($relatedProduct, $channel, $locale),
            $this->relatedProductsProvider->provideForProduct($product)
        );

        return $productView;
    }

    public function createForCategory(ProductInterface $product, ChannelInterface $channel, string $locale, $removeCartField = false): ProductView
    {
        $productView = new ProductView();
        $productView->code = $product->getCode();
        $productView->warranty = $product->getWarranty();

        $cheapestProductVariant = $this->productVariantRepository->findCheapestPrice($product, $channel);
        if ($cheapestProductVariant instanceof ProductVariantInterface) {
            $channelPricing = $cheapestProductVariant->getChannelPricings()->get($channel->getCode());
            $productView->lowerPrice = $channelPricing instanceof ChannelPricingInterface ? self::getBundlePrice($channelPricing) : null;
            $productView->originalLowerPrice = $channelPricing instanceof ChannelPricingInterface ? self::getBundlePrice($channelPricing) : null;
        }

        $productRssGoogle = $product->getProductGoogleRss();
        $productView->gtin = $productRssGoogle instanceof ProductGoogleRssInterface ? $productRssGoogle->getGtin() : null;

        if (!$removeCartField) {
            $productView->averageRating = (string) $product->getAverageRating();

            $productView->numberOfRating = (string) $this->productReviewRepository->countRatingForProduct($product);
        }

        /** @var ProductTranslationInterface $translation */
        $translation = $product->getTranslations()->get($locale);
        $productView->name = $translation->getName();
        $productView->slug = $translation->getSlug();
        $productView->description = $translation->getDescription();
        $productView->shortDescription = $translation->getShortDescription();
        $productView->metaKeywords = $translation->getMetaKeywords();
        $productView->metaDescription = $translation->getMetaDescription();

        foreach ($product->getImages() as $image) {
            $productView->images[] = $this->imageViewFactory->create($image);
        }

        if (!$removeCartField) {
            foreach ($product->getOptions() as $option) {
                if ($product->hasBundle()) {
                    $productView->options[] = $this->productOptionViewFactory->createForBundle($option, $locale, $product, $channel);
                } else {
                    $productView->options[] = $this->productOptionViewFactory->createForTaxon($option, $locale, $product);
                }
            }
        }

        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            if ($variant->isEnabled()) {
                $productView->variants[] = $this->productVariantViewFactory->createForCategory($variant, $product, $channel, $locale);
            }
        }

        /** @var ProductTaxonView $taxons */
        $taxons = new ProductTaxonView();
        if (null !== $product->getMainTaxon()) {
            $taxons->main = $product->getMainTaxon()->getCode();
        }

        $prdTaxonList = $this->productTaxonRepository->findBy(['product' => $product], ['position' => 'ASC']);

        /** @var ProductTaxonInterface $prdTaxon */
        foreach ($prdTaxonList as $prdTaxon) {
            $taxons->others[] = $prdTaxon->getTaxon()->getCode();

            if (str_starts_with((string) $prdTaxon->getTaxon()->getCode(), 'review_')) {
                $taxons->review = str_replace('review_', '', (string) $prdTaxon->getTaxon()->getCode());
            }
        }
        $productView->taxons = $taxons;

        $productView->attributes = $this->attributeValuesViewFactory->create(
            $product->getAttributesByLocale($locale, $this->fallbackLocale)->toArray(),
            $locale
        );

        return $productView;
    }

    public function createTransmission(ProductInterface $product, ChannelInterface $channel, string $locale): ProductTransmissionView
    {
        $productTransmissionView = new ProductTransmissionView();
        foreach ($product->getVariants() as $variant) {
            $productTransmissionView->variants[] = $this->productVariantViewFactory->createTransmission($variant, $channel, $locale);
        }

        return $productTransmissionView;
    }
}
