<?php

namespace App\Factory\View;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueTranslationInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Exception\ViewCreationException;
use App\View\ProductVariantTransmissionView;
use App\View\ProductVariantView;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;
use Sylius\Component\Product\Model\ProductTranslationInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

final class ProductVariantViewFactory implements ProductVariantViewFactoryInterface
{
    use BundlePricingTrait;

    public function create(ProductVariantInterface $productVariant, ChannelInterface $channel, string $locale): ProductVariantView
    {
        $channelPricing = $productVariant->getChannelPricingForChannel($channel);
        if (null === $channelPricing) {
            throw new ViewCreationException('Variant does not have pricing.');
        }

        $productVariantView = new ProductVariantView();
        $productVariantView->code = $productVariant->getCode();
        $productVariantView->name = $productVariant->getTranslation($locale)->getName();
        $productVariantView->price = $productVariant->isBundle() ? self::getBundlePrice($channelPricing) : $channelPricing->getPrice();
        $productVariantView->originalPrice = $channelPricing->getOriginalPrice();
        $productVariantView->ecotax = $productVariant->getEcotax();
        $productVariantView->product = $productVariant->getProduct()->getCode();
        $productVariantView->slug = $productVariant->getProduct()->getSlug();
        $productVariantView->enabled = $productVariant->isEnabled();
        $productVariantView->joinStoryLink = $productVariant->getJoinStoryLink() ?? $productVariant->getProduct()->getJoinStoryLink();
        $optionValuesToOrder = $optionCodesToOrder = $optionsTranslatedToOrder = [];
        foreach ($productVariant->getOptionValues() as $optionValue) {
            $optionCodesToOrder[$optionValue->getOption()->getPosition()] = [$optionValue->getOption()->getCode() => $optionValue->getCode()];
            $optionValuesToOrder[$optionValue->getOption()->getPosition()] = [$optionValue->getCode() => $optionValue->getTranslation($locale)->getValue()];
            $optionsTranslatedToOrder[$optionValue->getOption()->getPosition()] = [$optionValue->getOption()->getCode() => $optionValue->getOption()->getTranslation($locale)->getName()];
        }
        ksort($optionValuesToOrder);
        foreach ($optionValuesToOrder as $row) {
            $productVariantView->optionValues[key($row)] = current($row);
        }
        ksort($optionCodesToOrder);
        foreach ($optionCodesToOrder as $row) {
            $productVariantView->optionCodes[key($row)] = current($row);
        }
        ksort($optionsTranslatedToOrder);
        foreach ($optionsTranslatedToOrder as $row) {
            $productVariantView->optionTranslated[key($row)] = current($row);
        }

        foreach ($productVariant->getPacks() as $pack) {
            $productVariantView->packs[] = $pack->getCode();
        }

        if ($productVariant->isBundle()) {
            /** @var ProductInterface $product */
            $product = $productVariant->getProduct();
            /** @var BundleAssociation $bundleAssociation */
            foreach ($productVariant->getBundleAssociations() as $bundleAssociation) {
                $productVariantView->bundles[] = [
                    'productCode' => $bundleAssociation->getVariant()->getProduct()->getCode(),
                    'code' => $bundleAssociation->getVariant()->getCode(),
                    'price' => self::getBundlePriceFromValues($bundleAssociation->getPrice(), $product->getBundleType()),
                    'originalPrice' => $channelPricing->getOriginalPrice() > 0 ?
                        round($bundleAssociation->getPrice() * $channelPricing->getOriginalPrice() / $channelPricing->getPrice()) : null,
                    'quantity' => $bundleAssociation->getQuantity(),
                    'gift' => 0 === $bundleAssociation->getPrice(),
                    'name' => $bundleAssociation->getVariant()->getName(),
                    'enabled' => $bundleAssociation->getVariant()->isEnabled(),
                ];

                if (!$bundleAssociation->getVariant()->isEnabled()) {
                    $productVariantView->enabled = false;
                }

                if (0 === $bundleAssociation->getPrice()) {
                    $productVariantView->gifted[] = [
                        $bundleAssociation->getVariant()->getProduct()->getCode() => $bundleAssociation->getVariant()->getCode(),
                    ];
                }
            }
        }

        return $productVariantView;
    }

