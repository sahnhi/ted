<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueTranslationInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Model\ProductReferenceInterface;
use App\View\ProductOptionValueView;
use App\View\ProductOptionView;
use Sylius\Component\Core\Model\ChannelInterface;

class ProductOptionViewFactory implements ProductOptionViewFactoryInterface
{
    use BundlePricingTrait;

    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository
    ) {
    }

    public function create(ProductOption $option, string $locale, ProductInterface $product): ProductOptionView
    {
        $optionView = new ProductOptionView();
        $optionView->code = $option->getCode();
        $optionView->name = $option->getTranslation($locale)->getName();
        $optionView->defaultOptionValue = $option->getDefaultOptionValueByLocaleAndProduct($locale, $product);

        /** @var ProductOptionValueInterface $value */
        foreach ($option->getValues() as $value) {
            if ($value->getEnabled()) {
                $productOption = [
                    $option->getCode() => $value->getCode(),
                ];
                $variants = $this->productVariantRepository->findByOptions($product->getCode(), $productOption);
                if (empty($variants)) {
                    continue;
                }
                $valueView = new ProductOptionValueView();
                $valueView->code = $value->getCode();
                $valueView->value = $value->getTranslation($locale)->getValue();
                $valueView->help = $value->getTranslation($locale)->getHelp();
                $valueView->description = $value->getTranslation($locale)->getDescription();
                $valueView->optgroup = $value->getTranslation($locale)->getOptgroup();
                $optionView->values[] = $valueView;
            }
        }

        return $optionView;
    }

    public function createForTaxon(ProductOption $option, string $locale, ProductInterface $product): ProductOptionView
    {
        $optionView = new ProductOptionView();
        $optionView->code = $option->getCode();

        $translation = $option->getTranslations()->get($locale);
        $optionView->name = $translation->getName();

        if (null !== $option->getDefaultOptionsValue()) {
            foreach ($option->getDefaultOptionsValue() as $defaultOptionValue) {
                if ($locale === $defaultOptionValue->getLocale()->getCode()) {
                    $optionView->defaultOptionValue = $defaultOptionValue->getOptionValue()->getCode();
                    break;
                }
            }
        }

        $optionValues = $option->getValues();

        /** @var ProductOptionValueInterface $value */
        foreach ($optionValues as $value) {
            if ($value->getEnabled()) {
                $valueView = new ProductOptionValueView();
                $valueView->code = $value->getCode();

                /** @var ProductOptionValueTranslationInterface $optTranslation */
                $optTranslation = $value->getTranslations()->get($locale);
                $valueView->value = $optTranslation->getValue();
                $valueView->help = $optTranslation->getHelp();
                $valueView->description = $optTranslation->getDescription();
                $valueView->optgroup = $optTranslation->getOptgroup();
                $optionView->values[] = $valueView;
            }
        }

        unset($translation, $optionValues);

        return $optionView;
    }

    public function createForBundle(ProductOption $option, string $locale, ProductInterface $product, ChannelInterface $channel): ProductOptionView
    {
        $optionView = $this->create($option, $locale, $product);

        /* @var ProductOptionValueView $value */
        $optionView->values ??= [];
        $arrayValue = [];
        $isBundle = false;
        $cheapestPrice = null;

        $optionCode = str_replace('bundle-', '', (string) $option->getCode());

        /** @var ProductOptionValueView $value */
        foreach ($optionView->values as $value) {
            $valueCode = $value->code;
            /* Check if product is a bed-frame type and get all size for monobloc and bibloc */
            if (isset(ProductReferenceInterface::BUNDLE_BED_FRAME_TYPE[$valueCode])) {
                $monobloc = $bibloc = [];
                $variants = $this->productVariantRepository->findByProductCode(ProductReferenceInterface::BUNDLE_BED_FRAME_TYPE[$valueCode]);
                foreach ($variants as $variant) {
                    if (0 !== (int) $variant->getWidth()) {
                        if (str_contains((string) $variant->getCode(), 'BIB')) {
                            if (!in_array((int) $variant->getWidth(), $bibloc)) {
                                $bibloc[] = (int) $variant->getWidth();
                            }
                        } else {
                            if (!in_array($variant->getWidth(), $monobloc)) {
                                $monobloc[] = (int) $variant->getWidth();
                            }
                        }
                    }
                }
                /* @var ProductOptionValueView $value */
                $value->monobloc = $monobloc;
                $value->bibloc = $bibloc;
            }
            if (isset(ProductReferenceInterface::BUNDLE_PRICE_MATCH[$product->getCode()])
                && isset(ProductReferenceInterface::BUNDLE_PRICE_MATCH[$product->getCode()][$valueCode])
            ) {
                $value->bedFramePriceDiff = ProductReferenceInterface::BUNDLE_PRICE_MATCH[$product->getCode()][$valueCode];
            }
            $value->difference = 0;
            /**
             * @var ProductVariantInterface $cheapestVariant
             * @var ProductVariantInterface $comparisonVariant
             */
            $cheapestVariant = $this->productVariantRepository->getCheapestByOption($product, $option, $channel);
            $comparisonVariant = $this->productVariantRepository->getCheapestByOptionValue($product, $valueCode, $channel);
            /** @var ?ProductVariant $comparisonVariant */
            if (!empty($comparisonVariant->getBundleAssociations())) {
                foreach ($comparisonVariant->getBundleAssociations() as $bundleAssociation) {
                    foreach ($bundleAssociation->getVariant()->getOptionValues() as $optionValue) {
                        $match = $this->checkIfOptionMatch($optionCode, $optionValue->getOption()->getCode());
                        /** @var ProductVariantInterface $cheapestVariantBundle */
                        $cheapestVariantBundle = $this->productVariantRepository->getCheapestByOption($bundleAssociation->getVariant()->getProduct(), $optionValue->getOption(), $channel);
                        if (null !== $cheapestVariantBundle) {
                            $cheapestPrice = $cheapestVariantBundle->getChannelPricingForChannel($channel);
                        }
                        if ($match && ('bundle-size' !== $option->getCode())) {
                            $isBundle = true;
                            /**
                             * @var ProductVariantInterface $comparisonVariantBundle
                             * @var ProductVariantInterface $cheapestVariantBundle
                             */
                            $comparisonVariantBundle = $this->productVariantRepository->getCheapestByOptionValue($bundleAssociation->getVariant()->getProduct(), $optionValue->getCode(), $channel);
                            if (null !== $comparisonVariantBundle) {
                                $comparisonPrice = $comparisonVariantBundle->getChannelPricingForChannel($channel);
                                $arrayValue[$value->code] = $comparisonPrice->getPrice();
                                if (null !== $cheapestPrice && null !== $comparisonPrice) {
                                    $value->difference = self::getBundlePriceFromValues($comparisonPrice->getPrice() - $cheapestPrice->getPrice(), $product->getBundleType());
                                }
                            }
                        }
                    }
                }
            }
            if (null !== $cheapestVariant && 0 === $value->difference) {
                $cheapestPrice = $cheapestVariant->getChannelPricingForChannel($channel);
                $comparisonPrice = $comparisonVariant->getChannelPricingForChannel($channel);

                if (null !== $cheapestPrice && null !== $comparisonPrice) {
                    $value->difference = self::getBundlePriceFromValues($comparisonPrice->getPrice() - $cheapestPrice->getPrice(), $product->getBundleType());
                }
            }
        }
        if ($isBundle) {
            asort($arrayValue);
            $diff = [];
            $i = 0;
            $arrayValuePointer = $arrayValue;
            foreach ($arrayValue as $key => $value) {
                if ($i > 0) {
                    $diff[$key] = $value - prev($arrayValuePointer);
                    next($arrayValuePointer);
                } else {
                    $diff[$key] = 0;
                }
                next($arrayValuePointer);
                ++$i;
            }
            if (!empty($diff)) {
                foreach ($optionView->values as $value) {
                    /** @var ProductOptionValueView $value */
                    if (isset($diff[$value->code])) {
                        $value->difference = self::getBundlePriceFromValues($diff[$value->code], $product->getBundleType());
                    }
                }
            }
        }

        return $optionView;
    }

    private function checkIfOptionMatch(string $optionCode, string $option): bool
    {
        $optionCodeArray = explode('-', $optionCode);
        foreach ($optionCodeArray as $row) {
            if (!str_contains($option, $row)) {
                return false;
            }
        }

        return true;
    }
}