    public function createForCategory(ProductVariantInterface $productVariant, ProductInterface $product, ChannelInterface $channel, string $locale): ProductVariantView
    {
        $channelPricing = $productVariant->getChannelPricings()->get($channel->getCode());
        if (null === $channelPricing) {
            throw new ViewCreationException('Variant does not have pricing.');
        }

        $productVariantView = new ProductVariantView();
        $productVariantView->code = $productVariant->getCode();

        /** @var ProductVariantTranslationInterface $translation */
        $translation = $productVariant->getTranslations()->get($locale);
        $productVariantView->name = $translation->getName();
        $productVariantView->price = $productVariant->isBundle() ? self::getBundlePrice($channelPricing) : $channelPricing->getPrice();
        $productVariantView->ecotax = $productVariant->getEcotax();
        $productVariantView->product = $product->getCode();

        /** @var ProductTranslationInterface $prdTranslation */
        $prdTranslation = $product->getTranslations()->get($locale);
        $productVariantView->slug = $prdTranslation->getSlug();
        $productVariantView->enabled = $productVariant->isEnabled();
        $optionValuesToOrder = $optionCodesToOrder = $optionsTranslatedToOrder = [];
        foreach ($productVariant->getOptionValues() as $optionValue) {
            $optionCodesToOrder[$optionValue->getOption()->getPosition()] = [$optionValue->getOption()->getCode() => $optionValue->getCode()];

            /** @var ProductOptionValueTranslationInterface $optValueTranslation */
            $optValueTranslation = $optionValue->getTranslations()->get($locale);
            $optionValuesToOrder[$optionValue->getOption()->getPosition()] = [$optionValue->getCode() => $optValueTranslation->getValue()];

            /** @var ProductOptionTranslationInterface $optTranslation */
            $optTranslation = $optionValue->getOption()->getTranslations()->get($locale);
            $optionsTranslatedToOrder[$optionValue->getOption()->getPosition()] = [$optionValue->getOption()->getCode() => $optTranslation->getName()];
        }
        ksort($optionValuesToOrder);
        foreach ($optionValuesToOrder as $row) {
            $productVariantView->optionValues[key($row)] = current($row);
        }
        ksort($optionCodesToOrder);
        foreach ($optionCodesToOrder as $row) {
            $productVariantView->optionCodes[key($row)] = current($row);
        }
        ksort($optionsTranslatedToOrder);
        foreach ($optionsTranslatedToOrder as $row) {
            $productVariantView->optionTranslated[key($row)] = current($row);
        }

        foreach ($productVariant->getPacks() as $pack) {
            $productVariantView->packs[] = $pack->getCode();
        }

        if ($productVariant->isBundle()) {
            /** @var BundleAssociation $bundleAssociation */
            foreach ($productVariant->getBundleAssociations() as $bundleAssociation) {
                /** @var ProductVariantTranslationInterface $varTranslation */
                $varTranslation = $bundleAssociation->getVariant()->getTranslations()->get($locale);

                $productVariantView->bundles[] = [
                    'productCode' => $bundleAssociation->getVariant()->getProduct()->getCode(),
                    'code' => $bundleAssociation->getVariant()->getCode(),
                    'price' => self::getPromotionBundlePrice($bundleAssociation->getPrice()),
                    'quantity' => $bundleAssociation->getQuantity(),
                    'gift' => 0 === $bundleAssociation->getPrice(),
                    'name' => $varTranslation->getName(),
                    'enabled' => $bundleAssociation->getVariant()->isEnabled(),
                ];

                if (!$bundleAssociation->getVariant()->isEnabled()) {
                    $productVariantView->enabled = false;
                }

                if (0 === $bundleAssociation->getPrice()) {
                    $productVariantView->gifted[] = [
                        $bundleAssociation->getVariant()->getProduct()->getCode() => $bundleAssociation->getVariant()->getCode(),
                    ];
                }
            }
        }

        return $productVariantView;
    }

    public function createForTaxon(ProductVariantInterface $productVariant, ?ProductInterface $product, ChannelInterface $channel, string $locale): ProductVariantView
    {
        $productVariantView = new ProductVariantView();

        /**
         * @var ProductVariantTranslationInterface $translation
         */
        $translation = $productVariant->getTranslations()->get($locale);

        $productVariantView->code = $productVariant->getCode();
        $productVariantView->name = $translation->getName();
        $channelPricing = $productVariant->getChannelPricings()->get($channel->getCode());
        $productVariantView->price = $channelPricing->getPrice();
        $productVariantView->originalPrice = $channelPricing->getOriginalPrice();

        if (!is_null($product)) {
            $productVariantView->product = $product->getCode();
            $productVariantView->slug = $product->getSlug();

            if (str_starts_with((string) $product->getCode(), 'bundle')) {
                $productVariantView->price = self::getBundlePrice($channelPricing);
                $productVariantView->originalPrice = $channelPricing->getOriginalPrice();
            }
        }

        foreach ($productVariant->getPacks() as $pack) {
            $productVariantView->packs[] = $pack->getCode();
        }

        unset($translation);

        return $productVariantView;
    }

    public function createTransmission(ProductVariantInterface $productVariant, ChannelInterface $channel, string $locale): ProductVariantTransmissionView
    {
        $productVariantTransmissionView = new ProductVariantTransmissionView();
        $productVariantTransmissionView->code = $productVariant->getCode();
        $productVariantTransmissionView->name = $productVariant->getTranslation($locale)->getName();

        $productVariantTransmissionView->weight = $productVariant->getWeight();
        $productVariantTransmissionView->height = $productVariant->getHeight();
        $productVariantTransmissionView->width = $productVariant->getWidth();
        $productVariantTransmissionView->depth = $productVariant->getDepth();

        return $productVariantTransmissionView;
    }
}
